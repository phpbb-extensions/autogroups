<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\conditions;

/**
* Auto Groups service class
*/
class manager
{
	/** @var array */
	protected $autogroups_conditions;

	/**
	* Constructor
	*
	* @param array               $autogroups_conditions         Array with auto groups conditions
	*
	* @return \phpbb\autogroups\conditions\manager
	* @access public
	*/
	public function __construct($autogroups_conditions)
	{
		$this->autogroups_conditions = $autogroups_conditions;
	}
}
