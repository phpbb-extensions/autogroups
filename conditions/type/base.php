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

	/** @var string The database table the auto group rules are stored in */
	protected $autogroups_rules_table;

	/** @var string The database table the auto group types are stored in */
	protected $autogroups_types_table;

	/** @var string */
	protected $phpbb_root_path;

	/** @var string */
	protected $php_ext;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                       Database object
	* @param \phpbb\user                          $user                     User object
	* @param string                               $autogroups_rules_table   Name of the table used to store auto group rules data
	* @param string                               $autogroups_types_table   Name of the table used to store auto group types data
	* @param string                               $phpbb_root_path          phpBB root path
	* @param string                               $php_ext                  phpEx
	*
	* @return \phpbb\autogroups\conditions\type\base
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->user = $user;

		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_types_table = $autogroups_types_table;

		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Get auto group rules for condition type
	*
	* @param string $type Auto group condition type name
	* @return array Auto group rows
	* @access public
	*/
	public function get_group_rules($type)
	{
		$sql_array = array(
			'SELECT'	=> 'agr.*',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'agr',
				$this->autogroups_types_table => 'agt',
			),
			'WHERE'	=> "agr.autogroups_type_id = agt.autogroups_type_id
				AND agt.autogroups_type_name = '" . $this->db->sql_escape($type) . "'",
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql, 7200);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Get user's group ids
	*
	* @param array $user_ids An array of user ids to check
	* @return array An array of usergroup ids each user belongs to
	* @access public
	*/
	public function get_users_groups($user_ids)
	{
		$group_ids = array();

		$sql = 'SELECT user_id, group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids, false, true);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[$row['user_id']][] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $group_ids;
	}

	/**
	* Add user to groups
	*
	* @param array $groups_data Data array where group id is key and user array is value
	* @param array $default Data array where group id is key and value is a boolean if
	*                       the group should be set as the default group for users
	* @return null
	* @access public
	*/
	public function add_user_to_groups($groups_data, $default = array())
	{
		if (!function_exists('group_user_add'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		foreach ($groups_data as $group_id => $users)
		{
			// Use default value if valid, otherwise use false
			$default = (isset($default[$group_id])) ? (bool) $default[$group_id] : false;

			group_user_add($group_id, $users, false, false, $default);
		}
	}

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array where a group id is a key and user array is value
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data)
	{
		if (!function_exists('group_user_del'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		foreach ($groups_data as $group_id => $users)
		{
			group_user_del($group_id, $users);
		}
	}
}
