<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\manager;

/**
* Base class for conditions manager tests
*/
class base_manager extends \phpbb_database_test_case
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

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\autogroups\conditions\type\base */
	protected $condition;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\DependencyInjection\ContainerInterface */
	protected $container;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\autogroups\conditions\manager */
	protected $manager;

	/** @var \phpbb\user */
	protected $user;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/phpbb.autogroups.xml');
	}

	public function setUp()
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->user = new \phpbb\user('\phpbb\datetime');
		$cache = new \phpbb_mock_cache();
		$this->container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');

		// Mock the condition
		$this->condition = $this->getMockBuilder('\phpbb\autogroups\conditions\type\base')
			->disableOriginalConstructor()
			->getMock();

		$this->manager = new \phpbb\autogroups\conditions\manager(
			array(
				'phpbb.autogroups.type.sample1' => '',
				'phpbb.autogroups.type.sample2' => '',
				'phpbb.autogroups.type.sample3' => '',
			),
			$this->container,
			$cache,
			$this->db,
			$this->user,
			'phpbb_autogroups_rules',
			'phpbb_autogroups_types'
		);
	}
}
