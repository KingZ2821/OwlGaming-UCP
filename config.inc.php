<?php

define('SITE_MAINTENANCE', false);

//ERROR CONTROL
define('DB_SHOW_ERROR', false);
//error_reporting(E_ALL);

//SESSION
/* After SESSION_LIFETIME of seconds, stored data will be seen as 'garbage' and cleaned up by the garbage collection process.
The probability that the 'garbage collection' process is started
on every session initialization. The probability is calculated by using
SESSION_PROBABILITY/SESSION_DIVISOR. Where session.gc_probability is the numerator
and gc_divisor is the denominator in the equation. Setting SESSION_PROBABILITY to 1
when the SESSION_DIVISOR value is 100 will give you approximately a 1% chance
the gc will run on any give request.
 */
define('SESSION_USE_HTTPS', true);
define('SESSION_PROBABILITY', 1);
define('SESSION_DIVISOR', 1000);
define('SESSION_LIFETIME', 86400); // 24 hours

//MTA database server
define('DB_SERVER', "localhost");
define('DB_USER', "mta");
define('DB_PASS', "3a7iqAxaLO3a1Aqu3I5E5IlEd32uyE");
define('DB_DATABASE', "owl_mta");
define('DB_PREFIX', "");

//Forums database server
define('DB_FORUMS_SERVER', "forums.owlgaming.net");
define('DB_FORUMS_USER', "");
define('DB_FORUMS_PASS', "");
define('DB_FORUMS_DATABASE', "");
define('DB_FORUMS_PREFIX', "");

//Logs database server
define('DB_LOGS_SERVER', "localhost");
define('DB_LOGS_USER', "mta");
define('DB_LOGS_PASS', "3a7iqAxaLO3a1Aqu3I5E5IlEd32uyE");
define('DB_LOGS_DATABASE', "owl_logs");
define('DB_LOGS_PREFIX', "");

//MTA SDK
define('SDK_IP', "mta.owlgaming.net");
define('SDK_PORT', "22005");
define('SDK_USER', "website");
define('SDK_PASSWORD', "4Jhc9FvbjT&*");

//EMAIL STUFF
define('EMAIL_DEFAULT_FROM_ADDRESS', 'system@owlgaming.net');
define('EMAIL_DEFAULT_FROM_NAME', 'OwlGaming Community');

//FEEDS
define('FEED_MTA', '');
define("FEED_UCP", '');
define("FEED_LIMIT", 100); // Number of rows to fetch.
define("FEED_CACHE_TIMEOUT", 600); // in seconds, 0 means no cache
define("FEED_FILTER_KEY", '[PUBLIC]'); // Feeds that contains this keyword in title will be displayed, Empty means show all feeds.

//Donation Stuff
define('USE_SANDBOX', false);
define('CA_CERT', false); //"/etc/httpd/ssl/ucp.ca-bundle");
if (USE_SANDBOX) {
    define('SELLER_EMAIL', "");
	define('BUSINESS_EMAIL', SELLER_EMAIL);
    define('PAYPAL_URL', "https://www.sandbox.paypal.com/cgi-bin/webscr");
} else {
    define('BUSINESS_EMAIL', "");
    define('SELLER_EMAIL', "");
    define('PAYPAL_URL', "https://www.paypal.com/cgi-bin/webscr");
}
define('WEBMASTER_EMAIL', "");
define('DONATION_SERVER_MAIL', "");

define('BITCOIN_TEST_MODE', false);
define('BITCOIN_ROOT', "https://blockchain.info/");
define('BITCOIN_ADDRESS', "");
define('BITCOIN_SECRET', "");
define('BITCOIN_CLEANUP_DAY', 30);
