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
 * This abstract class contains common tests to be
 * run on the condition type classes.
 */
abstract class type_test_case extends base
{
	/**
	 * Test the condition type's check method
	 *
	 * @dataProvider check_condition_test_data
	 */
	public function test_check_condition($user_data, $expected, $default, $options)
	{
		// Prepare the users data for checking
		foreach ($user_data as $user_id => $data)
		{
			$this->helper_update_user_data($user_id, $data);
		}

		// Instantiate the condition class
		$condition = $this->get_condition();

		// Get the users to check by default
		// ($options is used in the posts test)
		$users = $condition->get_users_for_condition($options);

		// Perform the check on the users
		$condition->check($users);

		// Assert the user's groups are as expected
		$result = $this->helper->get_users_groups(array_keys($user_data));
		foreach ($result as $key => $ary)
		{
			sort($result[$key]);
		}
		$this->assertEquals($expected, $result);

		// Assert the user's default group id is as expected
		$this->assertEquals($default, $this->helper_default_groups(array_keys($user_data)));
	}

	/**
	 * Test the condition type's check method by passing it the user ids
	 *
	 * @dataProvider check_condition_test_data
	 */
	public function test_check_condition_with_users($user_data, $expected, $default)
	{
		// Update the users regdate timestamp
		foreach ($user_data as $user_id => $data)
		{
			$this->helper_update_user_data($user_id, $data);
		}

		// Instantiate the condition class
		$condition = $this->get_condition();

		// Get the users to check passing user ids manually
		$users = $condition->get_users_for_condition(array(
			'users' => array_keys($user_data),
		));

		// Perform the check on the users
		$condition->check($users);

		// Assert the user's groups are as expected
		$result = $this->helper->get_users_groups(array_keys($user_data));
		foreach ($result as $key => $ary)
		{
			sort($result[$key]);
		}
		$this->assertEquals($expected, $result);

		// Assert the user's default group id is as expected
		$this->assertEquals($default, $this->helper_default_groups(array_keys($user_data)));
	}

	/**
	 * Get the default groups for users
	 *
	 * @param array $user_ids
	 * @return array Array of user ids and their default group ids
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

	/**
	 * Update the database with new user data
	 *
	 * @param int $user_id
	 * @param int $data
	 */
	abstract public function helper_update_user_data($user_id, $data);
}
