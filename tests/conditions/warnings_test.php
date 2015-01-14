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
 * Run tests on the posts type class.
 */
class warnings_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.warnings';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\warnings($this->phpbb_container, $this->config, $this->db, $this->user, 'phpbb_autogroups_rules', 'phpbb_autogroups_types', $this->root_path, $this->php_ext);
	}

	/**
	 * Data for test_check_warnings
	 */
	public function check_warnings_test_data()
	{
		/*
		 * Mock settings in the database:
		 * Warnings between 0 - 1 adds to group 4 (no default)
		 * Warnings between 2 - 3 adds to group 2 as default
		 * Warnings between 4 - 5 adds to group 3 (no default)
		 * Warnings between 6 - 0 (forever) adds to group 6 (no default)
		 *
		 * User 1 is already a member of groups 1 and 5 (1 is default)
		 * User 2 is already a member of groups 1 and 2 (2 is default)
		 */
		return array(
			array(
				array(
					1 => 1, // user 1 has 1 warning
				),
				array(
					1 => array(1, 4, 5), // user 1 added to group 4
				),
				array(
					1 => 1, // default
				),
			),
			array(
				array(
					2 => 2, // user 2 has 2 warnings
				),
				array(
					2 => array(1, 2), // user 2 added to no new groups
				),
				array(
					2 => 2, // default
				),
			),
			array(
				array(
					1 => 3, // user 1 has 3 warnings
					2 => 4, // user 2 has 4 warnings
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1, 3), // user 2 added to group 3, removed from group 2
				),
				array(
					1 => 2, // default
					2 => 1, // default
				),
			),
			array(
				array(
					1 => 6, // user 1 has 6 warnings
					2 => 7, // user 2 has 7 warnings
				),
				array(
					1 => array(1, 5, 6), // user 1 added to group 6
					2 => array(1, 6), // user 2 added to group 6, removed from group 2
				),
				array(
					1 => 1, // default
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
	 * Test the warnings check method
	 *
	 * @dataProvider check_warnings_test_data
	 */
	public function test_check_warnings($user_data, $expected, $default)
	{
		// Update the users warnings
		foreach ($user_data as $user_id => $warnings)
		{
			$this->helper_update_user_warnings($user_id, $warnings);
		}

		// Instantiate the warnings condition
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
	 * Test the warnings check method by passing it the user ids
	 *
	 * @dataProvider check_warnings_test_data
	 */
	public function test_check_warnings_alt($user_data, $expected, $default)
	{
		// Update the users regdate timestamp
		foreach ($user_data as $user_id => $warnings)
		{
			$this->helper_update_user_warnings($user_id, $warnings);
		}

		// Instantiate the warnings condition
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
	 * Update the database with new user warnings
	 */
	public function helper_update_user_warnings($user_id, $warnings)
	{
		$sql = 'UPDATE phpbb_users
			SET user_warnings = ' . (int) $warnings . '
			WHERE user_id = ' . (int) $user_id;
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
