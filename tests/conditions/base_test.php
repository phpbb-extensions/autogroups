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
		return new \phpbb\autogroups\conditions\type\posts(
			$this->phpbb_container,
			$this->db,
			$this->lang,
			'phpbb_autogroups_rules',
			'phpbb_autogroups_types',
			$this->root_path,
			$this->php_ext
		);
	}

	/**
	 * Data for test_get_group_rules
	 */
	public static function get_group_rules_test_data()
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
						'autogroups_notify'		=> 1,
						'autogroups_type_name'	=> 'phpbb.autogroups.type.posts',
						'autogroups_excluded_groups' => '',
					),
					array(
						'autogroups_id' 		=> 2,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 100,
						'autogroups_max_value'	=> 200,
						'autogroups_group_id'	=> 3,
						'autogroups_default'	=> 1,
						'autogroups_notify'		=> 0,
						'autogroups_type_name'	=> 'phpbb.autogroups.type.posts',
						'autogroups_excluded_groups' => '',
					),
					array(
						'autogroups_id' 		=> 3,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 500,
						'autogroups_max_value'	=> 0,
						'autogroups_group_id'	=> 4,
						'autogroups_default'	=> 0,
						'autogroups_notify'		=> 0,
						'autogroups_type_name'	=> 'phpbb.autogroups.type.posts',
						'autogroups_excluded_groups' => '',
					),
					array(
						'autogroups_id' 		=> 4,
						'autogroups_type_id'	=> 1,
						'autogroups_min_value'	=> 300,
						'autogroups_max_value'	=> 400,
						'autogroups_group_id'	=> 4,
						'autogroups_default'	=> 0,
						'autogroups_notify'		=> 0,
						'autogroups_type_name'	=> 'phpbb.autogroups.type.posts',
						'autogroups_excluded_groups' => '[1,2]',
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
		self::assertEquals($expected, $result);
	}

	/**
	 * Data for test_add_users_to_group
	 */
	public static function add_users_to_group_test_data()
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
					2 => array(1, 2, 4), // expect user 2 in groups 1, 2, 4
				),
			),
			array(
				array(1, 2), // add multiple users to group 5, no default
				5,
				false,
				array(
					1 => array(1, 5), // expect user 1 in groups 1, 5
					2 => array(1, 2, 5), // expect user 2 in groups 1, 2, 5
				),
			),
		);
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
			'autogroups_notify'		=> false,
		);

		// Instantiate the condition
		$condition = $this->get_condition();

		// Add the user to groups
		$condition->add_users_to_group($user_id_ary, $group_rule_data);

		// Get the user's groups
		$user_groups = $this->helper->get_users_groups($user_id_ary);

		// Assert the user's groups are as expected
		self::assertEquals($expected, $user_groups);
	}

	/**
	 * Data for test_remove_users_from_group
	 */
	public static function remove_users_from_group_test_data()
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
			array(
				array(),
				5,
				array(),
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
			'autogroups_notify'		=> false,
		);

		// Instantiate the condition
		$condition = $this->get_condition();

		// Remove the user from groups
		$condition->remove_users_from_group($user_id_ary, $group_rule_data);

		// Get the user's groups
		$user_groups = $this->helper->get_users_groups($user_id_ary);

		// Assert the user's groups are as expected
		self::assertEquals($expected, $user_groups);
	}

	public function test_check_initialises_groups_for_unknown_user()
	{
		$this->get_condition()->check(array(
			999 => array('user_posts' => 0),
		));

		self::assertSame(array(), $this->helper->get_users_groups(array(999)));
	}

	public function test_filter_users_keeps_only_users_failing_other_rule()
	{
		$other_condition = new filter_condition();
		$this->phpbb_container->set('phpbb.autogroups.type.other', $other_condition);

		$method = new \ReflectionMethod($this->get_condition(), 'filter_users');
		$method->setAccessible(true);
		$actual = $method->invoke(
			$this->get_condition(),
			array(2, 3),
			array('autogroups_group_id' => 4, 'autogroups_type_id' => 1),
			array(array(
				'autogroups_group_id' => 4,
				'autogroups_type_id' => 2,
				'autogroups_type_name' => 'phpbb.autogroups.type.other',
				'autogroups_min_value' => 10,
				'autogroups_max_value' => 20,
			))
		);

		self::assertSame(array(1 => 3), $actual);
	}

	public function timestamp_data()
	{
		return array(
			'empty timestamp' => array(0, null),
			'five days ago' => array(strtotime('5 days ago'), 5),
		);
	}

	/**
	 * @dataProvider timestamp_data
	 */
	public function test_timestamp_to_days($timestamp, $expected)
	{
		$method = new \ReflectionMethod($this->get_condition(), 'timestamp_to_days');
		$method->setAccessible(true);

		self::assertSame($expected, $method->invoke($this->get_condition(), $timestamp));
	}

	public function test_days_to_timestamp()
	{
		$method = new \ReflectionMethod($this->get_condition(), 'days_to_timestamp');
		$method->setAccessible(true);

		self::assertEqualsWithDelta(strtotime('3 days ago'), $method->invoke($this->get_condition(), 3), 1);
	}
}

class filter_condition extends \phpbb\autogroups\conditions\type\posts
{
	public function __construct()
	{
	}

	public function get_users_for_condition($options = array())
	{
		return array(
			2 => array('user_posts' => 15),
			3 => array('user_posts' => 5),
		);
	}

	public function check_user_data($value, $group_rule)
	{
		return parent::check_user_data($value, $group_rule);
	}
}
