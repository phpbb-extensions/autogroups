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
* Auto Groups service class
*/
abstract class base implements \phpbb\autogroups\conditions\type\type_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/**
	* The database table the auto group rules are stored in
	*
	* @var string
	*/
	protected $autogroups_rules_table;

	/**
	* The database table the auto group conditions are stored in
	*
	* @var string
	*/
	protected $autogroups_condition_types_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                                 Database object
	* @param \phpbb\user                          $user                               User object
	* @param string                               $autogroups_rules_table             Name of the table used to store auto group rules data
	* @param string                               $autogroups_condition_types_table   Name of the table used to store auto group conditions data
	*
	* @return \phpbb\autogroups\conditions\manager
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_condition_types_table)
	{
		$this->db = $db;
		$this->user = $user;

		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_condition_types_table = $autogroups_condition_types_table;
	}

	/**
	* Add user to groups
	*
	* @param array $groups_data Data array where a group id is a key and default is value
	* @return null
	* @access public
	*/
	public function add_user_to_groups($groups_data)
	{
		foreach ($groups_data as $group_id => $default)
		{
			group_user_add($group_id, $this->user->data['user_id'], false, false, $default)
		}
	}

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array where a group id is a key and default is value
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data)
	{
		foreach ($groups_data as $group_id => $default)
		{
			group_user_del($group_id, $this->user->data['user_id'], false, false, $default)
		}
	}
}
