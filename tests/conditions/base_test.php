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
 * Run tests on the type base class.
 * Because it is abstracted we will base these tests from the posts class.
 */
class base_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.posts';

	/**
	 * Data for test_get_group_rules
	 */
	public function get_group_rules_test_data()
	{
		return array(
			array(
				'phpbb.autogroups.type.posts',
				array(
					array(
						'autogroups_id' 		=> 1,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 10,
						'autogroups_max_value'	=> 20,
						'autogroups_group_id'	=> 2,
						'autogroups_default'	=> 1,
					),
					array(
						'autogroups_id' 		=> 2,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 100,
						'autogroups_max_value'	=> 200,
						'autogroups_group_id'	=> 3,
						'autogroups_default'	=> 1,
					),
					array(
						'autogroups_id' 		=> 3,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 500,
						'autogroups_max_value'	=> 0,
						'autogroups_group_id'	=> 4,
						'autogroups_default'	=> 0,
					),
				),
			),
		);
	}

	/**
	 * Test the get_group_rules method
	 *
	 * @dataProvider get_group_rules_test_data
	 */
	public function test_get_group_rules($type, $expected)
	{
		// Instantiate the condition
		$condition = $this->get_condition();

		$result = $condition->get_group_rules($type);
		$this->assertEquals($expected, $result);
	}

	/**
	 * Data for test_get_users_groups
	 */
	public function get_users_groups_test_data()
	{
		return array(
			array(
				1,
				array(
					1 => array(1, 5),
				),
			),
			array(
				2,
				array(
					2 => array(1, 2),
				),
			),
			array(
				array(1, 2),
				array(
					1 => array(1, 5),
					2 => array(1, 2),
				),
			),
			array(
				array(),
				array(),
			),
		);
	}

	/**
	 * Test the get_users_groups method
	 *
	 * @dataProvider get_users_groups_test_data
	 */
	public function test_get_users_groups($user_id, $expected)
	{
		// Instantiate the condition
		$condition = $this->get_condition();

		if (!is_array($user_id))
		{
			$user_id = array($user_id);
		}

		// Get the user's groups
		$result = $condition->get_users_groups($user_id);

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $result);
	}

	/**
	 * Data for test_add_user_to_groups
	 */
	public function add_user_to_groups_test_data()
	{
		return array(
			array(
				// add user 1 to group 2
				array(
					2 => 1,
				),
				true, // default
				// expect user 1 in groups 1, 2, 5
				array(
					1 => array(1, 5, 2),
				),
			),
			array(
				// add user 1 to group 2
				array(
					2 => 1,
				),
				false, // default
				// expect user 1 in groups 1, 2, 5
				array(
					1 => array(1, 5, 2),
				),
			),
			array(
				// add user 2 to multiple groups, no defaults
				array(
					3 => 2,
					4 => 2,
					5 => 2,
				),
				false, // default
				// expect user 2 in all groups
				array(
					2 => array(1, 2, 3, 4, 5),
				),
			),
			array(
				// add user 2 to multiple groups
				array(
					3 => 2,
					4 => 2,
					5 => 2,
				),
				true, // default
				// expect user 2 in all groups
				array(
					2 => array(1, 2, 3, 4, 5),
				),
			),
			array(
				// add multiple users to multiple groups
				array(
					2 => array(1, 2),
					3 => array(1, 2),
					4 => array(1, 2),
					5 => array(1, 2),
				),
				true, // default
				// expect user 1 in all groups, user 2 in all groups
				array(
					1 => array(1, 5, 2, 3, 4),
					2 => array(1, 2, 3, 4, 5),
				),
			),
		);
	}

	/**
	 * Test the add_user_to_groups method
	 *
	 * @dataProvider add_user_to_groups_test_data
	 */
	public function test_add_user_to_groups($groups_data, $default, $expected)
	{
		// Instantiate the condition
		$condition = $this->get_condition();

		// Add the user to groups
		$condition->add_user_to_groups($groups_data, $default);

		// Get the user's groups
		foreach ($groups_data as $user_ids)
		{
			$user_groups = $condition->get_users_groups($user_ids);

			// Assert the user's groups are as expected
			$this->assertEquals($expected, $user_groups);
		}
	}

	/**
	 * Data for test_remove_user_from_groups
	 */
	public function remove_user_from_groups_test_data()
	{
		return array(
			array(
				// remove user 1 from group 5
				array(
					5 => 1,
				),
				// expect user 1 in group 1
				array(
					1 => array(1),
				),
			),
			array(
				// remove user 1 from all their groups
				array(
					1 => 1,
					5 => 1,
				),
				// expect user 1 in no group
				array(),
			),
			array(
				// remove user 2 from all their groups
				array(
					1 => 2,
					2 => 2,
				),
				// expect user 2 in no group
				array(),
			),
			array(
				// remove user 2 from a group they do not belong to (5)
				array(
					5 => 2,
				),
				// expect user 2 in group 1 and 2
				array(
					2 => array(1, 2),
				),
			),
			array(
				// remove user 2 from a group they do (2) and do not (5) belong to
				array(
					2 => 2,
					5 => 2,
				),
				// expect user 2 in group 1
				array(
					2 => array(1),
				),
			),
			array(
				// remove users 1 and 2 from groups 2 and 5
				array(
					2 => array(1, 2),
					5 => array(1, 2),
				),
				// expect user 1 in group 1, user 2 in group 1
				array(
					1 => array(1),
					2 => array(1),
				),
			),
		);
	}

	/**
	 * Test the remove_user_from_groups method
	 *
	 * @dataProvider remove_user_from_groups_test_data
	 */
	public function test_remove_user_from_groups($groups_data, $expected)
	{
		// Instantiate the condition
		$condition = $this->get_condition();

		// Remove the user from groups
		$condition->remove_user_from_groups($groups_data);

		// Get the user's groups
		foreach ($groups_data as $user_ids)
		{
			$user_groups = $condition->get_users_groups($user_ids);

			// Assert the user's groups are as expected
			$this->assertEquals($expected, $user_groups);
		}
	}
}
