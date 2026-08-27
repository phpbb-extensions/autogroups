<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\conditions;

class helper_include_test extends \phpbb_test_case
{
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_send_notifications_loads_user_functions_when_needed()
	{
		global $db, $phpbb_container, $phpbb_root_path, $phpEx;

		self::assertFalse(function_exists('get_group_name'));

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$db->expects(self::once())->method('sql_query')->willReturn('result');
		$db->expects(self::once())->method('sql_fetchrow')->with('result')->willReturn(array(
			'group_name' => 'ADMINISTRATORS',
			'group_type' => GROUP_SPECIAL,
		));
		$db->expects(self::once())->method('sql_freeresult')->with('result');

		$group_helper = $this->createMock(\phpbb\group\helper::class);
		$group_helper->expects(self::once())->method('get_name')->with('ADMINISTRATORS')->willReturn('Administrators');
		$phpbb_container = new \phpbb_mock_container_builder();
		$phpbb_container->set('group_helper', $group_helper);

		$notification_manager = $this->createMock(\phpbb\notification\manager::class);
		$notification_manager->expects(self::once())
			->method('add_notifications')
			->with('phpbb.autogroups.notification.type.group_added', array(
				'user_ids' => array(2),
				'group_id' => 5,
				'group_name' => 'Administrators',
			));

		$helper = new \phpbb\autogroups\conditions\type\helper($db, $notification_manager, $phpbb_root_path, $phpEx);
		$helper->send_notifications('group_added', array(2), 5);

		self::assertTrue(function_exists('get_group_name'));
	}
}
