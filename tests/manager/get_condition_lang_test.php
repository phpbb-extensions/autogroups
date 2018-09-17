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

class get_condition_lang_test extends base_manager
{
	/**
	 * Data for test_get_condition_lang
	 *
	 * @return array Array of test data
	 */
	public function get_condition_lang_test_data()
	{
		return array(
			array('phpbb.autogroups.type.birthdays', 'AUTOGROUPS_TYPE_BIRTHDAYS'),
			array('phpbb.autogroups.type.lastvisit', 'AUTOGROUPS_TYPE_LASTVISIT'),
			array('phpbb.autogroups.type.membership', 'AUTOGROUPS_TYPE_MEMBERSHIP'),
			array('phpbb.autogroups.type.posts', 'AUTOGROUPS_TYPE_POSTS'),
			array('phpbb.autogroups.type.warnings', 'AUTOGROUPS_TYPE_WARNINGS'),
		);
	}

	/**
	 * Test the get_condition_lang method
	 *
	 * @dataProvider get_condition_lang_test_data
	 */
	public function test_get_condition_lang($type_name, $expected)
	{
		// Mock the container builder and set the condition type
		$phpbb_container = new \phpbb_mock_container_builder();
		$type_parts = explode('.', $type_name);
		$condition = '\phpbb\autogroups\conditions\type\\' . array_pop($type_parts);
		$phpbb_container->set($type_name, new $condition(
			$this->container,
			$this->db,
			$this->lang,
			'phpbb_autogroups_rules',
			'phpbb_autogroups_types',
			__DIR__ . '/../../../../../',
			'php'
		));

		// Instantiate a new manager using the new mocked container
		$manager = new \phpbb\autogroups\conditions\manager(
			array(),
			$phpbb_container,
			new \phpbb_mock_cache(),
			$this->db,
			$this->lang,
			'phpbb_autogroups_rules',
			'phpbb_autogroups_types'
		);

		// Assert the expected lang var is returned by the condition
		$this->assertEquals($expected, $manager->get_condition_lang($type_name));
	}

	/**
	 * Data for test_get_condition_lang_fails
	 *
	 * @return array Array of test data
	 */
	public function get_condition_lang_fails_test_data()
	{
		return array(
			array('phpbb.autogroups.type.sample', 'AUTOGROUPS_TYPE_NOT_EXIST'),
		);
	}

	/**
	 * Test the get_condition_lang method fails
	 *
	 * @dataProvider get_condition_lang_fails_test_data
	 */
	public function test_get_condition_lang_fails($type_name, $expected)
	{
		// Use mocked condition when container->get()
		$this->container->expects($this->any())
			->method('get')
			->with($type_name)
			->will($this->throwException(new \InvalidArgumentException()));

		// Assert the expected lang var is returned by the condition
		$this->assertEquals($expected, $this->manager->get_condition_lang($type_name));
	}
}
