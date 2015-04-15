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
class membership_test extends autogroups_base
{
	/**
	 * Test the auto groups membership type
	 *
	 * @return null
	 */
	public function test_autogroups_memberships()
	{
		$test_data = array(
			'type' => 'membership',
			'group_name' => 'test-membership',
			'min' => 1,
			'max' => 10,
		);

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		$this->assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($test_data['type'], $group_id, $test_data['min'], $test_data['max']);
		$this->assertNotNull($autogroup_id, 'Failed to create an auto group rule set.');

		// Run the cron job for a user with 2 days of membership, should add the user to the group
		$this->update_user_regdate(2, 2);
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->assertInGroup(2, $test_data['group_name']);

		$this->reset_cron();

		// Run the cron job for a user with 20 days of membership, should remove the user from the group
		$this->update_user_regdate(2, 20);
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->assertNotInGroup(2, $test_data['group_name']);
	}

	/**
	 * Update a user's registration date
	 *
	 * @param int $user_id The user to update
	 * @param int $days    The number of days ago membership began
	 * @return null
	 */
	protected function update_user_regdate($user_id, $days)
	{
		$time = strtotime("$days days ago");

		$sql = 'UPDATE phpbb_users
			SET user_regdate = ' . $time . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Reset the auto groups cron job last run time
	 *
	 * @return null
	 */
	protected function reset_cron()
	{
		$sql = "UPDATE phpbb_config
			SET config_value = 0
			WHERE config_name = 'autogroups_last_run'";
		$this->db->sql_query($sql);
	}
}
