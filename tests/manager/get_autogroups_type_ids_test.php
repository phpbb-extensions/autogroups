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

class get_autogroups_type_ids_test extends base_manager
{
	/**
	 * Test the get_autogroups_type_ids method
	 * Should load all available service types and their identifiers
	 */
	public function test_get_autogroups_type_ids()
	{
		$expected = array(
			'phpbb.autogroups.type.sample1' => 1,
			'phpbb.autogroups.type.sample2' => 2,
			'phpbb.autogroups.type.sample3' => 3,
		);

		$this->assertSame($expected, $this->manager->get_autogroups_type_ids());
	}
}
