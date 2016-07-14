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

require_once __DIR__ . '/../../../../../includes/functions.php';
require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class resync_autogroup_rule_test extends admin_controller_base
{
	/**
	 * Data set for test_resync_autogroup_rule
	 *
	 * @return array Array of test data
	 */
	public function resync_autogroup_rule_data()
	{
		return array(
			array(0),
			array(1),
			array(2),
		);
	}

	/**
	 * Test the resync_autogroup_rule() method.
	 *
	 * @dataProvider resync_autogroup_rule_data
	 */
	public function test_resync_autogroup_rule($id)
	{
		// Assert the admin_controller is instantiated
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Set a valid link hash
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap(array(
				array('hash', '', false, \phpbb\request\request_interface::REQUEST, generate_link_hash('sync' . $id))
			)))
		;

		// Test that the sync_autogroups() method is
		// called once with the identifier as its arg.
		$this->manager->expects($this->once())
			->method('sync_autogroups')
			->with($this->equalTo($id));

		// Call the save_autogroup_rule() method
		$this->admin_controller->resync_autogroup_rule($id);
	}

	/**
	 * Test the resync_autogroup_rule() method fails
	 * with an invalid link_hash.
	 *
	 * @dataProvider resync_autogroup_rule_data
	 */
	public function test_resync_autogroup_rule_fails($id)
	{
		// Assert the admin_controller is instantiated
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Set an invalid link hash
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap(array(
				array('hash', '', false, \phpbb\request\request_interface::REQUEST, generate_link_hash('foobar' . $id))
			)))
		;

		// Test that the sync_autogroups() method
		// is never called during execution.
		$this->manager->expects($this->never())
			->method('sync_autogroups')
			->with($this->equalTo($id));

		// Check that trigger_error() is called
		$this->setExpectedTriggerError(E_USER_WARNING);

		// Call the save_autogroup_rule() method
		$this->admin_controller->resync_autogroup_rule($id);
	}

	/**
	 * Test the resync_autogroup_rule() method fails
	 * when an exception is caught.
	 *
	 * @dataProvider resync_autogroup_rule_data
	 */
	public function test_resync_autogroup_rule_exception($id)
	{
		// Assert the admin_controller is instantiated
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Set a valid link hash
		$this->request->expects($this->any())
			->method('variable')
			->with($this->anything())
			->will($this->returnValueMap(array(
				array('hash', '', false, \phpbb\request\request_interface::REQUEST, generate_link_hash('sync' . $id))
			)))
		;

		// Make the sync_autogroups() method throw an exception.
		$this->manager->expects($this->once())
			->method('sync_autogroups')
			->with($this->anything())
			->will($this->throwException(new \Exception()))
		;

		// Check that trigger_error() is called
		$this->setExpectedTriggerError(E_USER_WARNING);

		// Call the save_autogroup_rule() method
		$this->admin_controller->resync_autogroup_rule($id);
	}
}
