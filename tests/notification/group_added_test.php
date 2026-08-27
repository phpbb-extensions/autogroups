<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\notification;

class group_added_test extends \phpbb_test_case
{
	/** @var \phpbb\autogroups\notification\type\group_added */
	protected $notification;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\notification\manager */
	protected $manager;

	protected function setUp(): void
	{
		parent::setUp();

		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$this->language = $this->createMock(\phpbb\language\language::class);
		$user = $this->createMock(\phpbb\user::class);
		$auth = $this->createMock(\phpbb\auth\auth::class);
		$this->manager = $this->createMock(\phpbb\notification\manager::class);

		$this->notification = new \phpbb\autogroups\notification\type\group_added(
			$db,
			$this->language,
			$user,
			$auth,
			$phpbb_root_path,
			$phpEx,
			'phpbb_user_notifications'
		);

		$property = new \ReflectionProperty(\phpbb\notification\type\base::class, 'notification_manager');
		$property->setAccessible(true);
		$property->setValue($this->notification, $this->manager);
	}

	public function test_metadata_and_delivery_contract()
	{
		self::assertSame('phpbb.autogroups.notification.type.group_added', $this->notification->get_type());
		self::assertFalse($this->notification->is_available());
		self::assertSame(42, $this->notification::get_item_id(array('group_id' => 42)));
		self::assertSame(0, $this->notification::get_item_parent_id(array('group_id' => 42)));
		self::assertSame(array(), $this->notification->users_to_query());
		self::assertFalse($this->notification->get_email_template());
		self::assertSame(array(), $this->notification->get_email_template_variables());
	}

	public function find_users_data()
	{
		return array(
			'single user' => array(2, array(2 => array('notification.method.board'))),
			'multiple users' => array(array(2, 7), array(
				2 => array('notification.method.board'),
				7 => array('notification.method.board'),
			)),
			'no users' => array(array(), array()),
		);
	}

	/**
	 * @dataProvider find_users_data
	 */
	public function test_find_users_for_notification($user_ids, $expected)
	{
		$this->manager->expects(self::exactly(count((array) $user_ids)))
			->method('get_default_methods')
			->willReturn(array('notification.method.board'));

		self::assertSame($expected, $this->notification->find_users_for_notification(array('user_ids' => $user_ids)));
	}

	public function test_title_url_and_insert_data()
	{
		global $phpbb_root_path;

		$this->language->expects(self::once())
			->method('lang')
			->with('AUTOGROUPS_NOTIFICATION_GROUP_ADDED', 'Administrators')
			->willReturn('Added to Administrators');

		$this->notification->create_insert_array(array(
			'group_id' => 42,
			'group_name' => 'Administrators',
		));

		self::assertSame('Added to Administrators', $this->notification->get_title());
		self::assertSame($phpbb_root_path . 'memberlist.php?mode=group&amp;g=42', $this->notification->get_url());

		$insert = $this->notification->get_insert_array();
		self::assertSame(42, $insert['item_id']);
		self::assertSame(array('group_name' => 'Administrators'), unserialize($insert['notification_data']));
	}

	public function test_removed_notification_overrides_type_title_and_url()
	{
		global $phpbb_root_path, $phpEx;

		$notification = new \phpbb\autogroups\notification\type\group_removed(
			$this->createMock(\phpbb\db\driver\driver_interface::class),
			$this->language,
			$this->createMock(\phpbb\user::class),
			$this->createMock(\phpbb\auth\auth::class),
			$phpbb_root_path,
			$phpEx,
			'phpbb_user_notifications'
		);
		$notification->create_insert_array(array('group_id' => 42, 'group_name' => 'Administrators'));

		$this->language->expects(self::once())
			->method('lang')
			->with('AUTOGROUPS_NOTIFICATION_GROUP_REMOVED', 'Administrators')
			->willReturn('Removed from Administrators');

		self::assertSame('phpbb.autogroups.notification.type.group_removed', $notification->get_type());
		self::assertSame('Removed from Administrators', $notification->get_title());
		self::assertSame('', $notification->get_url());
	}
}
