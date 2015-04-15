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

/**
 * @group functional
 */
class acp_test extends autogroups_base
{
	/**
	 * Test the auto groups posts type
	 *
	 * @return null
	 */
	public function test_autogroups_acp()
	{
		// Load the lang files
		$this->add_lang_ext('phpbb/autogroups', array(
			'info_acp_autogroups',
			'autogroups_acp',
		));

		// Set up some test data
		$test_data = array(
			'type_lang' => 'AUTOGROUPS_TYPE_BIRTHDAYS',
			'group_name' => 'test-acp-group',
			'min' => '10',
			'max' => '20',
		);

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		$this->assertNotNull($group_id, 'Failed to create a test group.');

		// Test the module is in place
		$crawler = self::request('GET', "adm/index.php?i=acp_groups&icat=12&mode=manage&sid={$this->sid}");
		$this->assertContainsLang('ACP_AUTOGROUPS_MANAGE', $crawler->filter('#menu')->text());

		// Test the module loads and displays correctly
		$crawler = self::request('GET', "adm/index.php?i=\\phpbb\\autogroups\\acp\\autogroups_module&mode=manage&sid={$this->sid}");
		$this->assertContainsLang('ACP_AUTOGROUPS_MANAGE', $crawler->filter('#main h1')->text());

		// Test the create new auto group rule page loads and displays correctly
		$form = $crawler->selectButton($this->lang('ACP_AUTOGROUPS_CREATE_RULE'))->form();
		$crawler = self::submit($form);
		$this->assertContainsLang('ACP_AUTOGROUPS_ADD', $crawler->filter('#acp_autogroups_group')->text());

		// Submit new auto group data
		$form = $crawler->selectButton($this->lang('SUBMIT'))->form(array(
			'autogroups_group_id'	=> $group_id,
			'autogroups_type_id'	=> 1, // birthdays type
			'autogroups_min_value'	=> $test_data['min'],
			'autogroups_max_value'	=> $test_data['max'],
			'autogroups_default'	=> 1,
			'autogroups_notify'		=> 1,
		));
		$crawler = self::submit($form);

		// Assert the addition was a success
		$this->assertGreaterThan(0, $crawler->filter('.successbox')->count());
		$this->assertContainsLang('ACP_AUTOGROUPS_SUBMIT_SUCCESS', $crawler->text());

		// Test the new auto group is displayed in the list of auto groups
		$crawler = self::request('GET', "adm/index.php?i=\\phpbb\\autogroups\\acp\\autogroups_module&mode=manage&sid={$this->sid}");
		$this->assertContains($test_data['group_name'], $crawler->filter('#main table tbody tr td')->eq(0)->text());
		$this->assertContainsLang($test_data['type_lang'], $crawler->filter('#main table tbody tr td')->eq(1)->text());
		$this->assertContains($test_data['min'], $crawler->filter('#main table tbody tr td')->eq(2)->text());
		$this->assertContains($test_data['max'], $crawler->filter('#main table tbody tr td')->eq(3)->text());
	}
}
