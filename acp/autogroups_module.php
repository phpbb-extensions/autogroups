<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\acp;

class autogroups_module
{
	public $u_action;

	function main($id, $mode)
	{
		global $phpbb_container, $request, $user;

		// Add the auto groups ACP lang file
		$user->add_lang_ext('phpbb/autogroups', 'autogroups_acp');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('phpbb.autogroups.admin_controller');

		// Requests
		$action = $request->variable('action', '');
		$autogroups_id = $request->variable('autogroups_id', 0);

		// Make the $u_action url available in the admin controller
		$admin_controller->set_page_url($this->u_action);

		// Load a template from adm/style for our ACP auto groups
		$this->tpl_name = 'manage_autogroups';

		// Set the page title for our ACP auto groups
		$this->page_title = $user->lang('ACP_AUTOGROUPS_MANAGE');

		if ($request->is_set_post('generalsubmit'))
		{
			$admin_controller->set_general_options();
		}

		// Perform any actions submitted by the user
		switch ($action)
		{
			case 'add':
				// Set the page title for our ACP auto groups
				$this->page_title = $user->lang('ACP_AUTOGROUPS_ADD');

				// Load the add auto group handle in the admin controller
				$admin_controller->add_edit_autogroup_rule();

				// Return to stop execution of this script
				return;
			break;

			case 'edit':
				// Set the page title for our ACP auto groups
				$this->page_title = $user->lang('ACP_AUTOGROUPS_EDIT');

				// Load the edit auto group handle in the admin controller
				$admin_controller->add_edit_autogroup_rule($autogroups_id);

				// Return to stop execution of this script
				return;
			break;

			case 'sync':
				// Resync applies an auto group check against all users
				$admin_controller->resync_autogroup_rule($autogroups_id);
			break;

			case 'delete':
				// Use a confirm box routine when deleting an auto group rule
				if (confirm_box(true))
				{
					// Delete auto group rule on confirmation from the user
					$admin_controller->delete_autogroup_rule($autogroups_id);
				}
				else
				{
					// Request confirmation from the user to delete the auto group rule
					confirm_box(false, $user->lang('ACP_AUTOGROUPS_DELETE_CONFIRM'), build_hidden_fields(array(
						'autogroups_id'	=> $autogroups_id,
						'mode'			=> $mode,
						'action'		=> $action,
					)));
				}
			break;
		}

		// Display auto group rules
		$admin_controller->display_autogroups();
	}
}
