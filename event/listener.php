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
			'core.submit_post_end'	=> 'check_posts',
		);
	}

	/**
	* Check user's post count after submitting a post
	*
	* @return null
	* @access public
	*/
	public function check_posts()
	{
		// User's post count won't be incremented until the next session
		// so we need to temporarily increment it now for our manager
		$this->user->data['user_posts']++;

		// Perform the auto group check for posts
		$this->manager->check_condition('phpbb.autogroups.type.posts');

		// Set the user's post count back to its original value
		$this->user->data['user_posts']--;
	}
}
