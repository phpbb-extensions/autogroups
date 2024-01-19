<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\functional;

/**
 * @group functional
 */
class lastvisit_test extends autogroups_base
{
	/**
	 * Test the auto groups lastvisit type
	 */
	public function test_autogroups_memberships()
	{
		$this->add_lang('acp/users');

		$test_data = array(
			'type' => 'lastvisit',
			'group_name' => 'test-lastvisit',
			'min' => 10,
			'max' => 1000,
		);

		$test_user_id = $this->create_user('Bertie');

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		self::assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($test_data['type'], $group_id, $test_data['min'], $test_data['max']);
		self::assertNotNull($autogroup_id, 'Failed to create an auto group rule set.');

		// Run the cron job for a user with 20 days since last visit, should add the user to the group
		$this->update_user_lastvisit($test_user_id, 20)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->purge_cache();
		$this->assertInGroup($test_user_id, $test_data['group_name']);

		// Run the cron job for a user with 2 days since last visit, should remove the user from the group
		$this->update_user_lastvisit($test_user_id, 2)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->purge_cache();
		$this->assertNotInGroup($test_user_id, $test_data['group_name']);
	}

	/**
	 * Update a user's lastvisit date
	 *
	 * @param int $user_id The user to update
	 * @param int $days    The number of days ago membership began
	 * @return $this
	 */
	protected function update_user_lastvisit($user_id, $days)
	{
		$time = strtotime("$days days ago");

		$sql = 'UPDATE phpbb_users
			SET user_lastvisit = ' . $time . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		$this->purge_cache();

		return $this;
	}
}
