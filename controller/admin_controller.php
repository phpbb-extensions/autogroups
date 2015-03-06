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
	/** @var \phpbb\config\config */
	protected $config;

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
	* @param \phpbb\config\config                 $config                   Config object
	* @param \phpbb\db\driver\driver_interface    $db                       Database object
	* @param \phpbb\log\log                       $log                      The phpBB log system
	* @param \phpbb\autogroups\conditions\manager $manager                  Auto groups condition manager object
	* @param \phpbb\request\request               $request                  Request object
	* @param \phpbb\template\template             $template                 Template object
	* @param \phpbb\user                          $user                     User object
	* @param string                               $autogroups_rules_table   Name of the table used to store auto group rules data
	* @param string                               $autogroups_types_table   Name of the table used to store auto group types data
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\log\log $log, \phpbb\autogroups\conditions\manager $manager, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table)
	{
		$this->config = $config;
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
	* {@inheritdoc}
	*/
	public function display_autogroups()
	{
		$autogroup_rows = $this->get_all_autogroups();

		foreach ($autogroup_rows as $row)
		{
			$this->template->assign_block_vars('autogroups', array(
				'GROUP_NAME'		=> $row['group_name'],
				'CONDITION_NAME'	=> $this->manager->get_condition_lang($row['autogroups_type_name']),
				'MIN_VALUE'			=> $row['autogroups_min_value'],
				'MAX_VALUE'			=> $row['autogroups_max_value'],

				'S_DEFAULT'	=> $row['autogroups_default'],
				'S_NOTIFY'	=> $row['autogroups_notify'],

				'U_EDIT'	=> "{$this->u_action}&amp;action=edit&amp;autogroups_id=" . $row['autogroups_id'],
				'U_DELETE'	=> "{$this->u_action}&amp;action=delete&amp;autogroups_id=" . $row['autogroups_id'],
				'U_SYNC'	=> "{$this->u_action}&amp;action=sync&amp;autogroups_id=" . $row['autogroups_id'] . '&amp;hash=' . generate_link_hash('sync' . $row['autogroups_id']),
			));
		}

		$this->template->assign_vars(array(
			'U_ACTION'				=> $this->u_action,
			'U_ADD_AUTOGROUP_RULE'	=> "{$this->u_action}&amp;action=add",
		));

		$this->display_group_exempt_options();
	}

	/**
	* {@inheritdoc}
	*/
	public function save_autogroup_rule($autogroups_id = 0)
	{
		if ($this->request->is_set_post('submit'))
		{
			$this->submit_autogroup_rule($autogroups_id);
		}

		// Get data for the auto group so we can display it
		$autogroups_data = $this->get_autogroup($autogroups_id);

		$this->build_groups_menu(array($autogroups_data['autogroups_group_id']), true);
		$this->build_conditions_menu($autogroups_data['autogroups_type_id']);
		$this->template->assign_vars(array(
			'S_ADD'			=> (bool) !$autogroups_id,
			'S_EDIT'		=> (bool) $autogroups_id,

			'MIN_VALUE'		=> (int) $autogroups_data['autogroups_min_value'],
			'MAX_VALUE'		=> (int) $autogroups_data['autogroups_max_value'],

			'S_DEFAULT'		=> (bool) $autogroups_data['autogroups_default'],
			'S_NOTIFY'		=> (bool) $autogroups_data['autogroups_notify'],

			'U_FORM_ACTION'	=> $this->u_action . '&amp;action=' . (($autogroups_id) ? 'edit' : 'add') . '&amp;autogroups_id=' . $autogroups_id,
			'U_ACTION'		=> $this->u_action,
			'U_BACK'		=> $this->u_action,
		));
	}

	/**
	* {@inheritdoc}
	*/
	public function delete_autogroup_rule($autogroups_id)
	{
		$sql = 'DELETE FROM ' . $this->autogroups_rules_table . '
			WHERE autogroups_id = ' . (int) $autogroups_id;
		$this->db->sql_query($sql);

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_AUTOGROUPS_DELETE_LOG', time());

		// If AJAX was used, show user a result message
		if ($this->request->is_ajax())
		{
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				'MESSAGE_TITLE'	=> $this->user->lang['INFORMATION'],
				'MESSAGE_TEXT'	=> $this->user->lang('ACP_AUTOGROUPS_DELETE_SUCCESS'),
				'REFRESH_DATA'	=> array(
					'time'	=> 3
				)
			));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function resync_autogroup_rule($autogroups_id)
	{
		// If the link hash is invalid, stop and show an error message to the user
		if (!check_link_hash($this->request->variable('hash', ''), 'sync' . $autogroups_id))
		{
			trigger_error($this->user->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		try
		{
			$this->manager->sync_autogroups($autogroups_id);
		}
		catch (\Exception $e)
		{
			trigger_error($e->getMessage() . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function submit_autogroups_settings()
	{
		// Get data from the form
		$autogroups_default_exempt = $this->request->variable('group_ids', array(0));

		// Use a confirmation box routine before setting the data
		if (confirm_box(true))
		{
			$this->config->set('autogroups_default_exempt', serialize($autogroups_default_exempt));
		}
		else
		{
			confirm_box(false, $this->user->lang('CONFIRM_OPERATION'), build_hidden_fields(array(
				'generalsubmit' => true,
				'group_ids' => $autogroups_default_exempt,
			)));
		}
	}

	/**
	* Submit auto group rule form data
	*
	* @param int $autogroups_id An auto group identifier
	*                           A value of 0 is new, otherwise we're updating
	* @return null
	* @access protected
	*/
	protected function submit_autogroup_rule($autogroups_id = 0)
	{
		$data = array(
			'autogroups_type_id'	=> $this->request->variable('autogroups_type_id', 0),
			'autogroups_min_value'	=> $this->request->variable('autogroups_min_value', 0),
			'autogroups_max_value'	=> $this->request->variable('autogroups_max_value', 0),
			'autogroups_group_id'	=> $this->request->variable('autogroups_group_id', 0),
			'autogroups_default'	=> $this->request->variable('autogroups_default', false),
			'autogroups_notify'		=> $this->request->variable('autogroups_notify', false),
		);

		// Prevent form submit when no user groups are available or selected
		if (!$data['autogroups_group_id'])
		{
			trigger_error($this->user->lang('ACP_AUTOGROUPS_INVALID_GROUPS') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($autogroups_id != 0)
		{
			$sql = 'UPDATE ' . $this->autogroups_rules_table . '
				SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE autogroups_id = ' . (int) $autogroups_id;
			$this->db->sql_query($sql);
		}
		else
		{
			$sql = 'INSERT INTO ' . $this->autogroups_rules_table . ' ' . $this->db->sql_build_array('INSERT', $data);
			$this->db->sql_query($sql);
			$autogroups_id = $this->db->sql_nextid();
		}

		// Apply the auto group to all users
		$this->manager->sync_autogroups($autogroups_id);

		// Log the action
		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'ACP_AUTOGROUPS_SAVED_LOG', time());

		// Output message to user after submitting the form
		trigger_error($this->user->lang('ACP_AUTOGROUPS_SUBMIT_SUCCESS') . adm_back_link($this->u_action));
	}

	/**
	* Get one auto group rule from the database
	*
	* @param int $id An auto group rule identifier
	* @return array An auto group rule and it's associated data
	* @access public
	*/
	protected function get_autogroup($id)
	{
		$sql = 'SELECT *
			FROM ' . $this->autogroups_rules_table . '
			WHERE autogroups_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$autogroups_data = $this->db->sql_fetchrow();
		$this->db->sql_freeresult($result);

		return $autogroups_data;
	}

	/**
	* Get all auto group rules from the database
	*
	* @return array Array of auto group rules and their associated data
	* @access public
	*/
	protected function get_all_autogroups()
	{
		$sql_array = array(
			'SELECT'	=> 'agr.*, agt.autogroups_type_name, g.group_name',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'agr',
				$this->autogroups_types_table => 'agt',
				GROUPS_TABLE => 'g',
			),
			'WHERE'	=> 'agr.autogroups_type_id = agt.autogroups_type_id
				AND agr.autogroups_group_id = g.group_id',
			'ORDER_BY'	=> 'g.group_name ASC, autogroups_min_value ASC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Display multi-select box containing all user groups
	 *
	 * @return null
	 * @access protected
	 */
	protected function display_group_exempt_options()
	{
		// Get default exempt groups from db or an empty array
		$group_id_ary = (!$this->config['autogroups_default_exempt']) ? array() : unserialize(trim($this->config['autogroups_default_exempt']));

		$this->build_groups_menu($group_id_ary);
	}

	/**
	* Build template vars for a select menu of user groups
	*
	* @param array $selected An array of identifiers for selected group(s)
	* @param bool $exclude_predefined_groups Exclude GROUP_SPECIAL
	* @return null
	* @access protected
	*/
	protected function build_groups_menu($selected, $exclude_predefined_groups = false)
	{
		// Get groups excluding BOTS, Guests, and optionally predefined
		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE . '
			WHERE ' . $this->db->sql_in_set('group_name', array('BOTS', 'GUESTS'), true, true) .
				(($exclude_predefined_groups) ? ' AND group_type <> ' . GROUP_SPECIAL : '') . '
			ORDER BY group_name';
		$result = $this->db->sql_query($sql);

		while ($group_row = $this->db->sql_fetchrow())
		{
			$this->template->assign_block_vars('groups', array(
				'GROUP_ID'		=> $group_row['group_id'],
				'GROUP_NAME'	=> ($group_row['group_type'] == GROUP_SPECIAL) ? $this->user->lang('G_' . $group_row['group_name']) : $group_row['group_name'],

				'S_SELECTED'	=> in_array($group_row['group_id'], $selected),
			));
		}
		$this->db->sql_freeresult($result);
	}

	/**
	* Build template vars for a select menu of auto group conditions
	*
	* @param int $selected An identifier for the selected group
	* @return null
	* @access protected
	*/
	protected function build_conditions_menu($selected)
	{
		$conditions = $this->manager->get_autogroup_type_ids();

		foreach ($conditions as $condition_name => $condition_id)
		{
			$this->template->assign_block_vars('conditions', array(
				'CONDITION_ID'		=> $condition_id,
				'CONDITION_NAME'	=> $this->manager->get_condition_lang($condition_name),

				'S_SELECTED'		=> $condition_id == $selected,
			));
		}
	}

	/**
	* {@inheritdoc}
	*/
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
