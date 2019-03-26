<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
* (Thanks/credit to nickvergessen for designing these tests)
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\tests\event;

class listener_test extends \phpbb_test_case
{
	/** @var \phpbb\autogroups\event\listener */
	protected $listener;

	/** @var \PHPUnit_Framework_MockObject_MockObject|\phpbb\autogroups\conditions\manager */
	protected $manager;

	/**
	* Create our event listener
	*/
	protected function set_listener()
	{
		$this->manager = $this->getMockBuilder('\phpbb\autogroups\conditions\manager')
			->disableOriginalConstructor()
			->getMock();

		$this->listener = new \phpbb\autogroups\event\listener($this->manager);
	}

	/**
	* Test the event listener is constructed correctly
	*/
	public function test_construct()
	{
		$this->set_listener();
		$this->assertInstanceOf('\Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->listener);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.delete_group_after',
			'core.user_setup',
			'core.submit_post_end',
			'core.delete_posts_after',
			'core.approve_posts_after',
			'core.mcp_warn_post_after',
			'core.mcp_warn_user_after',
			'core.session_create_after',
			'core.user_add_after',
		), array_keys(\phpbb\autogroups\event\listener::getSubscribedEvents()));
	}

	/**
	 * Test the delete_group_rules event
	 */
	public function test_delete_group_rules()
	{
		$this->set_listener();

		// Mock the group_id var
		$group_id = array();

		// Test the purge_autogroups_group() method is called once
		// with group_id event data as its argument.
		$this->manager->expects($this->once())
			->method('purge_autogroups_group')
			->with($group_id);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.delete_group_after', array($this->listener, 'delete_group_rules'));

		$event_data = array('group_id');
		$event = new \phpbb\event\data(compact($event_data));
		$dispatcher->dispatch('core.delete_group_after', $event);
	}

	/**
	* Data set for test_load_language_on_setup
	*
	* @return array Array of test data
	*/
	public function load_language_on_setup_data()
	{
		return array(
			array(
				array(),
				array(
					array(
						'ext_name' => 'phpbb/autogroups',
						'lang_set' => 'autogroups_common',
					),
				),
			),
			array(
				array(
					array(
						'ext_name' => 'foo/bar',
						'lang_set' => 'foobar',
					),
				),
				array(
					array(
						'ext_name' => 'foo/bar',
						'lang_set' => 'foobar',
					),
					array(
						'ext_name' => 'phpbb/autogroups',
						'lang_set' => 'autogroups_common',
					),
				),
			),
		);
	}

	/**
	* Test the load_language_on_setup event
	*
	* @dataProvider load_language_on_setup_data
	*/
	public function test_load_language_on_setup($lang_set_ext, $expected_contains)
	{
		$this->set_listener();

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.user_setup', array($this->listener, 'load_language_on_setup'));

		$event_data = array('lang_set_ext');
		$event = new \phpbb\event\data(compact($event_data));
		$dispatcher->dispatch('core.user_setup', $event);

		$lang_set_ext = $event->get_data_filtered($event_data);
		$lang_set_ext = $lang_set_ext['lang_set_ext'];

		foreach ($expected_contains as $expected)
		{
			$this->assertContains($expected, $lang_set_ext);
		}
	}

	/**
	 * Data set for test_autogroup_listeners
	 *
	 * @return array Array of test data
	 */
	public function autogroup_listeners_data()
	{
		return array(
			array(
				'phpbb.autogroups.type.posts',
				'submit_post_check',
				'core.submit_post_end',
				'data',
				array('poster_id' => '$poster_ids'),
				array('users' => '$poster_ids'),
			),
			array(
				'phpbb.autogroups.type.posts',
				'delete_post_check',
				'core.delete_posts_after',
				'poster_ids',
				'$poster_ids',
				array(
					'action' => 'delete',
					'users' => '$poster_ids',
				),
			),
			array(
				'phpbb.autogroups.type.posts',
				'approve_post_check',
				'core.approve_posts_after',
				'post_info',
				array(
					array('post_id' => 1, 'poster_id' => 100),
					array('post_id' => 2, 'poster_id' => 200),
					array('post_id' => 3, 'poster_id' => 300),
				),
				array('users' => array(100, 200, 300)),
			),
			array(
				'phpbb.autogroups.type.warnings',
				'add_warning_check',
				'core.mcp_warn_post_after',
				'user_row',
				array('user_id' => '$poster_ids'),
				array('users' => '$poster_ids'),
			),
			array(
				'phpbb.autogroups.type.warnings',
				'add_warning_check',
				'core.mcp_warn_user_after',
				'user_row',
				array('user_id' => '$poster_ids'),
				array('users' => '$poster_ids'),
			),
			array(
				'phpbb.autogroups.type.lastvisit',
				'last_visit_check',
				'core.session_create_after',
				'session_data',
				array('session_user_id' => '$user_id'),
				array('users' => '$user_id'),
			),
			array(
				'phpbb.autogroups.type.membership',
				'membership_check',
				'core.user_add_after',
				'user_id',
				array('user_id' => 100),
				array('users' => array('user_id' => 100)),
			),
		);
	}

	/**
	 * Test all the autogroup listener events that run check_condition()
	 *
	 * @dataProvider autogroup_listeners_data
	 */
	public function test_autogroup_listeners($type_class, $event_method, $event_listener, $event_var, $event_data, $options)
	{
		$this->set_listener();

		// Mock the event var with test event data
		${$event_var} = $event_data;

		// Test the check_condition() method is called once
		// with expected arguments.
		$this->manager->expects($this->once())
			->method('check_condition')
			->with($type_class, $options);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener($event_listener, array($this->listener, $event_method));

		$event_data = array($event_var);
		$event = new \phpbb\event\data(compact($event_data));
		$dispatcher->dispatch($event_listener, $event);
	}
}
