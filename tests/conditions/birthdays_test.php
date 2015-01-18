<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\conditions;

/**
 * Run tests on the birthdays type class.
 */
class birthdays_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.birthdays';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\birthdays($this->phpbb_container, $this->config, $this->db, $this->user, 'phpbb_autogroups_rules', 'phpbb_autogroups_types', $this->root_path, $this->php_ext);
	}

	/**
	 * Data for test_check_birthdays
	 */
	public function check_birthdays_test_data()
	{
		/*
		 * Mock settings in the database:
		 * Ages under 20 adds to group 2 as default
		 * Ages between 30 - 50 adds to group 3 (no default)
		 * Ages over 40 adds to group 4 (no default)
		 *
		 * User 1 is already a member of groups 1 and 5 (1 is default)
		 * User 2 is already a member of groups 1 and 2 (2 is default)
		 */
		return array(
			array(
				array(
					1 => 15,
					2 => 45,
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1, 3, 4), // user 2 added to group 3 and 4, removed from group 2
				),
				array(
					1 => 2, // default
					2 => 1, // default
				),
			),
			array(
				array(),
				array(),
				array(),
			),
		);
	}

	/**
	 * Test the birthdays check method
	 *
	 * @dataProvider check_birthdays_test_data
	 */
	public function test_check_birthdays($user_data, $expected, $default)
	{
		// Update the users birthdays
		foreach ($user_data as $user_id => $age)
		{
			$this->helper_update_user_birthdays($user_id, $age);
		}

		// Instantiate the birthdays condition
		$condition = $this->get_condition();

		// Get the users to check by default
		$users = $condition->get_users_for_condition();

		// Perform the check on the users
		$condition->check($users);

		// Assert the user's groups are as expected
		$result = $condition->get_users_groups(array_keys($user_data));
		foreach ($result as $key => $ary)
		{
			sort($result[$key]);
		}
		$this->assertEquals($expected, $result);

		// Assert the user's default group id is as expected
		$this->assertEquals($default, $this->helper_default_groups(array_keys($user_data)));
	}

	/**
	 * Test the birthdays check method by passing it the user ids
	 *
	 * @dataProvider check_birthdays_test_data
	 */
	public function test_check_birthdays_alt($user_data, $expected, $default)
	{
		// Update the users regdate timestamp
		foreach ($user_data as $user_id => $age)
		{
			$this->helper_update_user_birthdays($user_id, $age);
		}

		// Instantiate the birthdays condition
		$condition = $this->get_condition();

		// Get the users to check passing user ids manually
		$users = $condition->get_users_for_condition(array(
			'users' => array_keys($user_data),
		));

		// Perform the check on the users
		$condition->check($users);

		// Assert the user's groups are as expected
		$result = $condition->get_users_groups(array_keys($user_data));
		foreach ($result as $key => $ary)
		{
			sort($result[$key]);
		}
		$this->assertEquals($expected, $result);

		// Assert the user's default group id is as expected
		$this->assertEquals($default, $this->helper_default_groups(array_keys($user_data)));
	}

	/*
	 * Update the database with user ages (relative to current time)
	 */
	public function helper_update_user_birthdays($user_id, $age)
	{
		$now = getdate(time());
		$birthday = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], ($now['year'] - $age));

		$sql = "UPDATE phpbb_users
			SET user_birthday = '" . $this->db->sql_escape($birthday) . "'
			WHERE user_id = " . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/*
	 * Get the default groups for users
	 */
	public function helper_default_groups($user_ids)
	{
		$sql = 'SELECT user_id, group_id
			FROM phpbb_users
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids, false, true);
		$result = $this->db->sql_query($sql);

		$rowset = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[$row['user_id']] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $rowset;
	}
}
