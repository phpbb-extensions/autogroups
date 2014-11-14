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

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                       Database object
	* @param \phpbb\user                          $user                     User object
	* @param string                               $autogroups_rules_table   Name of the table used to store auto group rules data
	* @param string                               $autogroups_types_table   Name of the table used to store auto group types data
	*
	* @return \phpbb\autogroups\conditions\type\base
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table)
	{
		$this->db = $db;
		$this->user = $user;

		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_types_table = $autogroups_types_table;
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
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Get user's group ids
	*
	* @return array An array of usergroup ids the user belongs to
	* @access public
	*/
	public function get_users_groups()
	{
		$group_ids = array();

		$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_ids[] = $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		return $group_ids;
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
			group_user_add($group_id, $this->user->data['user_id'], false, false, $default);
		}
	}

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array with group ids
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data)
	{
		foreach ($groups_data as $group_id)
		{
			group_user_del($group_id, $this->user->data['user_id']);
		}
	}
}
