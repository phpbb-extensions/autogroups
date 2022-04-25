<?php
/**
 *
 * Auto Groups extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2015 phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbb\autogroups\tests\functional;

/**
 * @group functional
 */
class membership_test extends autogroups_base
{
	protected $test_data = array(
		'type'       => 'membership',
		'group_name' => 'test-membership',
		'min'        => 0,
		'max'        => 10,
	);

	protected $test_user = 'user-ag-test';

	/**
	 * Test the auto groups membership type
	 */
	public function test_autogroups_memberships()
	{
		// Create a new test group
		$group_id = $this->create_group($this->test_data['group_name']);
		self::assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($this->test_data['type'], $group_id, $this->test_data['min'], $this->test_data['max']);
		self::assertNotNull($autogroup_id, 'Failed to create an auto group rule set.');

		// Run the cron job for a user with 2 days of membership, should add the user to the group
		$this->update_user_regdate(2, 2)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->purge_cache();
		$this->assertInGroup(2, $this->test_data['group_name']);

		// Run the cron job for a user with 20 days of membership, should remove the user from the group
		$this->update_user_regdate(2, 20)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->purge_cache();
		$this->assertNotInGroup(2, $this->test_data['group_name']);
	}

	/**
	 * Register a new user, should add the user to the group
	 *
	 * @depends test_autogroups_memberships
	 */
	public function test_user_registration()
	{
		$this->disable_captcha();
		$this->logout();
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'ucp.php?mode=register');
		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> $this->test_user,
			'email'				=> $this->test_user . '@phpbb.com',
			'new_password'		=> $this->test_user . $this->test_user,
			'password_confirm'	=> $this->test_user . $this->test_user,
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);
		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
		$new_user_id = $this->get_new_user_id();
		self::assertGreaterThan(40, $new_user_id); // let's just make sure this is a newer user
		$this->assertInGroup($new_user_id, $this->test_data['group_name']);
		return $new_user_id;
	}

	/**
	 * Admin activate a new user, should add the user to the group
	 *
	 * @depends test_user_registration
	 * @param int $new_user_id
	 */
	public function test_user_activation($new_user_id)
	{
		// Make new user inactive and remove from the group
		$this->login();
		$this->admin_login();
		$this->add_lang('acp/users');
		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u=$new_user_id&sid={$this->sid}");
		$form = $crawler->filter('#user_quick_tools')->selectButton($this->lang('SUBMIT'))->form();
		$data = array('action' => 'active');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertContainsLang('USER_ADMIN_DEACTIVED', $crawler->filter('.successbox')->text());
		$this->remove_user_group($this->test_data['group_name'], $this->test_user);
		$this->assertNotInGroup($new_user_id, $this->test_data['group_name']);

		// Re-activate the user, should add the user to the group
		$crawler = self::request('GET', "adm/index.php?i=users&mode=overview&u=$new_user_id&sid={$this->sid}");
		$form = $crawler->filter('#user_quick_tools')->selectButton($this->lang('SUBMIT'))->form();
		$data = array('action' => 'active');
		$form->setValues($data);
		$crawler = self::submit($form);
		$this->assertContainsLang('USER_ADMIN_ACTIVATED', $crawler->filter('.successbox')->text());
		$this->assertInGroup($new_user_id, $this->test_data['group_name']);
		return $new_user_id;
	}

	/**
	 * User logs in and should add the user to the group
	 *
	 * @depends test_user_activation
	 * @param int $new_user_id
	 */
	public function test_user_login($new_user_id)
	{
		// Remove user from the group
		$this->login();
		$this->remove_user_group($this->test_data['group_name'], $this->test_user);
		$this->assertNotInGroup($new_user_id, $this->test_data['group_name']);
		$this->logout();

		// User logs in, and should be added to group
		$this->login($this->test_user);
		$this->assertInGroup($new_user_id, $this->test_data['group_name']);
	}

	/**
	 * Update a user's registration date
	 *
	 * @param int $user_id The user to update
	 * @param int $days    The number of days ago membership began
	 * @return $this
	 */
	protected function update_user_regdate($user_id, $days)
	{
		$time = strtotime("$days days ago");

		$sql = 'UPDATE phpbb_users
			SET user_regdate = ' . $time . '
			WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		$this->purge_cache();

		return $this;
	}

	/**
	 * Disable captcha for easy registration
	 */
	protected function disable_captcha()
	{
		$this->login();
		$this->admin_login();

		$crawler = self::request('GET', "adm/index.php?i=acp_board&mode=registration&sid={$this->sid}");
		$form = $crawler->selectButton('Submit')->form();
		$form['config[enable_confirm]']->setValue('0');
		$crawler = self::submit($form);

		$this->assertContainsLang('CONFIG_UPDATED', $crawler->filter('#main .successbox')->text());
	}

	/**
	 * Get user id of last/newest registered user
	 *
	 * @return int User ID
	 */
	protected function get_new_user_id()
	{
		$sql = 'SELECT user_id FROM phpbb_users ORDER BY user_id DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$user_id = (int) $this->db->sql_fetchfield('user_id');
		$this->db->sql_freeresult($result);

		return $user_id ?: 0;
	}

//	protected function remove_from_group($user_id, $group_id)
//	{
//		$this->db->sql_query('DELETE FROM ' . USER_GROUP_TABLE . '
//			WHERE user_id = ' . (int) $user_id . '
//			AND group_id = ' . (int) $group_id);
//	}
}
