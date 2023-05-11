<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\extension;

class ext_test extends \phpbb_database_test_case
{
	protected const EXTENSION = 'phpbb/autogroups';

	/** @var \phpbb\extension\manager \phpbb\extension\manager */
	protected $extension_manager;

	/** @var \phpbb\notification\manager */
	protected $notifications;

	public function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__ . '/fixtures/extensions.xml');
	}

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = $this->new_dbal();
		$this->extension_manager = $this->create_extension_manager();
	}

	public function test_steps()
	{
		// Enable it
		$this->extension_manager->enable(self::EXTENSION);

		// Assert it's enabled
		$this->assertEquals([self::EXTENSION], array_keys($this->extension_manager->all_enabled()));
		$this->assertEquals([self::EXTENSION], array_keys($this->extension_manager->all_configured()));

		// Assert the notifications were enabled
		$this->assertEquals(1, $this->get_notifications()['phpbb.autogroups.notification.type.group_added']);
		$this->assertEquals(1, $this->get_notifications()['phpbb.autogroups.notification.type.group_removed']);

		// Disable it
		$this->extension_manager->disable(self::EXTENSION);

		// Assert it's disabled
		$this->assertEquals([self::EXTENSION], array_keys($this->extension_manager->all_disabled()));

		// Assert the notifications were enabled
		$this->assertEquals(0, $this->get_notifications()['phpbb.autogroups.notification.type.group_added']);
		$this->assertEquals(0, $this->get_notifications()['phpbb.autogroups.notification.type.group_removed']);

		// Purge it
		$this->extension_manager->purge(self::EXTENSION);

		// Assert it's purged
		$this->assertArrayNotHasKey(self::EXTENSION, $this->extension_manager->all_enabled());

		// Assert the notifications are deleted
		$this->assertCount(0, $this->get_notifications());
	}

	protected function create_extension_manager()
	{
		global $phpbb_root_path, $php_ext;

		$config = new \phpbb\config\config(['version' => PHPBB_VERSION, 'allow_board_notifications' => 1]);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$factory = new \phpbb\db\tools\factory();
		$db_tools = $factory->get($this->db);
		$table_prefix = 'phpbb_';

		$container = new \phpbb_mock_container_builder();

		$migrator = new \phpbb\db\migrator(
			$container,
			$config,
			$this->db,
			$db_tools,
			'phpbb_migrations',
			$phpbb_root_path,
			$php_ext,
			$table_prefix,
			[],
			new \phpbb\db\migration\helper()
		);
		$container->set('migrator', $migrator);
		$container->set('dispatcher', $phpbb_dispatcher);

		$cache_driver = new \phpbb\cache\driver\dummy();
		$cache = new \phpbb\cache\service(
			$cache_driver,
			$config,
			$this->db,
			$phpbb_dispatcher,
			$phpbb_root_path,
			$php_ext
		);

		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $php_ext));

		$user_loader = new \phpbb\user_loader($this->db, $phpbb_root_path, $php_ext, USERS_TABLE);
		$user = $this->createMock('\phpbb\user');

		$container->set('notification.method.board', new \phpbb\notification\method\board(
			$user_loader,
			$this->db,
			new \phpbb_mock_cache(),
			$user,
			$config,
			NOTIFICATION_TYPES_TABLE,
			NOTIFICATIONS_TABLE
		));

		$this->notifications = new \phpbb\notification\manager(
			[],
			['notification.method.board' => $container->get('notification.method.board')],
			$container,
			$user_loader,
			$phpbb_dispatcher,
			$this->db,
			$cache,
			$language,
			$user,
			NOTIFICATION_TYPES_TABLE,
			USER_NOTIFICATIONS_TABLE
		);
		$container->set('notification_manager', $this->notifications);

		return new \phpbb\extension\manager(
			$container,
			$this->db,
			$config,
			new \phpbb\filesystem\filesystem(),
			EXT_TABLE,
			$phpbb_root_path,
			$php_ext,
			null
		);
	}

	protected function get_notifications()
	{
		$notification_type_ids = [];
		$sql = 'SELECT *
			FROM ' . NOTIFICATION_TYPES_TABLE;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$notification_type_ids[$row['notification_type_name']] = (int) $row['notification_type_enabled'];
		}
		$this->db->sql_freeresult($result);
		return $notification_type_ids;
	}
}
