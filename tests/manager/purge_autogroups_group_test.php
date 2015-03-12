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

class purge_autogroups_group_test extends base_manager
{
	/**
	 * Data for test_purge_autogroups_group
	 *
	 * @return array Array of test data
	 */
	public function purge_autogroups_group_test_data()
	{
		// In the fixture we have the following settings:
		// Rule 1 applies to group 1
		// Rule 2 applies to group 2
		// Rule 3 applies to group 2
		return array(
			array(1, array(2, 3)), // delete group 1, deletes rule 1
			array(2, array(1)), // delete group 2, deletes rules 2 and 3
			array(3, array(1, 2, 3)), // group 3 does not apply, no change
		);
	}

	/**
	 * Test the purge_autogroups_group method
	 *
	 * @dataProvider purge_autogroups_group_test_data
	 */
	public function test_purge_autogroups_group($group_id, $expected)
	{
		$autogroups_id = array();

		// Delete autogroup rule(s) by group_id
		$this->manager->purge_autogroups_group($group_id);

		// Get all autogroup rule identifiers still in the dbd
		$sql = 'SELECT autogroups_id FROM phpbb_autogroups_rules';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$autogroups_id[] = $row['autogroups_id'];
		}
		$this->db->sql_freeresult();

		// Check that the remaining autogroup rules matches expected
		$this->assertEquals($expected, $autogroups_id);
	}
}
