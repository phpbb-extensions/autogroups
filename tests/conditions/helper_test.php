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
	 * Data for test_get_users_groups
	 */
	public static function get_users_groups_test_data()
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
		self::assertEquals($expected, $result);
	}

	/**
	 * Test the get_default_exempt_users method
	 * In our tables, only user 3 is in a default exempt group
	 */
	public function test_get_default_exempt_users()
	{
		self::assertEquals(array(3), $this->helper->get_default_exempt_users());
	}

	/**
	 * Data for test_prepare_users_for_query
	 */
	public static function prepare_users_for_query_test_data()
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
		self::assertEquals($expected, $this->helper->prepare_users_for_query($user_ids));
	}

	/**
	 * Data for test_send_notifications
	 */
	public static function send_notifications_data()
	{
		return array(
			array(
				'posts',
				array(
					'user_ids' => array(1, 2, 3),
					'group_id' => 2,
					'group_name' => 'FOO',
				),
			),
			array(
				'membership',
				array(
					'user_ids' => array(0),
					'group_id' => 3,
					'group_name' => 'BAR',
				),
			),
			array(
				'warnings',
				array(
					'user_ids' => array(),
					'group_id' => 4,
					'group_name' => 'Global moderators',
				),
			),
		);
	}

	/**
	 * Test the send_notifications method
	 *
	 * @dataProvider send_notifications_data
	 */
	public function test_send_notifications($type, $expected)
	{
		$this->notification_manager->expects(self::once())
			->method('add_notifications')
			->with("phpbb.autogroups.notification.type.$type", $expected);

		$this->helper->send_notifications($type, $expected['user_ids'], $expected['group_id']);
	}
}
