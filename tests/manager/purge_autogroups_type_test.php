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

class purge_autogroups_type_test extends base_manager
{
	/**
	 * Data for test_purge_autogroups_type
	 *
	 * @return array Array of test data
	 */
	public static function purge_autogroups_type_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample1'),
			array('phpbb.autogroups.type.sample2'),
		);
	}

	/**
	 * Test the purge_autogroups_type method
	 *
	 * @dataProvider purge_autogroups_type_test_data
	 */
	public function test_purge_autogroups_type($type_name)
	{
		// First, we need to grab the id of this auto group type_name
		$sql = "SELECT autogroups_type_id
			FROM phpbb_autogroups_types
			WHERE autogroups_type_name = '" . $this->db->sql_escape($type_name) . "'";
		$result = $this->db->sql_query($sql);
		$type_id = $this->db->sql_fetchfield('autogroups_type_id');
		$this->db->sql_freeresult($result);

		// Check that there are types and rules
		self::assertGreaterThan(0, $this->count_types($type_name));
		self::assertGreaterThan(0, $this->count_rules($type_id));

		// Purge auto group data
		$this->manager->purge_autogroups_type($type_name);

		// Check that there are no more types or rules
		self::assertEquals(0, $this->count_types($type_name));
		self::assertEquals(0, $this->count_rules($type_id));
	}

	/**
	 * Get a count of type_name in phpbb_autogroups_types
	 *
	 * @param $type_id
	 * @return int
	 */
	public function count_rules($type_id)
	{
		$sql = 'SELECT COUNT(autogroups_id) AS rules
			FROM phpbb_autogroups_rules
			WHERE autogroups_type_id = ' . (int) $type_id;
		$result = $this->db->sql_query($sql);
		$rules = $this->db->sql_fetchfield('rules');
		$this->db->sql_freeresult($result);

		return (int) $rules;
	}

	/**
	 * Get count of autogroups using type_name in phpbb_autogroups_rules
	 *
	 * @param $type_name
	 * @return int
	 */
	public function count_types($type_name)
	{
		$sql = "SELECT COUNT(autogroups_type_id) AS types
			FROM phpbb_autogroups_types
			WHERE autogroups_type_name = '" . $this->db->sql_escape($type_name) . "'";
		$result = $this->db->sql_query($sql);
		$types = $this->db->sql_fetchfield('types');
		$this->db->sql_freeresult($result);

		return (int) $types;
	}
}
