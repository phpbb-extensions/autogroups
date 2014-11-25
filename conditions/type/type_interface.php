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
	* Get users to apply to this condition
	*
	* @param array $options Array of optional data
	* @return array Array of users ids and their post counts
	* @access public
	*/
	public function get_users_for_condition($options = array());

	/**
	* Get auto group rules for condition type
	*
	* @param string $type Auto group condition type name
	* @return array Auto group rows
	* @access public
	*/
	public function get_group_rules($type);

	/**
	* Get user's group ids
	*
	* @param array $user_id_ary An array of user ids to check
	* @return array An array of usergroup ids each user belongs to
	* @access public
	*/
	public function get_users_groups($user_id_ary);

	/**
	* Add user to groups
	*
	* @param array $groups_data Data array where group id is key and user array is value
	* @param array $default Data array where group id is key and value is a boolean if
	*                       the group should be set as the default group for users
	* @return null
	* @access public
	*/
	public function add_user_to_groups($groups_data, $default = array());

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array where a group id is a key and user array is value
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data);
}
