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
class posts_test extends autogroups_base
{
	/**
	 * Test the auto groups posts type
	 *
	 * @return null
	 */
	public function test_autogroups_posts()
	{
		$test_data = array(
			'type' => 'posts',
			'group_name' => 'test-posters',
			'min' => 1,
			'max' => 2,
		);

		// Create a new test group
		$group_id = $this->create_group($test_data['group_name']);
		$this->assertNotNull($group_id, 'Failed to create a test group.');

		// Create a new auto group rule for the test group
		$autogroup_id = $this->create_autogroup_rule($test_data['type'], $group_id, $test_data['min'], $test_data['max']);
		$this->assertNotNull($autogroup_id, 'Failed to create an auto group rule set.');

		// Create a new topic/post (will be a 2nd post adding admin to the group)
		$post = $this->create_topic(2, 'Auto Groups Test Post', 'This is a test post for the Auto Groups extension.');
		$this->assertInGroup(2, $test_data['group_name']);

		// Create a reply post (will be a 3rd post, removing admin from the group)
		$this->create_post(2, $post['topic_id'], 'Re: Auto Groups Test Post', 'This is a test post posted by the testing framework.');
		$this->assertNotInGroup(2, $test_data['group_name']);
	}
}
