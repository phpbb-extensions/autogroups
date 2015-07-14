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
	protected $db;
	protected $helper;
	protected $notification_manager;
	protected $phpbb_container;
	protected $user;
	protected $root_path;
	protected $php_ext;

	public function getDataSet()
	{
		// Aggregate multiple fixtures into a single dataset
		$ds1 = $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/' . $this->condition_type . '.xml');
		$ds2 = $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/users.xml');

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

		$lang_loader = new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx);
		$lang = new \phpbb\language\language($lang_loader);
		$this->user = new \phpbb\user($lang, '\phpbb\datetime');

		$user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'
		));

		$db = $this->db;
		$auth = $this->getMock('\phpbb\auth\auth');

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('cache.driver', new \phpbb\cache\driver\dummy());
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
