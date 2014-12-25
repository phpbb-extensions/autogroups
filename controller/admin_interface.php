<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\controller;

/**
* Interface for our admin controller
*
* This describes all of the methods we'll use for the admin front-end of this extension
*/
interface admin_interface
{
	/**
	* Display the auto group rules
	*
	* @return null
	* @access public
	*/
	public function display_autogroups();

	/**
	* Add an auto group rule
	*
	* @return null
	* @access public
	*/
	public function add_autogroup_rule();

	/**
	* Edit an auto group rule
	*
	* @param int $autogroups_id The auto groups identifier to edit
	* @return null
	* @access public
	*/
	public function edit_autogroup_rule($autogroups_id);

	/**
	* Delete an auto group rule
	*
	* @param int $autogroups_id The auto groups identifier to delete
	* @return null
	* @access public
	*/
	public function delete_autogroup_rule($autogroups_id);

	/**
	* Set page url
	*
	* @param string $u_action Custom form action
	* @return null
	* @access public
	*/
	public function set_page_url($u_action);
}
