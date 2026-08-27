<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\controller;

class submit_autogroups_options_test extends admin_controller_base
{
	protected function tearDown(): void
	{
		unset($_POST['cancel']);
		parent::tearDown();
	}

	public function test_submit_autogroups_options_requests_confirmation()
	{
		$_POST['cancel'] = true;
		$this->request->expects(self::once())
			->method('variable')
			->with('group_ids', array(0))
			->willReturn(array(1));

		$this->admin_controller->submit_autogroups_options();

		self::assertSame(array(2), $this->get_exempt_group_ids());
	}

	public function test_submit_autogroups_options_updates_selected_groups()
	{
		global $db, $language, $phpbb_root_path, $phpEx, $request, $user;

		$db = $this->db;
		$request = $this->request;
		$language = new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx));
		$user->session_id = 'session-id';
		$user->data['user_last_confirm_key'] = 'confirm-key';

		$yes = $language->lang('YES');
		$this->request->method('variable')->willReturnCallback(function ($name, $default) use ($yes) {
			$values = array(
				'group_ids' => array(1),
				'confirm' => $yes,
				'confirm_uid' => 2,
				'sess' => 'session-id',
				'confirm_key' => 'confirm-key',
			);
			return array_key_exists($name, $values) ? $values[$name] : $default;
		});

		$this->admin_controller->submit_autogroups_options();

		self::assertSame(array(1), $this->get_exempt_group_ids());
	}

	public function test_get_excluded_groups_returns_selected_names()
	{
		$method = new \ReflectionMethod($this->admin_controller, 'get_excluded_groups');
		$method->setAccessible(true);

		self::assertSame(array(1 => 'GROUP1'), $method->invoke($this->admin_controller, '[1]'));
	}

	private function get_exempt_group_ids()
	{
		$sql = 'SELECT group_id
			FROM ' . GROUPS_TABLE . '
			WHERE autogroup_default_exempt = 1
			ORDER BY group_id';
		$result = $this->db->sql_query($sql);
		$group_ids = array_map('intval', array_column($this->db->sql_fetchrowset($result), 'group_id'));
		$this->db->sql_freeresult($result);

		return $group_ids;
	}
}
