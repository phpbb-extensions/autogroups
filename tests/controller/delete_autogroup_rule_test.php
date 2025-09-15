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

use Symfony\Component\HttpFoundation\JsonResponse;

class delete_autogroup_rule_test extends admin_controller_base
{
	/**
	 * Data set for test_delete_autogroup_rule
	 *
	 * @return array Array of test data
	 */
	public function delete_autogroup_rule_data()
	{
		return array(
			// id, exists in db
			array(0, 0),
			array(1, 1),
			array(2, 1),
			array(3, 0),
		);
	}

	/**
	 * Test the delete_autogroup_rule() method
	 *
	 * @dataProvider delete_autogroup_rule_data
	 */
	public function test_delete_autogroup_rule($id, $expected)
	{
		// Assert the admin_controller is instantiated
		self::assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// First check the autogroup rule exists as expected
		self::assertEquals($expected, $this->get_autogroup_rule_count($id));

		// Prevent AJAX request
		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn(false);

		// Call the delete_autogroup_rule() method
		$this->admin_controller->delete_autogroup_rule($id);

		// Verify the autogroup rule has been removed
		self::assertEquals(0, $this->get_autogroup_rule_count($id));
	}

	/**
	 * Test the delete_autogroup_rule() method with AJAX
	 */
	public function test_delete_autogroup_rule_ajax()
	{
		// Assert the admin_controller is instantiated
		self::assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// First check the autogroup rule exists as expected
		self::assertEquals(1, $this->get_autogroup_rule_count(1));

		// AJAX request
		$this->request->expects(self::once())
			->method('is_ajax')
			->willReturn(true);

		// Call the delete_autogroup_rule() method
		$response = $this->admin_controller->delete_autogroup_rule(1);
		self::assertInstanceOf(JsonResponse::class, $response);

		// Verify the autogroup rule has been removed
		self::assertEquals(0, $this->get_autogroup_rule_count(1));
	}

	/**
	 * Get the number of autogroup rules by their identifier
	 *
	 * @param int $id An autogroup rule identifier
	 * @return int The count of autogroup rules found
	 */
	public function get_autogroup_rule_count($id)
	{
		$sql = 'SELECT COUNT(autogroups_id) AS autogroups_id
			FROM phpbb_autogroups_rules
			WHERE autogroups_id = ' . (int) $id;
		$result = $this->db->sql_query($sql);
		$count = $this->db->sql_fetchfield('autogroups_id');
		$this->db->sql_freeresult($result);

		return (int) $count;
	}
}
