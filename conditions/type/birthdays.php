<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\conditions\type;

/**
* Auto Groups Birthdays class
*/
class birthdays extends \phpbb\autogroups\conditions\type\base
{
	/**
	* Get condition type
	*
	* @return string Condition type
	* @access public
	*/
	public function get_condition_type()
	{
		return 'phpbb.autogroups.type.birthdays';
	}

	/**
	* Get condition field (this is the field to check)
	*
	* @return string Condition field name
	* @access public
	*/
	public function get_condition_field()
	{
		return 'user_birthday';
	}

	/**
	* Get condition type name
	*
	* @return string Condition type name
	* @access public
	*/
	public function get_condition_type_name()
	{
		return $this->user->lang('AUTOGROUPS_TYPE_BIRTHDAYS');
	}

	/**
	 * Get users to apply to this condition
	 *
	 * @param array $options Array of optional data
	 * @return array Array of users ids as keys and their condition data as values
	 * @access public
	 */
	public function get_users_for_condition($options = array())
	{
		// The user data this condition needs to check
		$condition_data = array(
			$this->get_condition_field(),
		);

		// Merge default options, use the active user as the default
		$options = array_merge(array(
			'users'		=> '',
		), $options);

		$user_ids = $options['users'];

		// Clean up array of ids
		if (is_array($user_ids))
		{
			$user_ids = array_map('intval', $user_ids);
		}
		else
		{
			$user_ids = array((int) $user_ids);
		}

		$now = phpbb_gmgetdate(); // Get the current UTC timestamp

		// Get data for the users to be checked (exclude bots and guests)
		$sql = 'SELECT user_id, ' . implode(', ', $condition_data) . '
			FROM ' . USERS_TABLE . '
			WHERE user_type <> ' . USER_IGNORE . ((!empty($options['users'])) ? ' AND ' .  $this->db->sql_in_set('user_id', $user_ids) : '');
		$result = $this->db->sql_query($sql);

		$user_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			// Convert stored birth date into the users current age
			$birthday_year = (int) substr($row['user_birthday'], -4);
			$birthday_age = ($birthday_year) ? max(0, $now['year'] - $birthday_year) : 0;
			$user_data[$row['user_id']] = array($this->get_condition_field() => $birthday_age);
		}
		$this->db->sql_freeresult($result);

		return $user_data;
	}
}
