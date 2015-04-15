<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\functional;

use \Symfony\Component\DomCrawler\Crawler;

class autogroups_base extends \phpbb_functional_test_case
{
	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array('phpbb/autogroups');
	}

//	public function setUp()
//	{
//		parent::setUp();
//
//		$this->add_lang_ext('phpbb/boardrules', array(
//			'boardrules_common',
//			'boardrules_controller',
//			'info_acp_boardrules',
//			'boardrules_acp',
//		));
//	}

	/**
	 * Create a group
	 *
	 * @param string $group_name The name of the group to create
	 * @return int Return the new group id
	 */
	public function create_group($group_name)
	{
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/groups');

		// Go to the group add form
		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => $group_name));

		// Submit the group add form
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form();
		$crawler = self::submit($form, array('group_name' => $group_name));

		// Assert the group was added successfully
		$this->assertContainsLang('GROUP_CREATED', $crawler->filter('#main')->text());

		// Go back to the groups page and find the settings link to the new group
		$crawler = self::request('GET', 'adm/index.php?i=acp_groups&mode=manage&sid=' . $this->sid);
		$crawler = $crawler
			->filter('table > tbody > tr')
			->reduce(function (Crawler $node) use ($group_name) {
				return $node->filter('strong')->text() == $group_name;
			});
		$url = $crawler->selectLink($this->lang('SETTINGS'))->link()->getUri();

		return (int) $this->get_parameter_from_link($url, 'g');
	}

	/**
	 * Create an Auto Groups rule set
	 *
	 * @param string $type     The auto group type posts|warnings|membershipbirthdays
	 * @param int    $group_id The group id
	 * @param int    $min      The minimum test value
	 * @param int    $max      Tha maximum test value
	 * @return int|null Return the new auto group rule id
	 */
	public function create_autogroup_rule($type, $group_id, $min, $max)
	{
		$db = $this->get_db();

		// Get the type id
		$sql = 'SELECT autogroups_type_id AS type_id
			FROM phpbb_autogroups_types
			WHERE autogroups_type_name = "' . $db->sql_escape('phpbb.autogroups.type.' . $type) . '"';
		$result = $db->sql_query($sql);
		$type_id = $db->sql_fetchfield('type_id');
		$db->sql_freeresult($result);

		if (!$type_id)
		{
			return null;
		}

		// Build the data array to insert
		$data = array(
			'autogroups_type_id'	=> $type_id,
			'autogroups_min_value'	=> $min,
			'autogroups_max_value'	=> $max,
			'autogroups_group_id'	=> $group_id,
			'autogroups_default'	=> true,
			'autogroups_notify'		=> false,
		);

		// Insert the data array
		$db->sql_query('INSERT INTO phpbb_autogroups_rules ' . $db->sql_build_array('INSERT', $data));

		return (int) $db->sql_nextid();
	}
}
