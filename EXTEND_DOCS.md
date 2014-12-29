# Extending Auto Groups

Auto Groups can easily be extended by experienced extension developers. Add the Auto Group functionality to an existing
extension or write a simple extension as an add-on that adds more Auto Group possibilities not available in the base package.

The Auto Groups extension works by comparing a component of user data against pre-defined minimum / maximum values
set by the Admin in the ACP, and if a user's data is within the defined range for a specified group, the user will
automatically be added to the group.

The base Auto Group extension provides this functionality for user post counts, membership days and warning counts. To
add new user data types, such as those added by other extensions (i.e.: Points/Reputation extensions, PayPal Donors,
etc.) you need to extend the Auto Groups base class and trigger your Auto Group class in the appropriate manner for
your extension.

### Auto Group Condition Type Classes

Adding your own Auto Groups starts with extending the base class in `autogroups/conditions/type/`:

```php
class example extends \phpbb\autogroups\conditions\type\base
{
}
```

Add a method that defines the name of your Auto Group type. It must be prefixed by your unique vendor and extension name:

```php
public function get_condition_type()
{
	return 'vendor.extension.autogroups.type.example';
}
```

Add a method that defines the name of the user data field it will be checking the value of:

```php
public function get_condition_field()
{
	return 'example_data';
}
```

Add a method that defines a language var stored in a language file with your extension. It will be used to display
the name of this Auto Group type (prefix the language file with `info_acp_` so that it will be auto-loaded
in the ACP):

```php
public function get_condition_type_name()
{
	return $this->user->lang('VENDOR_EXTENSION_AUTOGROUPS_TYPE_EXAMPLE');
}
```

The most important method is the one that will get all the users and their data to check. Review our posts, membership
and warning classes to see some examples. For example, in posts, we pass it an array of user_ids (via the `$options`
parameter) that is already available when the method is called. In our memberships class, we use a more specific SQL
query to get an array of users who are eligible to be added to the group and any users already in the group (in case
they need to be removed).

This method must output an array of users and their data where the array keys are the user ids, and the array values
hold the user's data to be tested. You can use any means to get whatever data you need, so long as you return the
expected user data array. For example:

```php
/*
* Return an array of user data
*    eg: 1 => array('user_id' => 1, 'example_data' => 'foobar')
*/
public function get_users_for_condition($options = array())
{
	// The user data this condition needs to check
	$condition_data = array(
		$this->get_condition_field(),
	);

	$user_data = array();

	// This query simply grabs all users and their example_data field
	$sql = 'SELECT user_id, ' . implode(', ', $condition_data) . '
		FROM ' . USERS_TABLE;
	$result = $this->db->sql_query_limit($sql, 1);

	while ($row = $this->db->sql_fetchrow($result))
	{
		$user_data[$row['user_id']] = $row;
	}
	$this->db->sql_freeresult($result);

	return $user_data;
}
```

You may optionally want to modify or override the `check()` method, either to perform additional filtering before
checking or to completely rewrite the method to better suit your particular needs. The `$options` array parameter can
provide variables and/or data needed for further processing in this method:

```php
public function check($user_row, $options = array())
{
	// Merge default options, overriden by any data provided when called
	$options = array_merge(array(
		'foo'	=> '',
	), $options);

	if ($options['foo'] == 'bar')
	{
		// do some pre-check actions here
	}

	// Now perform the base check() method
	parent::check($user_row, $options);
}
```

### Service Definitions

Your extension's `services.yml` file should contain a service definition for your Auto Group type class(es). The service
name should be an exact match with the `get_condition_type()` method:

```yml
vendor.extension.autogroups.type.example:			# change this for your extension
	class: vendor\extension\conditions\type\example # change this for your extension
	scope: prototype
	arguments:
		- @service_container
		- @config
		- @dbal.conn
		- @user
		- %core.table_prefix%autogroups_rules
		- %core.table_prefix%autogroups_types
		- %core.root_path%
		- %core.php_ext%
	tags:
		- { name: phpbb.autogroups.type }
```

### Calling Auto Group Type Classes

There are three possible ways to call your Auto Groups classes:

- **Events:** From an event listener using any of phpBB's events will call your class from specific points during phpBB's
execution. Our posts and warnings classes are triggered using events. The Auto Groups manager class must be made
available in your listener by injecting it as an optional service (and setting it in a constructor):

```yml
vendor.extension.listener:
	class: vendor\extension\event\listener
	arguments:
		- @?phpbb.autogroups.manager   # The ? defines this as an optional dependency
	tags:
		- { name: event.listener }
```

```php
// The autogroups_manager argument must be set last and = to null (because it is optional)
public function __construct(\phpbb\autogroups\conditions\manager $autogroup_manager = null)
{
	$this->autogroup_manager = $autogroup_manager;
}
```

- **In Code:** Call your class directly in your extension code somewhere. All that is required is to make the Auto
Groups manager class available in your extension (similar to way it is made available to the listener class).

- **Cron:** Some Auto Group types are best checked via automated intervals, such as once a day. Our membership class is
is called using phpBB's cron methods. The Auto Groups extension will automatically check all types available once
daily. If you need more frequent intervals you can create your own cron class (view our cron class as an example).

Note: because of the use of automated cron checks, it is very important that all Auto Group type classes have a default
set of data to check defined by the `$options` argument in the `get_users_for_condition()` method. That is to say, the
`get_users_for_condition()` method must always output a valid user data array whether the `$options` array has data 
or is empty.

Calling an Auto Group type class is fairly simple (this applies the same to events, in code or cron):

```php
// This conditional must be used to ensure calls only go out if Auto Groups is installed/enabled
if ($this->autogroup_manager !== null)
{
	// This calls our class (with no optional arguments)
	$this->autogroup_manager->check_condition('vendor.extension.autogroups.type.example');

	// This calls our class and sends it some $options data
	$this->autogroup_manager->check_condition('vendor.extension.autogroups.type.example', array(
		'foo'	=> 'bar',
		'users'	=> $user_id_ary,
	));
}
```

### Important Compatibility Precautions

Auto Groups must be installed and enabled for your extension to use it, obviously. But special care must be taken to
ensure it is impossible to run your Auto Groups code if the main Auto Groups extension has been disabled for any reason.
Making the Auto Groups manager class an optional service as described above is the most important step.

The `ext.php` class can be used in your extension as another safety measure to prevent the installation/enabling
of your extension if Auto Groups is unavailable.

```php
public function is_enableable()
{
	$ext_manager = $this->container->get('ext.manager');

	return $ext_manager->is_enabled('phpbb/autogroups');
}
```

The above code is only needed if your extension is an add-on to Auto Groups. You may want to skip this step if your 
extension does a lot of other stuff and is just trying to take advantage of Auto Groups if it is available.

### ACP

TODO

### Resources

TODO (links to extended scripts)
