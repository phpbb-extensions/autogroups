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
	* {@inheritdoc}
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
	* {@inheritdoc}
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
	* {@inheritdoc}
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
			WHERE ' . $this->db->sql_in_set('group_id', array_map('intval', $group_id_ary));
		$result = $this->db->sql_query($sql, 7200);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_id_ary[] = $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return array_unique($user_id_ary);
	}

	/**
	* {@inheritdoc}
	*/
	public function add_users_to_group($user_id_ary, $group_rule_data)
	{
		if (!function_exists('group_user_add'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Set this variable for readability in the code below
		$group_id = $group_rule_data['autogroups_group_id'];

		// Add user(s) to the group
		group_user_add($group_id, $user_id_ary);

		// Set group as default?
		if (!empty($group_rule_data['autogroups_default']))
		{
			// Make sure user_id_ary is an array
			if (!is_array($user_id_ary))
			{
				$user_id_ary = array((int) $user_id_ary);
			}

			// Get array of users exempt from default group switching
			$default_exempt_users = $this->get_default_exempt_users();

			// Remove any exempt users from our main user array
			if (sizeof($default_exempt_users))
			{
				$user_id_ary = array_diff($user_id_ary, $default_exempt_users);
			}

			// Set the current group as default for non-exempt users
			group_set_user_default($group_id, $user_id_ary);
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function remove_users_from_group($user_id_ary, $group_rule_data)
	{
		if (!function_exists('group_user_del'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Set this variable for readability in the code below
		$group_id = $group_rule_data['autogroups_group_id'];

		// Delete user(s) from the group
		group_user_del($group_id, $user_id_ary);
	}

	/**
	* {@inheritdoc}
	*/
	public function check($user_row, $options = array())
	{
		// Get auto group rule data sets for this type
		$group_rules = $this->get_group_rules($this->get_condition_type());

		// Get the groups the users belongs to
		$user_groups = $this->get_users_groups(array_keys($user_row));

		foreach ($group_rules as $group_rule)
		{
			// Initialize some arrays
			$add_users_to_group = $remove_users_from_group = array();

			foreach ($user_row as $user_id => $user_data)
			{
				// Check if a user's post count is within the min/max range
				if (($user_data[$this->get_condition_field()] >= $group_rule['autogroups_min_value']) && (empty($group_rule['autogroups_max_value']) || ($user_data[$this->get_condition_field()] <= $group_rule['autogroups_max_value'])))
				{
					// Check if a user is a member of checked group
					if (!in_array($group_rule['autogroups_group_id'], $user_groups[$user_id]))
					{
						// Add user to group
						$add_users_to_group[] = $user_id;
					}
				}
				else
				{
					// Check if a user is a member of checked group
					if (in_array($group_rule['autogroups_group_id'], $user_groups[$user_id]))
					{
						// Remove user from the group
						$remove_users_from_group[] = $user_id;
					}
				}
			}

			// Add users to groups
			if (sizeof($add_users_to_group))
			{
				$this->add_users_to_group($add_users_to_group, $group_rule);
			}

			// Remove users from groups
			if (sizeof($remove_users_from_group))
			{
				$this->remove_users_from_group($remove_users_from_group, $group_rule);
			}
		}
	}
}
