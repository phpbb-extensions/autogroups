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
class inactive_test extends autogroups_base
{
	/**
	 * Test the auto groups inactive type
	 */
	public function test_autogroups_memberships()
	{
		$this->add_lang('acp/users');

		$test_data = array(
			'type' => 'inactive',
			'group_name' => 'test-inactive',
			'min' => 0,
			'max' => 10,
		);

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		$this->assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($test_data['type'], $group_id, $test_data['min'], $test_data['max']);
		$this->assertNotFalse($autogroup_id, 'Failed to create an auto group rule set.');

		// Create a new user
		$user_1 = $this->create_user('Inactive-test-user');

		// Run the cron job, should add the user to the group
		$this->update_user_inactive($user_1);
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->assertInGroup($user_1, $test_data['group_name']);

		// Activate the user
		$this->user_activation('Inactive-test-user', 'activated');
		$this->assertNotInGroup($user_1, $test_data['group_name']);

		// Deactivate the user
		$this->user_activation('Inactive-test-user', 'deactived');
		$this->assertInGroup($user_1, $test_data['group_name']);
	}

	/**
	 * Activate or deactivate a user
	 *
	 * @param string $mode activated|deactivated
	 */
	protected function user_activation($username, $mode)
	{
		$crawler = self::request('GET', 'adm/index.php?i=acp_users&mode=overview&sid=' . $this->sid);
		$this->assertContainsLang('FIND_USERNAME', $crawler->filter('html')->text());
		$form = $crawler->selectButton('Submit')->form();
		$crawler = self::submit($form, array('username' => $username));
		$this->assertContainsLang('USER_TOOLS', $crawler->filter('html')->text());
		$form = $crawler->filter('input[name=update]')->selectButton('Submit')->form();
		$crawler = self::submit($form, array('action' => 'active'));
		$this->assertContainsLang('USER_ADMIN_' . strtoupper($mode), $crawler->filter('html')->text());
	}

	/**
	 * Update a user's registration date
	 *
	 * @param int $user_id The user to update
	 * @return $this
	 */
	protected function update_user_inactive($user_id)
	{
		$sql_ary = array(
			'user_type' => USER_INACTIVE,
			'user_inactive_time' => time(),
		);

		$sql = 'UPDATE phpbb_users
			SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		$this->purge_cache();

		return $this;
	}

	/**
	 * Reset the auto groups cron job last run time
	 */
	protected function reset_cron()
	{
		$sql = "UPDATE phpbb_config
			SET config_value = 0
			WHERE config_name = 'autogroups_last_run'";
		$this->db->sql_query($sql);

		$this->purge_cache();
	}
}
