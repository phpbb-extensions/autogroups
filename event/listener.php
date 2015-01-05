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

	/**
	* Constructor
	*
	* @param \phpbb\autogroups\conditions\manager $manager     Auto groups condition manager object
	* @return \phpbb\autogroups\event\listener
	* @access public
	*/
	public function __construct(\phpbb\autogroups\conditions\manager $manager)
	{
		$this->manager = $manager;
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
			'core.user_setup'			=> 'load_language_on_setup',

			// Auto Groups "Posts" listeners
			'core.submit_post_end'		=> 'submit_post_check',
			'core.delete_posts_after'	=> 'delete_post_check',
		);
	}

	/**
	* Load common language files during user setup
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbb/autogroups',
			'lang_set' => 'autogroups_common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	* Check user's post count after submitting a post for auto groups
	*
	* @return null
	* @access public
	*/
	public function submit_post_check()
	{
		$this->manager->check_condition('phpbb.autogroups.type.posts');
	}

	/**
	* Check user's post count after deleting a post for auto groups
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function delete_post_check($event)
	{
		$this->manager->check_condition('phpbb.autogroups.type.posts', array(
			'action'	=> 'delete',
			'users'		=> $event['poster_ids'],
		));
	}
}
