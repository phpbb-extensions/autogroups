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

require_once __DIR__ . '/../../../../../includes/functions.php';
require_once __DIR__ . '/../../../../../includes/functions_user.php';

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

	/** @var string */
	protected $condition_type;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\autogroups\conditions\type\helper */
	protected $helper;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb_mock_container_builder */
	protected $phpbb_container;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	public function getDataSet()
	{
		// Aggregate multiple fixtures into a single dataset
		$ds1 = $this->createXMLDataSet(__DIR__ . '/fixtures/' . $this->condition_type . '.xml');
		$ds2 = $this->createXMLDataSet(__DIR__ . '/fixtures/users.xml');

		$compositeDs = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet();
		$compositeDs->addDataSet($ds1);
		$compositeDs->addDataSet($ds2);

		return $compositeDs;
	}

	public function setUp()
	{
		parent::setUp();

		global $auth, $db, $user, $phpbb_container, $phpbb_dispatcher, $phpbb_log, $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();
		$db = $this->db;

		$this->user = new \phpbb\user('\phpbb\datetime');
		$user = $this->user;

		/** @var $auth \PHPUnit_Framework_MockObject_MockObject|\phpbb\auth\auth */
		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new \phpbb\cache\driver\null());
		$phpbb_container->set('notification_manager', new \phpbb_mock_notification_manager());
		$this->phpbb_container = $phpbb_container;

		$phpbb_log = new \phpbb\log\log($db, $user, $auth, $phpbb_dispatcher, $phpbb_root_path, 'adm/', $phpEx, LOG_TABLE);

		$this->notification_manager = $this->getMockBuilder('\phpbb\notification\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->root_path = $phpbb_root_path;
		$this->php_ext = $phpEx;

		$this->helper = new \phpbb\autogroups\conditions\type\helper(
			$this->db,
			$this->notification_manager,
			$this->root_path,
			$this->php_ext
		);

		$phpbb_container->set('phpbb.autogroups.helper', $this->helper);
	}
}
