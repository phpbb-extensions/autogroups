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

class check_conditions_test extends base_manager
{
	/**
	 * Test the sync_autogroups method
	 */
	public function test_check_conditions()
	{
		// Mock get_users_for_condition() and test expected values
		$this->condition->expects($this->exactly(3))
			->method('get_users_for_condition')
			->with($this->anything())
			->will($this->returnValue(array(1 => 'foo', 2 => 'bar')));

		// Mock check() and test expected values
		$this->condition->expects($this->exactly(3))
			->method('check')
			->with(array(1 => 'foo', 2 => 'bar'))
			->will($this->returnValue(null));

		// Use mocked condition when container->get()
		$this->container->expects($this->exactly(3))
			->method('get')
			->with($this->anything())
			->will($this->returnValue($this->condition));

		// Call and test sync autogroups
		$this->assertNull($this->manager->check_conditions());
	}
}
