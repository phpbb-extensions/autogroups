<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\acp;

class autogroups_module_test extends \phpbb_test_case
{
	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\autogroups\controller\admin_interface */
	protected $controller;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\language\language */
	protected $language;

	/** @var \PHPUnit\Framework\MockObject\MockObject|\phpbb\request\request */
	protected $request;

	protected function setUp(): void
	{
		parent::setUp();

		global $db, $language, $phpbb_container, $phpbb_dispatcher, $request, $user;

		$this->controller = $this->createMock(\phpbb\autogroups\controller\admin_interface::class);
		$this->language = $this->createMock(\phpbb\language\language::class);
		$this->request = $this->createMock(\phpbb\request\request::class);

		$phpbb_container = $this->createMock(\Symfony\Component\DependencyInjection\ContainerInterface::class);
		$services = array(
			'language' => $this->language,
			'request' => $this->request,
			'phpbb.autogroups.admin_controller' => $this->controller,
		);
		$phpbb_container->method('get')->willReturnCallback(function ($id) use ($services) {
			return $services[$id];
		});

		$language = $this->language;
		$request = $this->request;
		$db = $this->createMock(\phpbb\db\driver\driver_interface::class);
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();
		$user = new \stdClass();
		$user->data = array(
			'user_id' => 2,
			'user_last_confirm_key' => 'confirm-key',
		);
		$user->session_id = 'session-id';
	}

	protected function tearDown(): void
	{
		unset($_POST['cancel']);
		parent::tearDown();
	}

	public function action_data()
	{
		return array(
			'list' => array('', null, null, 1, 'ACP_AUTOGROUPS_MANAGE'),
			'add' => array('add', 'save_autogroup_rule', 0, 0, 'ACP_AUTOGROUPS_ADD'),
			'edit' => array('edit', 'save_autogroup_rule', 7, 0, 'ACP_AUTOGROUPS_EDIT'),
			'sync' => array('sync', 'resync_autogroup_rule', 7, 1, 'ACP_AUTOGROUPS_MANAGE'),
		);
	}

	/**
	 * @dataProvider action_data
	 */
	public function test_main_actions($action, $called_method, $autogroups_id, $display_count, $page_title)
	{
		$this->request->method('variable')->willReturnMap(array(
			array('action', '', false, \phpbb\request\request_interface::REQUEST, $action),
			array('autogroups_id', 0, false, \phpbb\request\request_interface::REQUEST, $autogroups_id),
		));
		$this->request->method('is_set_post')->with('generalsubmit')->willReturn(false);

		if ($called_method)
		{
			$this->controller->expects(self::once())->method($called_method)->with($autogroups_id);
		}
		$this->controller->expects(self::exactly($display_count))->method('display_autogroups');

		$module = $this->run_module();

		self::assertSame('manage_autogroups', $module->tpl_name);
		self::assertSame($page_title, $module->page_title);
	}

	public function test_main_submits_general_options()
	{
		$this->set_request_action('');
		$this->request->expects(self::once())->method('is_set_post')->with('generalsubmit')->willReturn(true);
		$this->controller->expects(self::once())->method('submit_autogroups_options');
		$this->controller->expects(self::once())->method('display_autogroups');

		$this->run_module();
	}

	public function test_main_requests_delete_confirmation()
	{
		$_POST['cancel'] = true;
		$this->set_request_action('delete', 7);
		$this->request->method('is_set_post')->with('generalsubmit')->willReturn(false);
		$this->controller->expects(self::never())->method('delete_autogroup_rule');
		$this->controller->expects(self::once())->method('display_autogroups');
		$this->language->method('lang')->willReturnArgument(0);

		$this->run_module();
	}

	public function test_main_deletes_confirmed_rule()
	{
		global $db;

		$this->set_request_action('delete', 7, true);
		$this->request->method('is_set_post')->with('generalsubmit')->willReturn(false);
		$this->language->method('lang')->willReturnCallback(function ($key) {
			return $key;
		});
		$db->expects(self::once())->method('sql_query');
		$this->controller->expects(self::once())->method('delete_autogroup_rule')->with(7);
		$this->controller->expects(self::once())->method('display_autogroups');

		$this->run_module();
	}

	private function set_request_action($action, $autogroups_id = 0, $confirm = false)
	{
		$this->request->method('variable')->willReturnCallback(function ($name, $default) use ($action, $autogroups_id, $confirm) {
			$values = array(
				'action' => $action,
				'autogroups_id' => $autogroups_id,
				'confirm' => $confirm ? 'YES' : '',
				'confirm_uid' => 2,
				'sess' => 'session-id',
				'confirm_key' => 'confirm-key',
			);
			return array_key_exists($name, $values) ? $values[$name] : $default;
		});
	}

	private function run_module()
	{
		$this->language->expects(self::once())->method('add_lang')->with('autogroups_acp', 'phpbb/autogroups');
		$this->controller->expects(self::once())->method('set_page_url')->with('index.php');

		$module = new \phpbb\autogroups\acp\autogroups_module();
		$module->u_action = 'index.php';
		$module->main(0, 'manage');

		return $module;
	}
}
