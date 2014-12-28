<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\controller;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\autogroups\conditions\manager */
	protected $manager;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string The database table the auto group rules are stored in */
	protected $autogroups_rules_table;

	/** @var string The database table the auto group types are stored in */
	protected $autogroups_types_table;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                       Database object
	* @param \phpbb\log\log                       $log                      The phpBB log system
	* @param \phpbb\autogroups\conditions\manager $manager                  Auto groups condition manager object
	* @param \phpbb\request\request               $request                  Request object
	* @param \phpbb\template\template             $template                 Template object
	* @param \phpbb\user                          $user                     User object
	* @param string                               $autogroups_rules_table   Name of the table used to store auto group rules data
	* @param string                               $autogroups_types_table   Name of the table used to store auto group types data
	* @return \phpbb\autogroups\controller\admin_controller
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, \phpbb\autogroups\conditions\manager $manager, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table)
	{
		$this->db = $db;
		$this->log = $log;
		$this->manager = $manager;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_types_table = $autogroups_types_table;
	}

	/**
	* Display the auto group rules
	*
	* @return null
	* @access public
	*/
	public function display_autogroups()
	{
		// Load all auto groups data from the database
		$sql_array = array(
			'SELECT'	=> 'agr.*, agt.autogroups_type_name, g.group_name',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'agr',
				$this->autogroups_types_table => 'agt',
				GROUPS_TABLE => 'g',
			),
			'WHERE'	=> 'agr.autogroups_type_id = agt.autogroups_type_id
				AND agr.autogroups_group_id = g.group_id',
			'ORDER_BY'	=> 'agt.autogroups_type_name ASC, autogroups_min_value ASC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('autogroups', array(
				'GROUP_NAME'		=> !empty($this->user->lang('G_' . $row['group_name'])) ? $this->user->lang('G_' . $row['group_name']) : $row['group_name'],
				'CONDITION_NAME'	=> $this->manager->get_condition_lang($row['autogroups_type_name']),
				'MIN_VALUE'			=> $row['autogroups_min_value'],
				'MAX_VALUE'			=> $row['autogroups_max_value'],

				'S_DEFAULT'	=> $row['autogroups_default'],
				'S_NOTIFY'	=> $row['autogroups_notify'],

				'U_DELETE'	=> "{$this->u_action}&amp;action=delete&amp;autogroups_id=" . $row['autogroups_id'],
				'U_EDIT'	=> "{$this->u_action}&amp;action=edit&amp;autogroups_id=" . $row['autogroups_id'],
			));
		}
		$this->db->sql_freeresult($result);

		// Set output vars for display in the template
		$this->template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'U_ADD_AUTOGROUP_RULE'	=> "{$this->u_action}&amp;action=add",
		));
	}

	/**
	* Add an auto group rule
	*
	* @return null
	* @access public
	*/
	public function add_autogroup_rule()
	{
		// To-do
	}

	/**
	* Edit an auto group rule
	*
	* @param int $autogroups_id The auto groups identifier to edit
	* @return null
	* @access public
	*/
	public function edit_autogroup_rule($autogroups_id)
	{
		// To-do
	}

	/**
	* Process page data to be added or edited
	*
	* @param object $entity The page entity object
	* @return null
	* @access protected
	*/
	protected function add_edit_autogroup_data($row = '')
	{
		// To-do
	}

	/**
	* Delete an auto group rule
	*
	* @param int $autogroups_id The auto groups identifier to delete
	* @return null
	* @access public
	*/
	public function delete_autogroup_rule($autogroups_id)
	{
		// To-do
	}

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
