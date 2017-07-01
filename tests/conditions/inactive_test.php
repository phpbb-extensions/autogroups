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
class inactive_test extends type_test_case
{
	protected $condition_type = 'phpbb.autogroups.type.inactive';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\inactive(
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
	 * Inactive days between 0 - 10 adds to group 2
	 * Inactive days between 20 - 50 adds to group 3
	 * Inactive days between 100 - 0 (forever) adds to group 6
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
				array(1 => 5), // user 1 inactive 5 days
				array(1 => array(1, 2, 5)), // user 1 added to group 2
				array(1 => 2), // default
				array(),
			),
			array(
				array(1 => 15), // user 1 inactive 15 days
				array(1 => array(1, 5)), // user 1 added to no new groups
				array(1 => 1), // default
				array(),
			),
			array(
				array(1 => 25), // user 1 inactive 25 days
				array(1 => array(1, 3, 5)), // user 1 added to group 3
				array(1 => 3), // default
				array(),
			),
			array(
				array(1 => 200), // user 1 inactive 200 days
				array(1 => array(1, 5, 6)), // user 1 added to group 6
				array(1 => 6), // default
				array(),
			),
			array(
				array(
					1 => 15, // user 1 inactive 15 days
					2 => 50, // user 2 inactive 50 days
				),
				array(
					1 => array(1, 5), // user 1 added to no new groups
					2 => array(1, 3), // user 2 removed from group 2, added to group 3
				),
				array(
					1 => 1, // default
					2 => 3, // default
				),
				array(),
			),
			array(
				array(
					1 => 5, // user 1 inactive 5 days
					2 => 15, // user 2 inactive 15 days
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1), // user 2 removed from group 2
				),
				array(
					1 => 2, // default
					2 => 1, // default
				),
				array(),
			),
			array(
				array(
					1 => 0, // user 1 inactive 0 days
					2 => 501, // user 2 inactive 501 days
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1, 6), // user 2 removed from group 2 and added to group 6
				),
				array(
					1 => 2, // default
					2 => 6, // default
				),
				array(),
			),
			array(
				array(
					1 => false, // user 1 not inactive (false results in a user_inactive_time of 0)
					3 => 0,  // user 3 inactive 0 days
				),
				array(
					1 => array(1, 5), // user 1 added to no new groups
					3 => array(2, 5), // user 3 added to group 2
				),
				array(
					1 => 1, // default
					3 => 5, // default remains on group 5 (because its an exempted group)
				),
				array(),
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
	 * Update the database with new inactive timestamp
	 *
	 * @param int $user_id
	 * @param int $data The number of days a user has been inactive
	 */
	public function helper_update_user_data($user_id, $data)
	{
		$sql = 'UPDATE phpbb_users
			SET user_inactive_time = ' . (int) (strtotime("$data days ago")) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
