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

class get_autogroups_type_name_test extends base_manager
{
	/**
	 * Data for test_get_autogroups_type_name
	 *
	 * @return array Array of test data
	 */
	public static function get_autogroups_type_name_test_data()
	{
		return array(
			array(1, 0, 'phpbb.autogroups.type.sample1'),
			array(2, 0, 'phpbb.autogroups.type.sample2'),
			array(0, 1, 'phpbb.autogroups.type.sample1'),
			array(0, 2, 'phpbb.autogroups.type.sample1'),
			array(0, 3, 'phpbb.autogroups.type.sample2'),
			array(1, 3, 'phpbb.autogroups.type.sample1'),
			array(0, 0, false),
			array(100, 0, false),
			array(0, 100, false),
		);
	}

	/**
	 * Test the get_autogroups_type_name method
	 *
	 * @dataProvider get_autogroups_type_name_test_data
	 */
	public function test_get_autogroups_type_name($type_id, $rule_id, $expected)
	{
		self::assertSame($expected, $this->manager->get_autogroups_type_name($type_id, $rule_id));
	}
}
