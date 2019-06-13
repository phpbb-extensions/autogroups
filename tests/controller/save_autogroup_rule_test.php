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
	public function save_autogroup_rule_data()
	{
		return array(
			array(1, 0, 20, true, false, false, true, false),
			array(2, 30, 50, false, false, true, false, true),
			array(0, 0, 0, false, false, false, false, false),
			array('', 0, 0, false, false, false, false, false),
			array('foo', 0, 0, false, false, false, false, false),
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
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Return false from is_set_post()
		$this->request->expects($this->any())
			->method('is_set_post')
			->will($this->returnValue(false));

		// Return condition names/ids from get_autogroups_type_ids()
		$this->manager->expects($this->any())
			->method('get_autogroups_type_ids')
			->will($this->returnValue(array(
				'phpbb.autogroups.type.sample1' => 1,
				'phpbb.autogroups.type.sample2' => 2,
			)));

		// Return empty strings for get_condition_lang()
		$this->manager->expects($this->any())
			->method('get_condition_lang')
			->will($this->returnValue(''));

		// Set expectations for the assign_block_vars template values
		$this->template->expects($this->exactly(3))
			->method('assign_block_vars')
			->withConsecutive(
				array('groups', array(
					'GROUP_ID'		=> 2,
					'GROUP_NAME'	=> 'GROUP2',
					'S_SELECTED'	=> $group_selected,
				)),
				array('conditions', array(
					'CONDITION_ID'		=> 1,
					'CONDITION_NAME'	=> '',
					'S_SELECTED'		=> $cond1_selected,
				)),
				array('conditions', array(
					'CONDITION_ID'		=> 2,
					'CONDITION_NAME'	=> '',
					'S_SELECTED'		=> $cond2_selected,
				))
			)
		;

		// Set expectations for the assign_vars template values
		$this->template->expects($this->once())
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
