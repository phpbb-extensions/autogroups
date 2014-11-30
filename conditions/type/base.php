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
	/** @var \phpbb\config\config */
	protected $config;

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
	* @param \phpbb\config\config                 $config                   Config object
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
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
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
	* @param array $user_id_ary An array of user ids to check
	* @return array An array of usergroup ids each user belongs to
	* @access public
	*/
	public function get_users_groups($user_id_ary)
	{
		$group_id_ary = array();

		$sql = 'SELECT user_id, group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_id_ary, false, true);
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_id_ary[$row['user_id']][] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $group_id_ary;
	}

	/**
	* Get users that should not have their default status changed
	*
	* @return array An array of user ids
	* @access public
	*/
	public function get_default_exempt_users()
	{
		$user_id_ary = array();

		// Get default exempt groups from db or an empty array
		$group_id_ary = (!$this->config['autogroups_default_exempt']) ? array() : unserialize(trim($this->config['autogroups_default_exempt']));

		if (!sizeof($group_id_ary))
		{
			return $user_id_ary;
		}

		$sql = 'SELECT user_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE ' . $this->db->sql_in_set('group_id', $group_id_ary);
		$result = $this->db->sql_query($sql, 7200);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id_ary[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return array_unique($user_id_ary);
	}

	/**
	* Add user(s) to group
	*
	* @param array $user_id_ary User(s) to add to group
	* @param array $group_rule_data Auto group rule data
	* @return null
	* @access public
	*/
	public function add_users_to_group($user_id_ary, $group_rule_data)
	{
		if (!function_exists('group_user_add'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Make sure user_id_ary is an array
		if (!is_array($user_id_ary))
		{
			$user_id_ary = array($user_id_ary);
		}

		// Add user(s) to the group
		group_user_add($group_rule_data['autogroups_group_id'], $user_id_ary);

		// Set group as default?
		if ($group_rule_data['autogroups_default'])
		{
			// Get array of users exempt from default group switching (run once)
			if (!isset($default_exempt_users))
			{
				$default_exempt_users = $this->get_default_exempt_users();
			}

			// Remove any exempt users from our main user array
			if (sizeof($default_exempt_users))
			{
				$user_id_ary = array_diff($user_id_ary, $default_exempt_users);
			}

			// Set the current group as default for non-exempt users
			group_set_user_default($group_rule_data['autogroups_group_id'], $user_id_ary);
		}
	}

	/**
	* Remove user(s) from group
	*
	* @param array $user_id_ary User(s) to remove from group
	* @param array $group_rule_data Auto group rule data
	* @return null
	* @access public
	*/
	public function remove_users_from_group($user_id_ary, $group_rule_data)
	{
		if (!function_exists('group_user_del'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Make sure user_id_ary is an array
		if (!is_array($user_id_ary))
		{
			$user_id_ary = array($user_id_ary);
		}

		// Delete user(s) from the group
		group_user_del($group_rule_data['autogroups_group_id'], $user_id_ary);
	}
}
