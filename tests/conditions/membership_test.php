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
class membership_test extends type_test_case
{
	protected $condition_type = 'phpbb.autogroups.type.membership';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\membership(
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
	 * Membership days between 0 - 3 adds to group 4 (no default)
	 * Membership days between 10 - 50 adds to group 2 as default
	 * Membership days between 50 - 100 adds to group 3 (no default)
	 * Membership days between 500 - 0 (forever) adds to group 6 (no default)
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
				array(1 => 15), // user 1 has 15 days
				array(1 => array(1, 2, 5)), // user 1 added to group 2
				array(1 => 2), // default
				array(),
			),
			array(
				array(1 => 150), // user 1 has 150 days
				array(1 => array(1, 5)), // user 1 added to no new groups
				array(1 => 1), // default
				array(),
			),
			array(
				array(3 => 20), // user 3 has 20 days
				array(3 => array(2, 5)), // user 3 added to group 2
				array(3 => 5), // default remains on group 5
				array(),
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
				array(),
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
				array(),
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
	 * Update the database with new registration timestamp user
	 *
	 * @param int $user_id
	 * @param int $data The number of days a user has been registered
	 */
	public function helper_update_user_data($user_id, $data)
	{
		$sql = 'UPDATE phpbb_users
			SET user_regdate = ' . (int) (strtotime("$data days ago")) . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
