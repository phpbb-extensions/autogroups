<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests;

class cron_test extends \phpbb_test_case
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\autogroups\cron\autogroups_check */
	protected $cron_task;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\autogroups\conditions\manager */
	protected $manager;

	protected function setUp(): void
	{
		parent::setUp();

		$this->config = new \phpbb\config\config(array());
		$this->manager = $this->getMockBuilder('\phpbb\autogroups\conditions\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->cron_task = new \phpbb\autogroups\cron\autogroups_check($this->config, $this->manager);
	}

	/**
	 * Test the cron task runs correctly
	 */
	public function test_run()
	{
		// Get the autogroups_last_run
		$autogroups_last_run = $this->config['autogroups_last_run'];

		// Test check_conditions() is called only once
		$this->manager->expects(self::once())
			->method('check_conditions');

		// Run the cron task
		$this->cron_task->run();

		// Assert the autogroups_last_run value has been updated
		self::assertNotEquals($autogroups_last_run, $this->config['autogroups_last_run']);
	}

	/**
	 * Data set for test_should_run
	 *
	 * @return array Array of test data
	 */
	public function should_run_data()
	{
		return array(
			array(time(), false),
			array(strtotime('23 hours ago'), false),
			array(strtotime('25 hours ago'), true),
			array('', true),
			array(0, true),
			array(null, true),
		);
	}

	/**
	 * Test cron task should run after 24 hours
	 *
	 * @dataProvider should_run_data
	 */
	public function test_should_run($time, $expected)
	{
		// Set the last cron run time
		$this->config['autogroups_last_run'] = $time;

		// Assert we get the expected result from should_run()
		self::assertSame($expected, $this->cron_task->should_run());
	}

	/**
	 * Test the cron task is runnable
	 */
	public function test_is_runnable()
	{
		self::assertTrue($this->cron_task->is_runnable());
	}
}
