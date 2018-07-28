<?php
// Rename this file to "config.php" and enter values appropriate to your site.

// Add an include path if needed.
// set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/include');

// 'SITE_NAME' used as page heading, and/or whenever the full official site name is needed.
$GLOBALS['SITE_NAME'] = 'Example Site';

// 'SITE_URL' is full official URL with no closing slash.
$GLOBALS['SITE_URL'] = 'https://www.example.org';

// 'SITE_URL' is full official URL with no closing slash.
$GLOBALS['SITE_DOMAIN'] = 'example.org';

// 'SITE_URL_CASUAL' is used to refer casually or informally to the site. i.e. "See you at example.com!"
//   Usually this is the domain name.
$GLOBALS['SITE_URL_CASUAL'] = $GLOBALS['SITE_DOMAIN'];

// Website Base Path.
$GLOBALS['WEB_BASE_DIRECTORY'] = '/var/www/www.example.org/html/';

// Content store path base. File uploads for user contributed content go here.
// For security, keep this out of directories that are served by your web server.
$GLOBALS['CONTENT_STORE_DIRECTORY'] = '/var/www/www.example.org/userContent/';

// Password encryption is necessarily and purposefully costly as far as processor time. A balance must be struck
//  between making it expensive for hackers to crack, and making it inexpensive enough for the site to function.
//
// Typically you want your password decryption to take less than 50ms. To translate "cost" to milliseconds on
//  your hardware, include functions.php and run getSaltHashTimeCost($cost). NOTE: Increase slowly when testing
//  and be careful with numbers over about 12.
$GLOBALS['PASSWORD_HASH_COST'] = 8;
$GLOBALS['VERIFYCODE_HASH_COST'] = 5;

// The debugOut function makes heavy use of this. It's very helpful during debugging to
//  live monitor this. To do so, SSH into your server and use:
//    tail -f var/log/nginx/dev.log
// This file must exist. Create the file and set permissions to allow PHP to write to it..)
$GLOBALS['LOG_FILE_PATH'] = '/var/log/nginx/dev.log';

// DB Connection info.
$GLOBALS['DB_SERVER'] = $GLOBALS['SITE_DOMAIN'];
$GLOBALS['DB_USERNAME'] = 'DB Username';
$GLOBALS['DB_PASSWORD'] = 'DB Password';
$GLOBALS['DB_DATABASE'] = 'Database (Schema)';
$GLOBALS['DB_PORT'] = '3306';
$GLOBALS['DB_CHARACTER_SET'] = 'utf8';
$GLOBALS['DB_OPTIONS'] =
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false,];
$GLOBALS['DB_DSN'] =
    "mysql:host=" . $GLOBALS['DB_SERVER'] . ";dbname=" . $GLOBALS['DB_DATABASE'] . ";" . "port=" . $GLOBALS['DB_PORT'] .
    ";charset=" . $GLOBALS['DB_CHARACTER_SET'];

// Mailgun connection info.
$GLOBALS['MAILGUN_API_KEY'] = 'Mailgun API Key';
$GLOBALS['MAILGUN_MAIL_DOMAIN'] = 'Mailgun Mail Domain';

// UI for contact verification.
$GLOBALS['VERIFICATION_EMAIL_FROM'] = 'verify@' . $GLOBALS['SITE_DOMAIN'];
$GLOBALS['VERIFICATION_EMAIL_SUBJECT'] = 'Please Verify Your Email';
$GLOBALS['VERIFICATION_EMAIL_SIGNATURE'] = 'The ' . $GLOBALS['SITE_NAME'] . ' Community';

// UI Lables
$GLOBALS['CONTENT_THUMBNAIL_LABEL'] = 'Content Graphic';
$GLOBALS['CONTENT_SUMMARY_LABEL'] = 'Summary';
$GLOBALS['CONTENT_EXCERPT_LABEL'] = 'Sample';
$GLOBALS['CONTENT_DESCRIPTION_LABEL'] = 'Description';

// Legal Info
$GLOBALS['SITE_COMPTROLLER'] = 'Comptroller Legal Name';
$GLOBALS['SITE_COMPTROLLER_TITLE'] = 'Comptroller Title';
$GLOBALS['SITE_LEGAL_ADDRESS'] = 'Address for legal contact';


// Development
ini_set('display_errors', 1);

// Production
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);

