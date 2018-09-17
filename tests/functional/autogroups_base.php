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

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->get_db();
	}

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
	 * @param string $type     The auto group type posts|warnings|membership|birthdays|lastvisit
	 * @param int    $group_id The group id
	 * @param int    $min      The minimum test value
	 * @param int    $max      Tha maximum test value
	 * @return int Return the new auto group rule id
	 */
	public function create_autogroup_rule($type, $group_id, $min, $max)
	{
		// Get the type id
		$sql = "SELECT autogroups_type_id AS type_id
			FROM phpbb_autogroups_types
			WHERE autogroups_type_name = '" . $this->db->sql_escape('phpbb.autogroups.type.' . $type) . "'";
		$result = $this->db->sql_query($sql);
		$type_id = $this->db->sql_fetchfield('type_id');
		$this->db->sql_freeresult($result);

		if (!$type_id)
		{
			$this->db->sql_query('INSERT INTO phpbb_autogroups_types ' . $this->db->sql_build_array('INSERT', array(
				'autogroups_type_name' => 'phpbb.autogroups.type.' . $type
			)));

			$type_id = (int) $this->db->sql_nextid();
		}

		// Build the data array to insert
		$data = array(
			'autogroups_type_id'	=> $type_id,
			'autogroups_min_value'	=> $min,
			'autogroups_max_value'	=> $max,
			'autogroups_group_id'	=> $group_id,
			'autogroups_default'	=> true,
			'autogroups_notify'		=> true,
		);

		// Insert the data array
		$this->db->sql_query('INSERT INTO phpbb_autogroups_rules ' . $this->db->sql_build_array('INSERT', $data));

		$this->purge_cache();

		return (int) $this->db->sql_nextid();
	}

	/**
	 * Assert a user is in a user group
	 *
	 * @param int    $user_id    The user id
	 * @param string $group_name The name of the group
	 */
	public function assertInGroup($user_id, $group_name)
	{
		$crawler = self::request('GET', "memberlist.php?mode=viewprofile&u=$user_id&sid={$this->sid}");
		$this->assertContains($group_name, $crawler->filter('select')->text(), "The group $group_name could not be found in the set of user groups.");
	}

	/**
	 * Assert a user is not in a user group
	 *
	 * @param int    $user_id    The user id
	 * @param string $group_name The name of the group
	 */
	public function assertNotInGroup($user_id, $group_name)
	{
		$crawler = self::request('GET', "memberlist.php?mode=viewprofile&u=$user_id&sid={$this->sid}");
		$this->assertNotContains($group_name, $crawler->filter('select')->text(), "The group $group_name still exists in the set of user groups.");
	}

	/**
	 * Reset the auto groups cron job last run time
	 */
	public function reset_cron()
	{
		$sql = "UPDATE phpbb_config
			SET config_value = 0
			WHERE config_name = 'autogroups_last_run'";
		$this->db->sql_query($sql);

		$this->purge_cache();
	}
}
