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
class post
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
	*
	*
	* @return
	* @access public
	*/
	public function get_type()
	{
		return 'phpbb.autogroups.condition.type.post';
	}

	/**
	*
	*
	* @return
	* @access public
	*/
	public function get_group_rules()
	{
		$sql_array = array(
			'SELECT'	=> 'a.*',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'a',
				$this->autogroups_condition_types_table => 'c',
			),
			'WHERE'	=> 'a.condition_type_id = c.condition_type_id
				AND c.condition_type_name = ' . $this->get_type(),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		return $result;
	}

	/**
	*
	*
	* @return
	* @access public
	*/
	public function check()
	{
		$group_rules = $this->get_group_rules()

		$add_groups = array();

		foreach($group_rules as $group_rule)
		{
			if ($group_rule[''] == 0 && $group_rule[''] == 0)
			{
				continue;
			}

			else if ((($group_rule[''] > 0 && $this->user->data['user_post'] >= $group_rule['min_']) ||
					($group_rule[''] > 0 && $this->user->data['user_post'] <= $group_rule['max_'])))
			{
				$add_groups[] = $group_rule[''];
			}
		}
	}

	public function add_user_to_groups()
	{
	}

	public function remove_user_from_groups()
	{
	}
}
