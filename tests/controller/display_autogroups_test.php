<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\controller;

class display_autogroups_test extends admin_controller_base
{
	/**
	 * Test the display_autogroups() method
	 */
	public function test_display_autogroups()
	{
		// Assert the admin_controller is instantiated
		self::assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Mocked manager should output the expected values
		// from get_condition_lang() at the expected times.
		$manager_expectations = [
			['phpbb.autogroups.type.sample1', 'phpbb.autogroups.type.sample1'],
			['phpbb.autogroups.type.sample2', 'phpbb.autogroups.type.sample2']
		];
		$this->manager->expects(self::exactly(2))
			->method('get_condition_lang')
			->willReturnCallback(function($arg) use (&$manager_expectations) {
				$expectation = array_shift($manager_expectations);
				self::assertEquals($expectation[0], $arg);
				return $expectation[1];
			});

		// Set expectations for the assign_block_vars template values
		$template_invocation = 0;
		$this->template->expects(self::exactly(4))
			->method('assign_block_vars')
			->willReturnCallback(function($block, $vars) use (&$template_invocation) {
				switch ($template_invocation) {
					case 0:
						self::assertEquals('autogroups', $block);
						self::assertEquals(array(
							'GROUP_NAME'		=> 'GROUP1',
							'CONDITION_NAME'	=> 'phpbb.autogroups.type.sample1',
							'MIN_VALUE'			=> '0',
							'MAX_VALUE'			=> '20',
							'S_DEFAULT'			=> '1',
							'S_NOTIFY'			=> '0',
							'EXCLUDED_GROUPS'	=> '',
							'U_EDIT'			=> 'index.php&amp;action=edit&amp;autogroups_id=1',
							'U_DELETE'			=> 'index.php&amp;action=delete&amp;autogroups_id=1',
							'U_SYNC'			=> 'index.php&amp;action=sync&amp;autogroups_id=1&amp;hash=' . generate_link_hash('sync' . 1),
						), $vars);
						break;
					case 1:
						self::assertEquals('autogroups', $block);
						self::assertEquals(array(
							'GROUP_NAME'		=> 'GROUP2',
							'CONDITION_NAME'	=> 'phpbb.autogroups.type.sample2',
							'MIN_VALUE'			=> '30',
							'MAX_VALUE'			=> '50',
							'S_DEFAULT'			=> '0',
							'S_NOTIFY'			=> '0',
							'EXCLUDED_GROUPS'	=> '',
							'U_EDIT'			=> 'index.php&amp;action=edit&amp;autogroups_id=2',
							'U_DELETE'			=> 'index.php&amp;action=delete&amp;autogroups_id=2',
							'U_SYNC'			=> 'index.php&amp;action=sync&amp;autogroups_id=2&amp;hash=' . generate_link_hash('sync' . 2),
						), $vars);
						break;
					case 2:
						self::assertEquals('groups', $block);
						self::assertEquals(array(
							'GROUP_ID'		=> 1,
							'GROUP_NAME'	=> 'GROUP1',
							'S_SELECTED'	=> false,
						), $vars);
						break;
					case 3:
						self::assertEquals('groups', $block);
						self::assertEquals(array(
							'GROUP_ID'		=> 2,
							'GROUP_NAME'	=> 'GROUP2',
							'S_SELECTED'	=> true,
						), $vars);
						break;
				}
				$template_invocation++;
			});

		// Set expectations for the assign_vars template values
		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'U_ACTION'				=> 'index.php',
				'U_ADD_AUTOGROUP_RULE'	=> 'index.php&amp;action=add',
			));

		// Set a u_index test value
		$this->admin_controller->set_page_url('index.php');

		// Call the display_autogroups() method
		$this->admin_controller->display_autogroups();
	}
}
