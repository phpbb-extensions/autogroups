<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\migrations\v10x;

/**
* Migration stage 4: Initial module
*/
class m4_initial_module extends \phpbb\db\migration\migration
{
	/**
	* Add or update data in the database
	*
	* @return array Array of table data
	* @access public
	*/
	public function update_data()
	{
		return array(
			array('module.add', array(
				'acp', 'ACP_GROUPS', array(
					'module_basename'	=> '\phpbb\autogroups\acp\autogroups_module',
					'modes'				=> array('manage'),
				),
			)),
		);
	}
}
