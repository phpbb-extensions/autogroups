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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
* Auto Groups service class
*/
class manager
{
	/** @var array */
	protected $autogroups_conditions;

	/** @var ContainerInterface */
	protected $phpbb_container;

	/**
	* Constructor
	*
	* @param array              $autogroups_conditions      Array with auto groups conditions
	* @param ContainerInterface $phpbb_container            Service container interface
	*
	* @return \phpbb\autogroups\conditions\manager
	* @access public
	*/
	public function __construct($autogroups_conditions, ContainerInterface $phpbb_container)
	{
		$this->autogroups_conditions = $autogroups_conditions;
		$this->phpbb_container = $phpbb_container;
	}

	/**
	* Check auto groups conditions and execute them
	*
	* @return
	* @access public
	*/
	public function check_conditions()
	{
		foreach ($this->autogroups_conditions as $autogroups_condition)
		{
			$this->check_condition($autogroups_condition);
		}
	}

	/**
	* Check auto groups condition and execute it
	*
	* @param string     $condtion_name      Name of the condition
	*
	* @return
	* @access public
	*/
	public function check_condition($condition_name)
	{
		$condition = $this->phpbb_container->get($condition_name);

		$condition->check();
	}
}
