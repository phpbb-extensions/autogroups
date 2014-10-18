<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\conditions;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Auto Groups service class
*/
class manager
{
	/** @var array */
	protected $autogroups_conditions;

	/** @var ContainerInterface */
	protected $phpbb_container;

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
	* @param array                                $autogroups_conditions              Array with auto groups conditions
	* @param ContainerInterface                   $phpbb_container                    Service container interface
	* @param \phpbb\db\driver\driver_interface    $db                                 Database object
	* @param \phpbb\user                          $user                               User object
	* @param string                               $autogroups_rules_table             Name of the table used to store auto group rules data
	* @param string                               $autogroups_condition_types_table   Name of the table used to store auto group conditions data
	*
	* @return \phpbb\autogroups\conditions\manager
	* @access public
	*/
	public function __construct($autogroups_conditions, ContainerInterface $phpbb_container, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_condition_types_table)
	{
		$this->autogroups_conditions = $autogroups_conditions;
		$this->phpbb_container = $phpbb_container;
		$this->db = $db;
		$this->user = $user;
		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_condition_types_table = $autogroups_condition_types_table;
	}

	/**
	* Check auto groups conditions and execute them
	*
	* @return
	* @access public
	*/
	public function check_conditions()
	{
		foreach ($this->autogroups_conditions as $autogroups_condition)
		{
			$this->check_condition($autogroups_condition);
		}
	}

	/**
	* Check auto groups condition and execute it
	*
	* @param string     $condtion_name      Name of the condition
	*
	* @return
	* @access public
	*/
	public function check_condition($condition_name)
	{
		$condition = $this->phpbb_container->get($condition_name);

		$condition->check();
	}

	/**
	* Add new condition for Auto Groups extension
	*
	* @param string     $condition_type_name      Type identifier of the condition
	*
	* @return
	* @access public
	*/
	public function add_condition($condition_type_name)
	{
		$sql = 'INSERT INTO ' . $this->autogroups_condition_types_table . ' condition_type_name = ' . $condition_type_name;
		$this->db->sql_query($sql);
	}

	/**
	* Purge all conditions of a certain type
	*
	* @param string     $condition_type_name      Type identifier of the condition
	*
	* @return
	* @access public
	*/
	public function purge_condition($condition_type_name)
	{
		try
		{
			$condtion_type_id = $this->get_condition_type_id($condition_type_name);

			$sql = 'DELETE FROM ' . $this->autogroups_rules_table . '
				WHERE condition_type_id = ' . (int) $condtion_type_id;
			$this->db->sql_query($sql);

			$sql = 'DELETE FROM ' . $this->autogroups_condition_types_table . '
				WHERE condition_type_id = ' . (int) $condtion_type_id;
			$this->db->sql_query($sql);
		}
		catch (\phpbb\autogroups\exception\base $e)
		{
			// Continue
		}
	}

	/**
	* Get the condition type id from the name
	*
	* @param string     $condition_type_name      Type identifier of the condition
	*
	* @return int The condition_type_id
	* @throws \phpbb\autogroups\exception
	*/
	public function get_condition_type_id($condition_type_name)
	{
		$condition_type_ids = array();

		$sql = 'SELECT condition_type_id, condition_type_name
			FROM ' . $this->autogroups_condition_types_table;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$condition_type_ids[$row['condition_type_name']] = (int) $row['condition_type_id'];
		}
		$this->db->sql_freeresult($result);

		if (!isset($condition_type_ids[$condition_type_name]))
		{
			if (!isset($this->autogroups_conditions[$condition_type_name]))
			{
				throw new \phpbb\autogroups\exception\base(array($condition_type_name, $this->user->lang('CONDITION_TYPE_NOT_EXIST')));
			}
		}

		return $condition_type_ids[$condition_type_name];
	}
}
