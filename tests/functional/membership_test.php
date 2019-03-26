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
	/**
	 * Test the auto groups membership type
	 */
	public function test_autogroups_memberships()
	{
		$test_data = array(
			'type' => 'membership',
			'group_name' => 'test-membership',
			'min' => 0,
			'max' => 10,
		);

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		$this->assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($test_data['type'], $group_id, $test_data['min'], $test_data['max']);
		$this->assertNotNull($autogroup_id, 'Failed to create an auto group rule set.');

		// Run the cron job for a user with 2 days of membership, should add the user to the group
		$this->update_user_regdate(2, 2)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->assertInGroup(2, $test_data['group_name']);

		// Run the cron job for a user with 20 days of membership, should remove the user from the group
		$this->update_user_regdate(2, 20)->reset_cron();
		self::request('GET', "cron.php?cron_type=cron.task.autogroups_check&sid={$this->sid}", array(), false);
		$this->assertNotInGroup(2, $test_data['group_name']);

		// Register a new user, should add the user to the group
		$this->disable_captcha();
		$this->logout();
		$this->add_lang('ucp');
		$crawler = self::request('GET', 'ucp.php?mode=register');
		$form = $crawler->selectButton('I agree to these terms')->form();
		$crawler = self::submit($form);
		$form = $crawler->selectButton('Submit')->form(array(
			'username'			=> 'user-ag-test',
			'email'				=> 'user-ag-test@phpbb.com',
			'new_password'		=> 'user-ag-testuser-reg-test',
			'password_confirm'	=> 'user-ag-testuser-reg-test',
		));
		$form['tz']->select('Europe/Berlin');
		$crawler = self::submit($form);
		$this->assertContainsLang('ACCOUNT_ADDED', $crawler->filter('#message')->text());
		$new_user_id = $this->get_new_user_id();
		$this->assertGreaterThan(40, $new_user_id); // lets just make sure this is a newer user
		$this->login();
		$this->assertInGroup($new_user_id, $test_data['group_name']);
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
}
