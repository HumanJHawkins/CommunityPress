<?php
// Rename this file to "config.php" and enter values appropriate to your site.

// Add an include path if needed.
// set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/include');

// 'SITE_NAME' used as page heading, and/or whenever the full official site name is needed.
$GLOBALS['SITE_NAME']       = 'Example Site';

// 'SITE_URL' is full official URL with no closing slash.
$GLOBALS['SITE_URL']        = 'https://www.example.org';

// 'SITE_URL' is full official URL with no closing slash.
$GLOBALS['SITE_DOMAIN']     = 'example.org';

// 'SITE_URL_CASUAL' is used to refer casually or informally to the site. i.e. "See you at example.com!"
//   Usually this is the domain name.
$GLOBALS['SITE_URL_CASUAL'] = $GLOBALS['SITE_DOMAIN'];

// The debugOut function makes heavy use of this. It's very helpful during debugging to
//  live monitor this. To do so, SSH into your server and use:
//    tail -f var/log/nginx/dev.log
// This file must exist. Create the file and set permissions to allow PHP to write to it..)
$GLOBALS['LOG_FILE_PATH'] = '/var/log/nginx/dev.log';

// DB Connection info.
$GLOBALS['DB_SERVER']   = 'DB Domain Name or Address';
$GLOBALS['DB_USERNAME'] = 'DB Username';
$GLOBALS['DB_PASSWORD'] = 'DB Password';
$GLOBALS['DB_DATABASE'] = 'Database (Schema)';
$GLOBALS['DB_PORT']     = 'Port (Usually 3306)';

// Mailgun connection info.
$GLOBALS['MAILGUN_API_KEY']     = 'Mailgun API Key';
$GLOBALS['MAILGUN_MAIL_DOMAIN']     = 'Mailgun Mail Domain';

// UI for contact verification.
$GLOBALS['VERIFICATION_EMAIL_FROM'] = 'verify@' . $GLOBALS['SITE_DOMAIN'];
$GLOBALS['VERIFICATION_EMAIL_SUBJECT'] = 'Please Verify Your Email';
$GLOBALS['VERIFICATION_EMAIL_SIGNATURE'] = 'The '. $GLOBALS['SITE_NAME'] . 'Community';







