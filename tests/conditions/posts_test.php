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
*
*/
class posts_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.posts';

	public function setUp()
	{
		parent::setUp();

		$this->condition = new \phpbb\autogroups\conditions\type\posts($this->db, $this->user, 'phpbb_autogroups_rules', 'phpbb_autogroups_types', $this->root_path, $this->php_ext);
	}

	/**
	 * Data for test_check
	 */
	public function check_test_data()
	{
		/*
		 * Mock settings in the database:
		 * Post count between 10 - 20 adds to group 2 as default
		 * Post count between 100 - 200 adds to group 3 as default
		 * Post count between 500 - unlimited adds to group 4 (no default)
		 *
		 * User 1 is already a member of groups 1 and 5 (1 is default)
		 * User 2 is already a member of groups 1 and 2 (2 is default)
		 */
		return array(
			array(
				1, // user id
				10, // posts
				array(1, 5, 2), // user added to group 2
				2, // default
			),
			array(
				1, // user id
				0, // posts
				array(1, 5), // user not added to any group
				1, // default
			),
			array(
				1, // user id
				21, // posts
				array(1, 5), // user not added to any group
				1, // default
			),
			array(
				1, // user id
				100, // posts
				array(1, 5, 3), // user added to group 3
				3, // default
			),
			array(
				1, // user id
				200, // posts
				array(1, 5, 3), // user added to group 3
				3, // default
			),
			array(
				1, // user id
				500, // posts
				array(1, 5, 4), // user added to group 4
				1, // default
			),
			array(
				1, // user id
				1000, // posts
				array(1, 5, 4), // user added to group 4
				1, // default
			),
			array(
				2, // user id
				15, // posts
				array(1, 2), // user remains in groups 1 and 2
				2, // default
			),
			array(
				2, // user id
				150, // posts
				array(1, 3), // user removed from group 2, added to group 3
				3, // default
			),
			array(
				2, // user id
				1500, // posts
				array(1, 4), // user removed from group 2, added to group 4
				1, // default
			),
		);
	}

	/**
	 * Test the check method
	 *
	 * @dataProvider check_test_data
	 */
	public function test_check($user_id, $post_count, $expected, $default)
	{
		// Update the user post count
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = ' . (int) $post_count . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		$condition = $this->get_condition();

		// Check the user and perform auto group
		$condition->check($user_id);

		// Set the user id
		$this->user->data['user_id'] = $user_id;

		// Get the user's groups
		$result = $condition->get_users_groups();

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $result);

		// Assert the user's default group id is as expected
		$sql = 'SELECT group_id from phpbb_users WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$this->assertEquals($default, $this->db->sql_fetchfield('group_id', false, $result));
	}
}
