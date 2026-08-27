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

class autogroups_info_test extends \phpbb_test_case
{
	public function test_module_info()
	{
		$info = new \phpbb\autogroups\acp\autogroups_info();

		self::assertSame(array(
			'filename' => '\phpbb\autogroups\acp\autogroups_module',
			'title' => 'ACP_AUTOGROUPS_MANAGE',
			'modes' => array(
				'manage' => array(
					'title' => 'ACP_AUTOGROUPS_MANAGE',
					'auth' => 'ext_phpbb/autogroups && acl_a_group',
					'cat' => array('ACP_GROUPS'),
				),
			),
		), $info->module());
	}
}
