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

require_once __DIR__ . '/../../../../../includes/functions.php';

class display_autogroups_test extends admin_controller_base
{
	/**
	 * Test the display_autogroups() method
	 */
	public function test_display_autogroups()
	{
		// Assert the admin_controller is instantiated
		$this->assertInstanceOf('\phpbb\autogroups\controller\admin_controller', $this->admin_controller);

		// Mocked manager should output the expected values
		// from get_condition_lang() at the expected times.
		$this->manager->expects($this->at(0))
			->method('get_condition_lang')
			->with('phpbb.autogroups.type.sample1')
			->will($this->returnValue('phpbb.autogroups.type.sample1'));
		$this->manager->expects($this->at(1))
			->method('get_condition_lang')
			->with('phpbb.autogroups.type.sample2')
			->will($this->returnValue('phpbb.autogroups.type.sample2'));

		// Set expectations for the assign_block_vars template values
		$this->template->expects($this->exactly(4))
			->method('assign_block_vars')
			->withConsecutive(
				array('autogroups', array(
					'GROUP_NAME'		=> 'GROUP1',
					'CONDITION_NAME'	=> 'phpbb.autogroups.type.sample1',
					'MIN_VALUE'			=> 0,
					'MAX_VALUE'			=> 20,
					'S_DEFAULT'			=> 1,
					'S_NOTIFY'			=> 0,
					'U_EDIT'			=> 'index.php&amp;action=edit&amp;autogroups_id=1',
					'U_DELETE'			=> 'index.php&amp;action=delete&amp;autogroups_id=1',
					'U_SYNC'			=> 'index.php&amp;action=sync&amp;autogroups_id=1&amp;hash=' . generate_link_hash('sync' . 1),
				)),
				array('autogroups', array(
					'GROUP_NAME'		=> 'GROUP2',
					'CONDITION_NAME'	=> 'phpbb.autogroups.type.sample2',
					'MIN_VALUE'			=> 30,
					'MAX_VALUE'			=> 50,
					'S_DEFAULT'			=> 0,
					'S_NOTIFY'			=> 0,
					'U_EDIT'			=> 'index.php&amp;action=edit&amp;autogroups_id=2',
					'U_DELETE'			=> 'index.php&amp;action=delete&amp;autogroups_id=2',
					'U_SYNC'			=> 'index.php&amp;action=sync&amp;autogroups_id=2&amp;hash=' . generate_link_hash('sync' . 2),
				)),
				array('groups', array(
					'GROUP_ID'		=> 1,
					'GROUP_NAME'	=> 'G_GROUP1',
					'S_SELECTED'	=> false,
				)),
				array('groups', array(
					'GROUP_ID'		=> 2,
					'GROUP_NAME'	=> 'GROUP2',
					'S_SELECTED'	=> true,
				))
			)
		;

		// Set expectations for the assign_vars template values
		$this->template->expects($this->once())
			->method('assign_vars')
			->with(array(
				'U_ACTION'				=> 'index.php',
				'U_ADD_AUTOGROUP_RULE'	=> 'index.php&amp;action=add',
			))
		;

		// Set a u_index test value
		$this->admin_controller->set_page_url('index.php');

		// Call the display_autogroups() method
		$this->admin_controller->display_autogroups();
	}
}
