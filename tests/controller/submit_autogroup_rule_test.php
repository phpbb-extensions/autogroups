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

require_once __DIR__ . '/../../../../../includes/functions_acp.php';

class submit_autogroup_rule_test extends admin_controller_base
{
	/**
	 * Data set for test_submit_autogroup_rule
	 *
	 * @return array Array of test data
	 */
	public function submit_autogroup_rule_data()
	{
		return array(
			array(
				0, // test insert new data
				array(
					array('autogroups_type_id', 0, false, \phpbb\request\request_interface::REQUEST, 0),
					array('autogroups_min_value', 0, false, \phpbb\request\request_interface::REQUEST, 12),
					array('autogroups_max_value', 0, false, \phpbb\request\request_interface::REQUEST, 34),
					array('autogroups_group_id', 0, false, \phpbb\request\request_interface::REQUEST, 1),
					array('autogroups_default', false, false, \phpbb\request\request_interface::REQUEST, true),
					array('autogroups_notify', false, false, \phpbb\request\request_interface::REQUEST, true),
				),
				E_USER_NOTICE,
			),
			array(
				1, // test update existing data
				array(
					array('autogroups_type_id', 0, false, \phpbb\request\request_interface::REQUEST, 1),
					array('autogroups_min_value', 0, false, \phpbb\request\request_interface::REQUEST, 56),
					array('autogroups_max_value', 0, false, \phpbb\request\request_interface::REQUEST, 78),
					array('autogroups_group_id', 0, false, \phpbb\request\request_interface::REQUEST, 1),
					array('autogroups_default', false, false, \phpbb\request\request_interface::REQUEST, true),
					array('autogroups_notify', false, false, \phpbb\request\request_interface::REQUEST, true),
				),
				E_USER_NOTICE,
			),
			array(
				2, // test error: no group id
				array(
					array('autogroups_type_id', 0, false, \phpbb\request\request_interface::REQUEST, 2),
					array('autogroups_min_value', 0, false, \phpbb\request\request_interface::REQUEST, 56),
					array('autogroups_max_value', 0, false, \phpbb\request\request_interface::REQUEST, 78),
					array('autogroups_group_id', 0, false, \phpbb\request\request_interface::REQUEST, 0),
					array('autogroups_default', false, false, \phpbb\request\request_interface::REQUEST, true),
					array('autogroups_notify', false, false, \phpbb\request\request_interface::REQUEST, true),
				),
				E_USER_WARNING,
			),
			array(
				3, // test error: max = min values
				array(
					array('autogroups_type_id', 0, false, \phpbb\request\request_interface::REQUEST, 3),
					array('autogroups_min_value', 0, false, \phpbb\request\request_interface::REQUEST, 0),
					array('autogroups_max_value', 0, false, \phpbb\request\request_interface::REQUEST, 0),
					array('autogroups_group_id', 0, false, \phpbb\request\request_interface::REQUEST, 1),
					array('autogroups_default', false, false, \phpbb\request\request_interface::REQUEST, true),
					array('autogroups_notify', false, false, \phpbb\request\request_interface::REQUEST, true),
				),
				E_USER_WARNING,
			),
		);
	}

	/**
	 * Test the save_autogroup_rule() method when submitting data
	 * Data is submitted by the submit_autogroup_rule() method
	 *
	 * @dataProvider submit_autogroup_rule_data
	 */
	public function test_submit_autogroup_rule($id, $requestMap, $errNo)
	{
		// Assert the admin_controller is instantiated
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Return true from is_set_post()
		$this->request->expects($this->any())
			->method('is_set_post')
			->will($this->returnValue(true));

		// Get the requested variable data
		$this->request->expects($this->any())
			->method('variable')
			->will($this->returnValueMap($requestMap));

		// Check that the expected trigger_error() is called
		$this->setExpectedTriggerError($errNo);

		// Call the save_autogroup_rule() method
		$this->admin_controller->save_autogroup_rule($id);
	}
}
