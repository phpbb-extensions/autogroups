<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\conditions;

/**
 * Run tests on the conditions helper class.
 */
class helper_test extends base
{
	protected $condition_type = 'phpbb.autogroups.type.posts';

	/**
	 * Data for test_get_default_exempt_users
	 */
	public function get_default_exempt_users_test_data()
	{
		return array(
			array(
				array(),
				array(),
			),
			array(
				array(1),
				array(1, 2),
			),
			array(
				array(2),
				array(2),
			),
			array(
				array(3),
				array(),
			),
			array(
				array(1, 2, 3),
				array(1, 2),
			),
		);
	}

	/**
	 * Test the get_default_exempt_users method
	 *
	 * @dataProvider get_default_exempt_users_test_data
	 */
	public function test_get_default_exempt_users($groups, $expected)
	{
		$this->config['autogroups_default_exempt'] = serialize($groups);

		// Get the user's groups
		$result = $this->helper->get_default_exempt_users();

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $result);
	}

	/**
	 * Data for test_get_users_groups
	 */
	public function get_users_groups_test_data()
	{
		return array(
			array(
				1,
				array(
					1 => array(1, 5),
				),
			),
			array(
				2,
				array(
					2 => array(1, 2),
				),
			),
			array(
				array(1, 2),
				array(
					1 => array(1, 5),
					2 => array(1, 2),
				),
			),
			array(
				array(),
				array(),
			),
		);
	}

	/**
	 * Test the get_users_groups method
	 *
	 * @dataProvider get_users_groups_test_data
	 */
	public function test_get_users_groups($user_id, $expected)
	{
		if (!is_array($user_id))
		{
			$user_id = array($user_id);
		}

		// Get the user's groups
		$result = $this->helper->get_users_groups($user_id);

		// Assert the user's groups are as expected
		$this->assertEquals($expected, $result);
	}

	/**
	 * Data for test_prepare_users_for_query
	 */
	public function prepare_users_for_query_test_data()
	{
		return array(
			array(1, array(1)),
			array(array(1, 2), array(1, 2)),
			array('1', array(1)),
			array(array(1, '2', 'foobar', '', true, false), array(1, 2, 0, 0, 1, 0)),
			array('', array(0)),
			array(array(), array()),
		);
	}

	/**
	 * Test the prepare_users_for_query method
	 *
	 * @dataProvider prepare_users_for_query_test_data
	 */
	public function test_prepare_users_for_query($user_ids, $expected)
	{
		$this->assertEquals($expected, $this->helper->prepare_users_for_query($user_ids));
	}
}
