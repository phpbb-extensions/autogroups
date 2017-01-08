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
 * Run tests on the posts type class.
 */
class posts_test extends type_test_case
{
	protected $condition_type = 'phpbb.autogroups.type.posts';

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
	 * Mock settings in the database:
	 * Post count between 10 - 20 adds to group 2 as default
	 * Post count between 100 - 200 adds to group 3 as default
	 * Post count between 500 - unlimited adds to group 4 (no default)
	 *
	 * User 1 is already a member of groups 1 and 5 (1 is default)
	 * User 2 is already a member of groups 1 and 2 (2 is default)
	 * User 3 is already a member of group 5 (5 is default)
	 *
	 * @return array Array of test data
	 */
	public function check_condition_test_data()
	{
		return array(
			array(
				array(1 => 10), // user 1 has 10 posts
				array(1 => array(1, 2, 5)), // user added to group 2
				array(1 => 2), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 0), // user 1 has 0 posts
				array(1 => array(1, 5)), // user not added to any group
				array(1 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 21), // user 1 has 21 posts
				array(1 => array(1, 5)), // user not added to any group
				array(1 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 21), // user 1 has 21 posts
				array(1 => array(1, 5)), // user not added to any group
				array(1 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 100), // user 1 has 100 posts
				array(1 => array(1, 3, 5)), // user added to group 3
				array(1 => 3), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 200), // user 1 has 200 posts
				array(1 => array(1, 3, 5)), // user added to group 3
				array(1 => 3), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 500), // user 1 has 500 posts
				array(1 => array(1, 4, 5)), // user added to group 4
				array(1 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(1 => 1000), // user 1 has 1000 posts
				array(1 => array(1, 4, 5)), // user added to group 4
				array(1 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(2 => 15), // user 2 has 15 posts
				array(2 => array(1, 2)), // user remains in groups 1 and 2
				array(2 => 2), // default
				array('action' => 'sync'),
			),
			array(
				array(2 => 150), // user 2 has 150 posts
				array(2 => array(1, 3)), // user removed from group 2, added to group 3
				array(2 => 3), // default
				array('action' => 'sync'),
			),
			array(
				array(2 => 1500), // user 2 has 1500 posts
				array(2 => array(1, 4)), // user removed from group 2, added to group 4
				array(2 => 1), // default
				array('action' => 'sync'),
			),
			array(
				array(3 => 15), // user 3 has 15 posts
				array(3 => array(2, 5)), // user added to group 2
				array(3 => 5), // default remains on group 5
				array('action' => 'sync'),
			),
			array(
				array(
					1 => 15, // user 1 has 15 posts
					2 => 15, // user 2 has 15 posts
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1, 2), // user 2 remains in groups 1 and 2
				),
				array(
					1 => 2, // default
					2 => 2, // default
				),
				array('action' => 'sync'),
			),
			array(
				array(
					1 => 1000, // user 1 has 1000 posts
					2 => 1000, // user 2 has 1000 posts
				),
				array(
					1 => array(1, 4, 5), // user 1 added to group 4
					2 => array(1, 4), // user 2 removed from group 2, added to group 4
				),
				array(
					1 => 1, // default
					2 => 1, // default
				),
				array('action' => 'sync'),
			),
			array(
				array(0 => 0),
				array(),
				array(),
				array('action' => 'sync'),
			),
			array(
				array(),
				array(),
				array(),
				array(),
			),
		);
	}

	/**
	 * Mock settings in the database:
	 * Post count between 10 - 20 adds to group 2 as default
	 * Post count between 100 - 200 adds to group 3 as default
	 * Post count between 500 - unlimited adds to group 4 (no default)
	 *
	 * User 1 is already a member of groups 1 and 5 (1 is default)
	 * User 2 is already a member of groups 1 and 2 (2 is default)
	 *
	 * @return array Array of test data
	 */
	public function check_no_options_test_data()
	{
		return array(
			array(1, 10, array(1, 5)),
			array(2, 1500, array(1, 2)),
		);
	}

	/**
	 * Test the check method, not passing it user ids.
	 * If the posts class does not receive a user_id array or
	 * a sync action is not performed, it results in no changes.
	 *
	 * @dataProvider check_no_options_test_data
	 */
	public function test_check_no_options($user_id, $post_count, $expected)
	{
		// Update the user post count
		$this->helper_update_user_data($user_id, $post_count);

		// Instantiate the condition
		$condition = $this->get_condition();

		// Check the user and perform auto group
		$check_users = $condition->get_users_for_condition();
		$condition->check($check_users);

		// Get the user's groups
		$result = $this->helper->get_users_groups($user_id);

		sort($result[$user_id]);

		// Assert the user's groups are unchanged
		$this->assertEquals($expected, $result[$user_id]);
	}

	/**
	 * Update the database with new post count values for a user
	 *
	 * @param int $user_id
	 * @param int $data The number of posts a user has
	 */
	public function helper_update_user_data($user_id, $data)
	{
		$sql = 'UPDATE ' . USERS_TABLE . '
			SET user_posts = ' . (int) $data . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
