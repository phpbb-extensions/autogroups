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

class add_autogroups_type_test extends base_manager
{
	/**
	 * Data for test_add_autogroups_type
	 *
	 * @return array Array of test data
	 */
	public function add_autogroups_type_test_data()
	{
		// The database insertion does not persist between tests,
		// so the expected ID of each insertion will always be 3.
		// Type names come from the service definitions, so
		// should always have content and be unique.
		return array(
			array('phpbb.autogroups.type.sample3', 3),
			array('phpbb.autogroups.type.sample4', 3),
		);
	}

	/**
	 * Test the add_autogroups_type method
	 *
	 * @dataProvider add_autogroups_type_test_data
	 */
	public function test_add_autogroups_type($type_name, $expected)
	{
		$inserted_id = $this->manager->add_autogroups_type($type_name);

		$this->assertEquals($expected, $inserted_id);
	}
}
