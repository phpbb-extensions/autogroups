<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2019 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'AUTOGROUPS_NOT_ENABLEABLE'	=> 'Nie można było włączyć automatycznych grup. Nie zostały spełnione minimalne wymagania phpBB 3.2.0 i/lub PHP 5.5.0.',
));
