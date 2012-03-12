# RAMP

This is a content deployment mechanism designed to enable the transfer of content between WordPress installs.   
**Please see the included documentation in the WordPress Admin for detailed usage documentation.**


## Requirements

- Requires WordPress 3.1+
- Requires PHP 5.2+

## Compatibility

- If you are using RAMP with Carrington Build, Carrington Build 1.1 or later is required


## File Access for protected servers

If dealing with servers that have 401 Authentication it requires some "white listing" via the `.htaccess` file (add extra file extensions to the `FilesMatch` declaration as needed):

	# Standard HTTP Auth
	AuthType Basic
	AuthName "My Protected Server"
	AuthUserFile /path/to/.htpasswd
	Require valid-user
	# additions for RAMP
	SetEnvIf Request_URI "(/wp-content/uploads/)" allow
	SetEnvIf Request_URI "(xmlrpc.php|async-upload\.php|wp-cron\.php)$" allow
	Order allow,deny
	Allow from env=allow
	Satisfy any


## Debug Helpers

- `RAMP_DEBUG`: set this constant to true to reveal the TESTS menu item
	- "Sample Data" tab: show a sample post gathering object in raw or serialized form
	- "Test Comms" tab: test communications between servers
		- "hello": simple hello to remote server. Remote server should respond kindly.
		- "send post": send a post to the remote server. Can be any valid DRAFT or PUBLISHED post. You can probably send a revision, but I won't guarantee what'll happen to it on the other end.
			- only post data is inserted at this time. Taxonomies and authors are not (post could be abandoned on the other end if user is not present).
- `RAMP_DEBUG_DUMP_COMMS_DATA`: set this constant to true to save comms data to a unix temp directory
	- `cf-data-sent.txt`: data being sent by "local" server
	- `cf-data-received.txt`: data being received by "remote" server