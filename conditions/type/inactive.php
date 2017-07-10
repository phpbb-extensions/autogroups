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
 * Auto Groups Inactive users class
 */
class inactive extends \phpbb\autogroups\conditions\type\membership
{
	/**
	 * Get condition type
	 *
	 * @return string Condition type
	 * @access public
	 */
	public function get_condition_type()
	{
		return 'phpbb.autogroups.type.inactive';
	}

	/**
	 * Get condition field (this is the field to check)
	 *
	 * @return string Condition field name
	 * @access public
	 */
	public function get_condition_field()
	{
		return 'user_inactive_time';
	}

	/**
	 * Get condition type name
	 *
	 * @return string Condition type name
	 * @access public
	 */
	public function get_condition_type_name()
	{
		return $this->user->lang('AUTOGROUPS_TYPE_INACTIVE');
	}

	/**
	 * An array of user types to ignore when querying users
	 *
	 * @return array Array of user types
	 * @access protected
	 */
	protected function ignore_user_types()
	{
		return array(USER_IGNORE);
	}
}
