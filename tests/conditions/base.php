<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\conditions;

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_user.php';

/**
* Base class for conditions type tests
*/
class base extends \phpbb_database_test_case
{
	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array('phpbb/autogroups');
	}

	protected $condition;
	protected $condition_type;
	protected $config;
	protected $db;
	protected $helper;
	protected $phpbb_container;
	protected $user;
	protected $root_path;
	protected $php_ext;

	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/' . $this->condition_type . '.xml');
	}

	public function setUp()
	{
		parent::setUp();

		global $auth, $db, $user, $phpbb_container, $phpbb_dispatcher, $phpbb_log, $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$this->config = new \phpbb\config\config(array());
		$this->user = new \phpbb\user('\phpbb\datetime');

		$db = $this->db;
		$user = new \phpbb_mock_user;
		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new \phpbb\cache\driver\null());
		$phpbb_container->set('notification_manager', new \phpbb_mock_notification_manager());
		$this->phpbb_container = $phpbb_container;

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$notification_manager = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->helper = new \phpbb\autogroups\conditions\type\helper(
			$this->config,
			$this->db,
			$notification_manager,
			$this->root_path,
			$this->php_ext
		);

		$phpbb_container->set('phpbb.autogroups.helper', $this->helper);
	}
}
