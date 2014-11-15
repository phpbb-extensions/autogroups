<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\autogroups\conditions\manager */
	protected $manager;

	/** @var \phpbb\user */
	protected $user;

	/**
	* Constructor
	*
	* @param \phpbb\autogroups\conditions\manager $manager     Auto groups condition manager object
	* @param \phpbb\user                          $user        User object
	* @return \phpbb\autogroups\event\listener
	* @access public
	*/
	public function __construct(\phpbb\autogroups\conditions\manager $manager, \phpbb\user $user)
	{
		$this->manager = $manager;
		$this->user = $user;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.submit_post_end'		=> 'check_posts_submit',
		);
	}

	/**
	* Check user's post count after submitting a post for auto groups
	*
	* @return null
	* @access public
	*/
	public function check_posts_submit()
	{
		$this->manager
			->set_users($this->user->data['user_id'])
			->check_condition('phpbb.autogroups.type.posts')
		;
	}

	}
}
