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
class post extends \phpbb\autogroups\conditions\type\base
{
	/**
	* Get condition type
	*
	* @return string Condition type
	* @access public
	*/
	public function get_condition_type()
	{
		return 'phpbb.autogroups.condition.type.post';
	}

	/**
	* Get condition type name
	*
	* @return string Condition type name
	* @access public
	*/
	public function get_condition_type_name()
	{
		return $this->user->lang('AUTOGROUPS_CONDITION_TYPE_POST');
	}

	/**
	* Get auto group rules for condition
	*
	* @return array
	* @access public
	*/
	public function get_group_rules()
	{
		$sql_array = array(
			'SELECT'	=> 'ag.*',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'ag',
				$this->autogroups_condition_types_table => 'agc',
			),
			'WHERE'	=> 'ag.condition_type_id = agc.condition_type_id
				AND agc.condition_type_name = ' . $this->get_condition_type(),
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		return $result;
	}

	/**
	* Check condition
	*
	* @return null
	* @access public
	*/
	public function check()
	{
		$group_rules = $this->get_group_rules();

		$add_user_to_groups = $remove_user_from_groups= array();

		foreach($group_rules as $group_rule)
		{
			// @To-Do Define empty fields and if conditions after defining table structure
			if ($group_rule[''] == 0 && $group_rule[''] == 0)
			{
				continue;
			}

			if ()
			{
				$add_user_to_groups[$group_rule['']] = $group_rule[''];
			}

			if ()
			{
				$remove_user_from_groups[$group_rule['']] = $group_rule[''];
			}

			// Add user to groups
			if (sizeof($add_user_to_groups))
			{
				$this->add_user_to_groups($add_user_to_groups);
			}

			// Remove user from groups
			if (sizeof($remove_user_from_groups))
			{
				$this->remove_user_from_groups($remove_user_from_groups);
			}
		}
	}
}
