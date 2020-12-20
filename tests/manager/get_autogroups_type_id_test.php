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

class get_autogroups_type_id_test extends base_manager
{
	/**
	 * Data for test_get_autogroups_type_id_from_db
	 *
	 * @return array Array of test data
	 */
	public function get_autogroups_type_id_from_db_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample1', 1),
			array('phpbb.autogroups.type.sample2', 2),
		);
	}

	/**
	 * Test the get_autogroups_type_id method
	 * with types that exist in the database.
	 *
	 * @dataProvider get_autogroups_type_id_from_db_test_data
	 */
	public function test_get_autogroups_type_id_from_db($type_name, $expected)
	{
		self::assertSame($expected, $this->manager->get_autogroups_type_id($type_name));
	}

	/**
	 * Data for test_get_autogroups_type_id_from_service
	 *
	 * @return array Array of test data
	 */
	public function get_autogroups_type_id_from_service_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample3', 3),
		);
	}

	/**
	 * Test the get_autogroups_type_id method
	 * with types that exist as a service but are missing
	 * from the database, and are added to the database.
	 *
	 * @dataProvider get_autogroups_type_id_from_service_test_data
	 */
	public function test_get_autogroups_type_id_from_service($type_name, $expected)
	{
		self::assertSame($expected, $this->manager->get_autogroups_type_id($type_name));
	}

	/**
	 * Test data for the test_get_autogroups_type_id_fails function
	 *
	 * @return array Array of test data
	 */
	public function get_autogroups_type_id_fails_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample'),
			array(''),
			array(0),
			array(1),
			array(null),
		);
	}

	/**
	 * Test getting invalid type_names which should throw an exception
	 *
	 * @dataProvider get_autogroups_type_id_fails_test_data
	 * @expectedException \phpbb\exception\runtime_exception
	 */
	public function test_get_autogroups_type_id_fails($type_name)
	{
		$this->manager->get_autogroups_type_id($type_name);
	}
}
