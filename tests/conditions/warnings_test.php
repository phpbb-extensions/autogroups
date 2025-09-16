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
 * Run tests on the warnings type class.
 */
class warnings_test extends type_test_case
{
	protected $condition_type = 'phpbb.autogroups.type.warnings';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\warnings(
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
	 * Warnings between 0 - 1 adds to group 4 (no default)
	 * Warnings between 2 - 3 adds to group 2 as default
	 * Warnings between 4 - 5 adds to group 3 (no default)
	 * Warnings between 6 - 0 (forever) adds to group 6 (no default)
	 *
	 * User 1 is already a member of groups 1 and 5 (1 is default)
	 * User 2 is already a member of groups 1 and 2 (2 is default)
	 * User 3 is already a member of group 5 (5 is default)
	 *
	 * @return array Array of test data
	 */
	public static function check_condition_test_data()
	{
		return array(
			array(
				array(1 => 1), // user 1 has 1 warning
				array(1 => array(1, 4, 5)), // user 1 added to group 4
				array(1 => 1), // default
				array(),
			),
			array(
				array(2 => 2), // user 2 has 2 warnings
				array(2 => array(1, 2)), // user 2 added to no new groups
				array(2 => 2), // default
				array(),
			),
			array(
				array(3 => 2), // user 3 has 2 warnings
				array(3 => array(2, 5)), // user 3 added to group 2
				array(3 => 5), // default remains on group 5
				array(),
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
				array(),
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
	 * @param int $data The number of warnings
	 */
	public function helper_update_user_data($user_id, $data)
	{
		$sql = 'UPDATE phpbb_users
			SET user_warnings = ' . (int) $data . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
