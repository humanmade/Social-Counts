Social-Counts
=============

Adds the # of times a post has been shared on major social networks as post meta. It uses http://sharedcount.com/ and it's API.

**Please Note**: This plugin requires an account / API from http://sharecount.com/ in order to work.

## Installation

* Upload this directory to your '/wp-content/plugins/' directory, using your prefered method (ftp, sftp, etc.)
* Activate Social Counts from your plugins page in your WordPress Dashboard area.

## Usage
Place the following code in your WordPress loop(s) to display the total number of shares:

```php
<?php

if ( class_exists( 'HM_Social_Counts' ) ) {
	echo HM_Social_Counts()->get_total_shares();
}
```

###Here's a list of networks currently supported from the SharedCount API:

- StumbleUpon
- Reddit
- Facebook
- Delicious
- Google+
- Buzz
- Twitter
- Digg
- Pinterest
- LinkedIn

If you'd like to display the total count from a specific network you could add a network paramter to your template tag.

For example:

```php
<?php

if ( class_exists( 'HM_Social_Counts' ) ) {
	echo HM_Social_Counts()->get_total_shares( 'facebook' );
}
```