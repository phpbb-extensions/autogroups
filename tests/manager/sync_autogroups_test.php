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

class sync_autogroups_test extends base_manager
{
	/**
	 * Data for test_sync_autogroups
	 *
	 * @return array Array of test data
	 */
	public static function sync_autogroups_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample1', 1),
			array('phpbb.autogroups.type.sample1', 2),
			array('phpbb.autogroups.type.sample2', 3),
		);
	}

	/**
	 * Test the sync_autogroups method
	 *
	 * @dataProvider sync_autogroups_test_data
	 */
	public function test_sync_autogroups($type_name, $rule_id)
	{
		// Options data always passed by sync_autogroups to check()
		$options = array('action' => 'sync');

		// Mock get_users_for_condition() and test expected values
		$this->condition->expects(self::once())
			->method('get_users_for_condition')
			->with($options)
			->willReturn(array());

		// Mock check() and test expected values
		$this->condition->expects(self::once())
			->method('check')
			->with(array(), $options)
			->willReturn(null);

		// Use mocked condition when container->get()
		$this->container->expects(self::once())
			->method('get')
			->with($type_name)
			->willReturn($this->condition);

		// Call and test sync autogroups
		$this->manager->sync_autogroups($rule_id);
	}
}
