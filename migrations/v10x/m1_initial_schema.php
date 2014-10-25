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
* Migration stage 1: Initial schema
*/
class m1_initial_schema extends \phpbb\db\migration\migration
{
	/**
	* Add the auto groups table schema to the database:
	*	auto groups:
	*		autogroups_id
	*		autogroups_type_id
	*		autogroups_min_value
	*		autogroups_min_operator
	*		autogroups_max_value
	*		autogroups_max_operator
	*		autogroups_group_id
	*		autogroups_default
	*
	* @return array Array of table schema
	* @access public
	*/
	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'autogroups_rules'	=> array(
					'COLUMNS'	=> array(
						'autogroups_id'					=> array('UINT', null, 'auto_increment'),
						'autogroups_type_id'			=> array('USINT', 0),
						'autogroups_min_value'			=> array('INT:11', ''),
						'autogroups_max_value'			=> array('INT:11', ''),
						'autogroups_group_id'			=> array('UINT', 0),
						'autogroups_default'			=> array('BOOL', 0),
					),
					'PRIMARY_KEY'	=> 'autogroups_id',
				),
				$this->table_prefix . 'autogroups_condition_types'	=> array(
					'COLUMNS'			=> array(
						'autogroups_type_id'	=> array('USINT', null, 'auto_increment'),
						'autogroups_type_name'	=> array('VCHAR:255', ''),
					),
					'PRIMARY_KEY'		=> array('autogroups_type_id'),
					'KEYS'				=> array(
						'type'			=> array('UNIQUE', array('autogroups_type_name')),
					),
				),
			),
		);
	}

	/**
	* Drop the auto groups table schema from the database
	*
	* @return array Array of table schema
	* @access public
	*/
	public function revert_schema()
	{
		return array(
			'drop_tables'	=> array(
				$this->table_prefix . 'autogroups_rules',
				$this->table_prefix . 'autogroups_condition_types',
			),
		);
	}
}
