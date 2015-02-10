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
 */
class base_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.posts';

	/**
	 * Because it is abstracted we will base these tests from the posts class.
	 * @return \phpbb\autogroups\conditions\type\posts
	 */
	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\posts($this->phpbb_container, $this->config, $this->db, $this->user, 'phpbb_autogroups_rules', 'phpbb_autogroups_types', $this->root_path, $this->php_ext);
	}

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
						'autogroups_notify'		=> 0,
					),
					array(
						'autogroups_id' 		=> 2,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 100,
						'autogroups_max_value'	=> 200,
						'autogroups_group_id'	=> 3,
						'autogroups_default'	=> 1,
						'autogroups_notify'		=> 0,
					),
					array(
						'autogroups_id' 		=> 3,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 500,
						'autogroups_max_value'	=> 0,
						'autogroups_group_id'	=> 4,
						'autogroups_default'	=> 0,
						'autogroups_notify'		=> 0,
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
	 * Data for test_get_default_exempt_users
	 */
	public function get_default_exempt_users_test_data()
	{
		return array(
			array(
				array(),
				array(),
			),
			array(
				array(1),
				array(1, 2),
			),
			array(
				array(2),
				array(2),
			),
			array(
				array(3),
				array(),
			),
			array(
				array(1, 2, 3),
				array(1, 2),
			),
		);
	}

	/**
	 * Test the get_default_exempt_users method
	 *
	 * @dataProvider get_default_exempt_users_test_data
	 */
	public function test_get_default_exempt_users($groups, $expected)
	{
		$this->config['autogroups_default_exempt'] = serialize($groups);

		$condition = $this->get_condition();

		// Get the user's groups
		$result = $condition->get_default_exempt_users();

		// Assert the user's groups are as expected
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
	 * Data for test_add_users_to_group
	 */
	public function add_users_to_group_test_data()
	{
		return array(
			array(
				array(1), // add user 1 to group 2, make group 2 default
				2,
				true,
				array(
					1 => array(1, 5, 2), // expect user 1 in groups 1, 2, 5
				),
			),
			array(
				array(1), // add user 1 to group 3, no default
				3,
				false,
				array(
					1 => array(1, 5, 3), // expect user 1 in groups 1, 3, 5
				),
			),
			array(
				array(2), // add user 2 to group 1 (already a member), make group 1 default
				1,
				true,
				array(
					2 => array(1, 2), // expect user 2 in groups 1, 2
				),
			),
			array(
				array(1, 2), // add multiple users to group 4, make group 4 default
				4,
				true,
				array(
					1 => array(1, 5, 4), // expect user 1 in groups 1, 4, 5
					2 => array(1, 2, 4), // expect user 1 in groups 1, 2, 4
				),
			),
			array(
				array(1, 2), // add multiple users to group 5, no default
				5,
				false,
				array(
					1 => array(1, 5), // expect user 1 in groups 1, 5
					2 => array(1, 2, 5), // expect user 1 in groups 1, 2, 5
				),
			),
		);
	}

	/**
	 * Data for test_prepare_users_for_query
	 */
	public function prepare_users_for_query_test_data()
	{
		return array(
			array(1, array(1)),
			array(array(1, 2), array(1, 2)),
			array('1', array(1)),
			array(array(1, '2', 'foobar', '', true, false), array(1, 2, 0, 0, 1, 0)),
			array('', array(0)),
			array(array(), array()),
		);
	}

	/**
	 * Test the prepare_users_for_query method
	 *
	 * @dataProvider prepare_users_for_query_test_data
	 */
	public function test_prepare_users_for_query($user_ids, $expected)
	{
		// Instantiate the condition
		$condition = $this->get_condition();

		$this->assertEquals($expected, $condition->prepare_users_for_query($user_ids));
	}

	/**
	 * Test the add_users_to_group method
	 *
	 * @dataProvider add_users_to_group_test_data
	 */
	public function test_add_users_to_group($user_id_ary, $group_id, $default, $expected)
	{
		// Prepare data
		$group_rule_data = array(
			'autogroups_group_id' 	=> $group_id,
			'autogroups_default'	=> $default,
		);

		// Instantiate the condition
		$condition = $this->get_condition();

		// Add the user to groups
		$condition->add_users_to_group($user_id_ary, $group_rule_data);

		// Get the user's groups
		$user_groups = $condition->get_users_groups($user_id_ary);

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $user_groups);
	}

	/**
	 * Data for test_remove_users_from_group
	 */
	public function remove_users_from_group_test_data()
	{
		return array(
			array(
				// remove user 1 from group 5
				array(1),
				5,
				array(
					1 => array(1), // expect user 1 in group 1
				),
			),
			array(
				array(2), // remove user 2 from a group they do not belong to (5)
				5,
				array(
					2 => array(1, 2), // expect user 2 in group 1 and 2
				),
			),
			array(
				array(1, 2), // remove users 1 and 2 from group 2
				2,
				array(
					1 => array(1, 5), // expect user 1 in groups 1 and 5
					2 => array(1), // expect user 2 in group 1
				),
			),
			array(
				array(1, 2), // remove users 1 and 2 from group 5
				5,
				array(
					1 => array(1), // expect user 1 in group 1
					2 => array(1, 2), // expect user 2 in groups 1 and 2
				),
			),
		);
	}

	/**
	 * Test the remove_users_from_group method
	 *
	 * @dataProvider remove_users_from_group_test_data
	 */
	public function test_remove_users_from_group($user_id_ary, $group_id, $expected)
	{
		// Prepare data
		$group_rule_data = array(
			'autogroups_group_id' 	=> $group_id,
		);

		// Instantiate the condition
		$condition = $this->get_condition();

		// Remove the user from groups
		$condition->remove_users_from_group($user_id_ary, $group_rule_data);

		// Get the user's groups
		$user_groups = $condition->get_users_groups($user_id_ary);

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $user_groups);
	}
}
