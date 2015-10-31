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
 * Run tests on the birthdays type class.
 */
class birthdays_test extends type_test_case
{
	protected $condition_type = 'phpbb.autogroups.type.birthdays';

	public function get_condition()
	{
		return new \phpbb\autogroups\conditions\type\birthdays(
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
	 * Ages under 20 adds to group 2 as default
	 * Ages between 30 - 50 adds to group 3 (no default)
	 * Ages over 40 adds to group 4 (no default)
	 *
	 * User 1 is already a member of groups 1 and 5 (1 is default)
	 * User 2 is already a member of groups 1 and 2 (2 is default)
	 * User 3 is already a member of group 5 (5 is default and exempt)
	 *
	 * @return Array of test data
	 */
	public function check_condition_test_data()
	{
		return array(
			array(
				array(
					1 => 15,
					2 => 45,
				),
				array(
					1 => array(1, 2, 5), // user 1 added to group 2
					2 => array(1, 3, 4), // user 2 added to group 3 and 4, removed from group 2
				),
				array(
					1 => 2, // default
					2 => 1, // default
				),
				array(),
			),
			array(
				array(3 => 15),
				array(3 => array(2, 5)), // user 3 added to group 2
				array(3 => 5), // default remains on group 5
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
	 * @param int $data The age in years of a user
	 */
	public function helper_update_user_data($user_id, $data)
	{
		$now = getdate(time());
		$birthday = sprintf('%2d-%2d-%4d', $now['mday'], $now['mon'], ($now['year'] - $data));

		$sql = "UPDATE phpbb_users
			SET user_birthday = '" . $this->db->sql_escape($birthday) . "'
			WHERE user_id = " . (int) $user_id;
		$this->db->sql_query($sql);
	}
}
