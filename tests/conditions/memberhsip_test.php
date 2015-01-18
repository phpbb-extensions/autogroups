<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\conditions;

/**
 * Run tests on the membership type class.
 */
class membership_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.membership';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\membership($this->phpbb_container, $this->config, $this->db, $this->user, 'phpbb_autogroups_rules', 'phpbb_autogroups_types', $this->root_path, $this->php_ext);
	}

	/**
	 * Data for test_check_membership
	 */
	public function check_membership_test_data()
	{
		/*
		 * Mock settings in the database:
		 * Membership days between 0 - 3 adds to group 4 (no default)
		 * Membership days between 10 - 50 adds to group 2 as default
		 * Membership days between 50 - 100 adds to group 3 (no default)
		 * Membership days between 500 - 0 (forever) adds to group 6 (no default)
		 *
		 * User 1 is already a member of groups 1 and 5 (1 is default)
		 * User 2 is already a member of groups 1 and 2 (2 is default)
		 */
		return array(
			array(
				array(
					1 => 15, // user 1 has 15 days
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
				),
				array(
					1 => 2, // default
				),
			),
			array(
				array(
					1 => 150, // user 1 has 150 days
				),
				array(
					1 => array(1, 5), // user 1 added to no new groups
				),
				array(
					1 => 1, // default
				),
			),
			array(
				array(
					1 => 5, // user 1 has 5 days
					2 => 50, // user 2 has 50 days
				),
				array(
					1 => array(1, 5), // user 1 added to no new groups
					2 => array(1, 2, 3), // user 2 added to group 3
				),
				array(
					1 => 1, // default
					2 => 2, // default
				),
			),
			array(
				array(
					1 => 50, // user 1 has 50 days
					2 => 5, // user 2 has 5 days
				),
				array(
					1 => array(1, 2, 3, 5), // user 1 added to groups 2 and 3
					2 => array(1), // user 2 removed from group 2
				),
				array(
					1 => 2, // default
					2 => 1, // default
				),
			),
			array(
				array(
					1 => 0, // user 1 has 0 days
					2 => 501, // user 2 has 501 days
				),
				array(
					1 => array(1, 4, 5), // user 1 added to group 4
					2 => array(1, 6), // user 2 removed from group 2 and added to group 6
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
	 * Test the membership check method
	 *
	 * @dataProvider check_membership_test_data
	 */
	public function test_check_membership($user_data, $expected, $default)
	{
		// Update the users regdate timestamp
		foreach ($user_data as $user_id => $membership_days)
		{
			$this->helper_update_user_regdate($user_id, $membership_days);
		}

		// Instantiate the membership condition
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
	 * Test the membership check method by passing it the user ids
	 *
	 * @dataProvider check_membership_test_data
	 */
	public function test_check_membership_alt($user_data, $expected, $default)
	{
		// Update the users regdate timestamp
		foreach ($user_data as $user_id => $membership_days)
		{
			$this->helper_update_user_regdate($user_id, $membership_days);
		}

		// Instantiate the membership condition
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
	 * Update the database with new registration timestamp user
	 */
	public function helper_update_user_regdate($user_id, $membership_days)
	{
		$sql = 'UPDATE phpbb_users
			SET user_regdate = ' . (int) (time() - ($membership_days * 86400)) . '
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
