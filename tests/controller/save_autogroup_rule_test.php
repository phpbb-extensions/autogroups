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

class save_autogroup_rule_test extends admin_controller_base
{
	/**
	 * Data set for test_save_autogroup_rule
	 * This is expected data from the test fixture
	 *
	 * @return array Array of test data
	 */
	public static function save_autogroup_rule_data()
	{
		return array(
			array(0, 0, 0, false, false, false, false, false), // with no existing rule, zero out the options
			array(1, 0, 20, true, false, false, true, false), // load options for rule #1
			array(2, 30, 50, false, false, true, false, true), // load options for rule #2
		);
	}

	/**
	 * Test the save_autogroup_rule() method when NOT submitting data
	 *
	 * @dataProvider save_autogroup_rule_data
	 */
	public function test_save_autogroup_rule($id, $min, $max, $default, $notify, $group_selected, $cond1_selected, $cond2_selected)
	{
		// Assert the admin_controller is instantiated
		self::assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Return false from is_set_post()
		$this->request->expects(self::once())
			->method('is_set_post')
			->willReturn(false);

		// Return condition names/ids from get_autogroups_type_ids()
		$this->manager->expects(self::once())
			->method('get_autogroups_type_ids')
			->willReturn(array(
				'phpbb.autogroups.type.sample1' => 1,
				'phpbb.autogroups.type.sample2' => 2,
			));

		// Return empty strings for get_condition_lang()
		$this->manager->expects(self::atMost(2))
			->method('get_condition_lang')
			->willReturn('');

		// Set expectations for the assign_block_vars template values
		$invocation = 0;
		$this->template->expects(self::exactly(5))
			->method('assign_block_vars')
			->willReturnCallback(function($block, $vars) use (&$invocation, $group_selected, $cond1_selected, $cond2_selected) {
				switch ($invocation) {
					case 0:
						self::assertEquals('excluded_groups', $block);
						self::assertEquals(array(
							'GROUP_ID'		=> 1,
							'GROUP_NAME'	=> 'GROUP1',
							'S_SELECTED'	=> false,
						), $vars);
						break;
					case 1:
						self::assertEquals('excluded_groups', $block);
						self::assertEquals(array(
							'GROUP_ID'		=> 2,
							'GROUP_NAME'	=> 'GROUP2',
							'S_SELECTED'	=> false,
						), $vars);
						break;
					case 2:
						self::assertEquals('groups', $block);
						self::assertEquals(array(
							'GROUP_ID'		=> 2,
							'GROUP_NAME'	=> 'GROUP2',
							'S_SELECTED'	=> $group_selected,
						), $vars);
						break;
					case 3:
						self::assertEquals('conditions', $block);
						self::assertEquals(array(
							'CONDITION_ID'		=> 1,
							'CONDITION_NAME'	=> '',
							'S_SELECTED'		=> $cond1_selected,
						), $vars);
						break;
					case 4:
						self::assertEquals('conditions', $block);
						self::assertEquals(array(
							'CONDITION_ID'		=> 2,
							'CONDITION_NAME'	=> '',
							'S_SELECTED'		=> $cond2_selected,
						), $vars);
						break;
				}
				$invocation++;
			});

		// Set expectations for the assign_vars template values
		$this->template->expects(self::once())
			->method('assign_vars')
			->with(array(
				'S_ADD'			=> (bool) !$id,
				'S_EDIT'		=> (bool) $id,
				'MIN_VALUE'		=> $min,
				'MAX_VALUE'		=> $max,
				'S_DEFAULT'		=> $default,
				'S_NOTIFY'		=> $notify,
				'EXEMPT_GROUPS'	=> 'GROUP2',
				'U_FORM_ACTION'	=> 'index.php&amp;action=' . ($id ? 'edit' : 'add') . '&amp;autogroups_id=' . $id,
				'U_ACTION'		=> 'index.php',
				'U_BACK'		=> 'index.php',
			))
		;

		// Set a u_index test value
		$this->admin_controller->set_page_url('index.php');

		// Call the save_autogroup_rule() method
		$this->admin_controller->save_autogroup_rule($id);
	}
}
