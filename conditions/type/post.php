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
	* Check condition
	*
	* @return null
	* @access public
	*/
	public function check()
	{
		$group_rules = $this->get_group_rules($this->get_condition_type());
		$user_groups = $this->user_groups();

		$add_user_to_groups = $remove_user_from_groups = array();

		foreach ($group_rules as $group_rule)
		{
			// Check if a user post value is the settled range
			if (($this->user->data['user_post'] >= $group_rule['autogroups_min_value'] && (empty($group_rule['autogroups_min_value']) || ($this->user->data['user_post'] <= $group_rule['autogroups_min_value'])))
			{
				// Check if a user is a member of checked group
				if (!in_array($group_rule['autogroups_group_id'], $user_groups)
				{
					// Add user to group (create array where a group id is a key and default is value)
					$add_user_to_groups[$group_rule['autogroups_group_id']] = $group_rule['autogroups_default'];
				}
			}
			// If the user post value doesn't match to the above range, add that group id to array as value
			else
			{
				$remove_user_from_groups[] = $group_rule['autogroups_group_id'];
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
