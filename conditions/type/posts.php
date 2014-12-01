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
class posts extends \phpbb\autogroups\conditions\type\base
{
	/**
	* Get condition type
	*
	* @return string Condition type
	* @access public
	*/
	public function get_condition_type()
	{
		return 'phpbb.autogroups.type.posts';
	}

	/**
	* Get condition type name
	*
	* @return string Condition type name
	* @access public
	*/
	public function get_condition_type_name()
	{
		return $this->user->lang('AUTOGROUPS_TYPE_POSTS');
	}

	/**
	* Get users to apply to this condition
	*
	* @param array $options Array of optional data
	* @return array Array of users ids as keys and their condition data as values
	* @access public
	*/
	public function get_users_for_condition($options = array())
	{
		// The user data this condition needs to check
		$condition_data = array(
			'user_posts',
		);

		// Merge default options, use the active user as the default
		$options = array_merge(array(
			'users'		=> $this->user->data['user_id'],
		), $options);

		$user_ids = $options['users'];

		// Clean up array of ids
		if (is_array($user_ids))
		{
			$user_ids = array_map('intval', $user_ids);
		}
		else
		{
			$user_ids = array((int) $user_ids);
		}

		// Get data for the users to be checked (exclude bots and guests)
		$sql = 'SELECT user_id, ' . implode(', ', $condition_data) . '
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_id', $user_ids) . '
				AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);

		$user_data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$user_data[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $user_data;
	}

	/**
	* Check condition
	*
	* @param array $user_row Array of user data to perform checks on
	* @param array $options Array of optional data
	* @return null
	* @access public
	*/
	public function check($user_row, $options = array())
	{
		// Merge default options
		$options = array_merge(array(
			'action'	=> '',
		), $options);

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
				// We need to decrement the post count when deleting posts because
				// the database has not yet been updated with new post counts
				if ($options['action'] == 'delete')
				{
					$user_data['user_posts']--;
				}

				// Check if a user's post count is within the min/max range
				if (($user_data['user_posts'] >= $group_rule['autogroups_min_value']) && (empty($group_rule['autogroups_max_value']) || ($user_data['user_posts'] <= $group_rule['autogroups_max_value'])))
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
