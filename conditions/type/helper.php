<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\conditions\type;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto Groups conditions type helper class
 */
class helper
{
	/** @var ContainerInterface */
	protected $container;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

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
	 * @param string                            $phpbb_root_path        phpBB root path
	 * @param string                            $php_ext                phpEx
	 *
	 * @access public
	 */
	public function __construct(ContainerInterface $container, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, $phpbb_root_path, $php_ext)
	{
		$this->container = $container;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Get user's group ids
	 *
	 * @param array $user_id_ary An array of user ids to check
	 * @return array An array of usergroup ids each user belongs to
	 * @access public
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
	 * Get users that should not have their default status changed
	 *
	 * @return array An array of user ids
	 * @access public
	 */
	public function get_default_exempt_users()
	{
		$user_id_ary = array();

		// Get default exempt groups from db
		$group_id_ary = unserialize(trim($this->config['autogroups_default_exempt']));

		if (empty($group_id_ary))
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
	 * Prepare user ids for querying
	 *
	 * @param mixed $user_ids User id(s) expected as int or array
	 * @return array An array of user id(s)
	 * @access public
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
	 * Send notifications
	 *
	 * @param string $type       Type of notification to send (group_added|group_removed)
	 * @param array $user_id_ary Array of user(s) to notify
	 * @param int $group_id      The usergroup identifier
	 * @return null
	 * @access public
	 */
	public function send_notifications($type, $user_id_ary, $group_id)
	{
		if (!function_exists('get_group_name'))
		{
			include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
		}

		$phpbb_notifications = $this->container->get('notification_manager');
		$phpbb_notifications->add_notifications("phpbb.autogroups.notification.type.$type", array(
			'user_ids'		=> $user_id_ary,
			'group_id'		=> $group_id,
			'group_name'	=> get_group_name($group_id),
		));
	}
}