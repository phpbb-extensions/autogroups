<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ACP_AUTOGROUPS_MANAGE'			=> 'Manage Auto Groups',
	'ACP_AUTOGROUPS_MANAGE_EXPLAIN'	=> 'Using this form you can add, edit, view and delete Auto Group rules',
	'ACP_AUTOGROUPS_ADD'			=> 'Add an Auto Group rule',
	'ACP_AUTOGROUPS_EDIT'			=> 'Edit the Auto Group rule',

	'ACP_AUTOGROUPS_GROUP_NAME'				=> 'Group name',
	'ACP_AUTOGROUPS_GROUP_NAME_EXPLAIN'		=> '',
	'ACP_AUTOGROUPS_CONDITION_NAME'			=> 'Condition',
	'ACP_AUTOGROUPS_CONDITION_NAME_EXPLAIN'	=> '',
	'ACP_AUTOGROUPS_MIN_VALUE'				=> 'Min value',
	'ACP_AUTOGROUPS_MIN_VALUE_EXPLAIN'		=> '',
	'ACP_AUTOGROUPS_MAX_VALUE'				=> 'Max value',
	'ACP_AUTOGROUPS_MAX_VALUE_EXPLAIN'		=> '',
	'ACP_AUTOGROUPS_DEFAULT'				=> 'Default',
	'ACP_AUTOGROUPS_DEFAULT_EXPLAIN'		=> '',
	'ACP_AUTOGROUPS_NOTIFY'					=> 'Notify user',
	'ACP_AUTOGROUPS_NOTIFY_EXPLAIN'			=> '',

	'ACP_AUTOGROUPS_CREATE_RULE'	=> 'Add new rule',
	'ACP_AUTOGROUPS_EMPTY'			=> 'There are not Auto Group rules',

	// Conditions
	'AUTOGROUPS_TYPE_POSTS'	=> 'Posts',
));
