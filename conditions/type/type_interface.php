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
* Auto Groups interface
*/
interface type_interface
{
	/**
	* Get condition type
	*
	* @return string Condition type
	* @access public
	*/
	public function get_condition_type();

	/**
	* Get condition type name
	*
	* @return string Condition type name
	* @access public
	*/
	public function get_condition_type_name();

	/**
	* Add user to groups
	*
	* @param array $groups_data Data array where a group id is a key and default is value
	* @return null
	* @access public
	*/
	public function add_user_to_groups($groups_data);

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array where a group id is a key and default is value
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data);
}
