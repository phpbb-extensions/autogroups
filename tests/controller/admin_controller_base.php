<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\controller;

class admin_controller_base extends \phpbb_database_test_case
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

	/** @var \phpbb\autogroups\controller\admin_controller */
	protected $admin_controller;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\log\log */
	protected $log;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\autogroups\conditions\manager */
	protected $manager;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\request\request */
	protected $request;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/phpbb.autogroups.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$cache = new \phpbb_mock_cache();
		$this->db = $this->new_dbal();
		$this->log = $this->getMockBuilder('\phpbb\log\log')
			->disableOriginalConstructor()
			->getMock();
		$this->manager = $this->getMockBuilder('\phpbb\autogroups\conditions\manager')
			->disableOriginalConstructor()
			->getMock();
		$this->request = $this->getMock('\phpbb\request\request');
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();
		$this->user = new \phpbb\user('\phpbb\datetime');

		$this->admin_controller = new \phpbb\autogroups\controller\admin_controller(
			$cache,
			$this->db,
			$this->log,
			$this->manager,
			$this->request,
			$this->template,
			$this->user,
			'phpbb_autogroups_rules',
			'phpbb_autogroups_types'
		);
	}
}
