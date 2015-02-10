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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto Groups base class
 */
abstract class base implements \phpbb\autogroups\conditions\type\type_interface
{
	/** @var ContainerInterface */
	protected $container;

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
	 * @param ContainerInterface                $container              Service container interface
	 * @param \phpbb\config\config              $config                 Config object
	 * @param \phpbb\db\driver\driver_interface $db                     Database object
	 * @param \phpbb\user                       $user                   User object
	 * @param string                            $autogroups_rules_table Name of the table used to store auto group rules data
	 * @param string                            $autogroups_types_table Name of the table used to store auto group types data
	 * @param string                            $phpbb_root_path        phpBB root path
	 * @param string                            $php_ext                phpEx
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_types_table, $phpbb_root_path, $php_ext)
	{
		$this->container = $container;
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
	public function get_group_rules($type = '')
	{
		$sql_array = array(
			'SELECT'	=> 'agr.*, agt.autogroups_type_name',
			'FROM'		=> array(
				$this->autogroups_rules_table => 'agr',
				$this->autogroups_types_table => 'agt',
			),
			'WHERE'		=> 'agr.autogroups_type_id = agt.autogroups_type_id' .
				(($type) ? " AND agt.autogroups_type_name = '" . $this->db->sql_escape($type) . "'" : ''),
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
	public function prepare_users_for_query($user_ids)
	{
		if (is_array($user_ids))
		{
			// Cast each array value to integer
			$user_ids = array_map('intval', $user_ids);
		}
		else
		{
			// Cast user id to integer and put it inside an array
			$user_ids = array((int) $user_ids);
		}

		return $user_ids;
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

		// Send notification
		$this->send_notifications((bool) $group_rule_data['autogroups_notify'], 'group_added', $user_id_ary, $group_id);

		// Set group as default?
		if ($group_rule_data['autogroups_default'])
		{
			// Make sure user_id_ary is an array
			$user_id_ary = $this->prepare_users_for_query($user_id_ary);

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
		// Return if the user_id_array is empty
		if (!sizeof($user_id_ary))
		{
			return;
		}

		if (!function_exists('group_user_del'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		// Set this variable for readability in the code below
		$group_id = $group_rule_data['autogroups_group_id'];

		// Delete user(s) from the group
		group_user_del($group_id, $user_id_ary);

		// Send notification
		$this->send_notifications((bool) $group_rule_data['autogroups_notify'], 'group_removed', $user_id_ary, $group_id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function check($user_row, $options = array())
	{
		// Get all auto group rule data sets
		$group_rules = $this->get_group_rules();

		// Get an array of users and the groups they belong to
		$user_groups = $this->get_users_groups(array_keys($user_row));

		foreach ($group_rules as $group_rule)
		{
			// Only check group rules set for this condition type
			if ($group_rule['autogroups_type_name'] == $this->get_condition_type())
			{
				// Initialize some arrays
				$add_users_to_group = $remove_users_from_group = array();

				foreach ($user_row as $user_id => $user_data)
				{
					// Check if a user's data is within the min/max range
					if ($this->check_user($user_data[$this->get_condition_field()], $group_rule))
					{
						// Check if a user is already a member of checked group
						if (!in_array($group_rule['autogroups_group_id'], $user_groups[$user_id]))
						{
							// Add user to group
							$add_users_to_group[] = $user_id;
						}
					}
					else if (in_array($group_rule['autogroups_group_id'], $user_groups[$user_id]))
					{
						// Remove user from the group
						$remove_users_from_group[] = $user_id;
					}
				}

				if (sizeof($add_users_to_group))
				{
					// Add users to groups
					$this->add_users_to_group($add_users_to_group, $group_rule);
				}

				if (sizeof($remove_users_from_group))
				{
					// Filter users that should not be removed
					$remove_users_from_group = $this->filter_users($remove_users_from_group, $group_rule, $group_rules);

					// Remove users from groups
					$this->remove_users_from_group($remove_users_from_group, $group_rule);
				}
			}
		}
	}

	/**
	 * Helper function checks if a user's data is within
	 * an auto group rule condition's min/max range.
	 *
	 * @param int   $value      The value of the user's data field to check
	 * @param array $group_rule Data array for an auto group rule
	 * @return bool True if the user meets the condition, false otherwise
	 * @access protected
	 */
	protected function check_user($value, $group_rule)
	{
		return ($value >= $group_rule['autogroups_min_value']) &&
			(empty($group_rule['autogroups_max_value']) || ($value <= $group_rule['autogroups_max_value'])
		);
	}

	/**
	 * Helper function prevents un-wanted removal of users from
	 * the current group in cases where users do not satisfy the
	 * conditions of the current rule, but may satisfy conditions
	 * for another rule that applies to the current group.
	 *
	 * @param array $user_id_ary  Array of users marked for removal
	 * @param array $current_rule Data array for an auto group rule
	 * @param array $group_rules  Data array for all auto group rules
	 * @return array Array of users to be removed
	 * @access protected
	 */
	protected function filter_users($user_id_ary, $current_rule, $group_rules)
	{
		// Iterate through every auto group rule
		foreach ($group_rules as $group_rule)
		{
			// Only look at other auto group rules that apply to this group
			if ($group_rule['autogroups_group_id'] == $current_rule['autogroups_group_id'] &&
				$group_rule['autogroups_type_id'] != $current_rule['autogroups_type_id'] &&
				sizeof($user_id_ary)
			)
			{
				// Load other auto group rule's condition type and get new data for our user(s)
				$condition = $this->container->get($group_rule['autogroups_type_name']);
				$condition_user_data = $condition->get_users_for_condition(array(
					'users' => $user_id_ary,
				));
				// Filter users out users that satisfy other conditions for this group
				$user_id_ary = array_filter($user_id_ary, function ($user_id) use ($condition, $condition_user_data, $group_rule) {
					return !$condition->check_user($condition_user_data[$user_id][$condition->get_condition_field()], $group_rule);
				});
			}
		}

		return $user_id_ary;
	}

	/**
	 * Send out notifications
	 *
	 * @param bool $notify       Should a notification be sent
	 * @param string $type       Type of notification to send (group_added|group_removed)
	 * @param array $user_id_ary Array of user(s) to notify
	 * @param int $group_id      The usergroup identifier
	 */
	protected function send_notifications($notify, $type, $user_id_ary, $group_id)
	{
		if ($notify)
		{
			$phpbb_notifications = $this->container->get('notification_manager');
			$phpbb_notifications->add_notifications("phpbb.autogroups.notification.type.$type", array(
				'user_ids'		=> $user_id_ary,
				'group_id'		=> $group_id,
				'group_name'	=> get_group_name($group_id),
			));
		}
	}
}
