<?php
if(!defined('BASE')) die("Nope.avi");

#DATABASE::add( DATABASENAME, array(DB_HOST, DB_USER, DB_PASS, DB_NAME [, DB_PORT]));
#LDAP::add( LDAPNAME, array(LDAP_HOST, LDAP_USER, LDAP_PASS, LDAP_CN));
#NOTE: DATABASENAME/LDAPNAME should be capital case!

#ROUTE::add( FROM, TO );
ROUTE::add('', 'home');
ROUTE::add('lang', 'language');
ROUTE::add('test@wtf', 'home@test');

/*** Global Config ***/

#If main funciton doesn't exist use index($func) instead. <boolean> true for all controllers OR <array> controllers. E.Q. array('controller1', 'controller2');
#Default: false.
CONFIG::$shiftFunc = true;

#If enabled assets and images urls will be obfuscated.
#Default: false.
CONFIG::$obfuscateURLs = true;

#Allows hotlinking to the website. If disabled absolute urls will still work.
#Default: true.
CONFIG::$hotlinking = true;

#If debug is enabled additional logs are created, caching is disabled and custom detailed error pages will be displayed.
#NOTE: Disabling caching only works if obfuscateURLS is enabled!
#Default: false.
CONFIG::$debug = false;

#Force a secure connection.
# 1 = ALL (IPv4, IPv6 & crawlers)
# 2 = IPv4 + IPv6
# 3 = IPv4 + crawlers
# 4 = IPv4 only
# 5 = IPv6 + crawlers
# 6 = IPv6 only
# 7 = Crawlers only
CONFIG::$forceSSL = 2;

/*** Title Settings ***/

#Webapplication title.
VIEW::$websiteName = 'My website!';

# 1 = website name only
# 2 = controller name only
# 3 = website name + controller name
# 4 = function name only
# 5 = website name + function name
# 6 = controller name + function name
# 7 = website name + controller name + function name
VIEW::$displayFormat = 3;

#Seperatorcharacter(s) to be used between the main title and optional sub-titles.
VIEW::$seperator = ' | ';

/*** Language Settings ***/

#Default application language.
LANG::setDefault('en');

ERROR::$log = true;
ERROR::$customPages = false; #Replace with controller name, functions are the error codes, eg, 'function __404($errorMessage){ echo "Custom 404 page"; }'.
ERROR::$exclude = array(E_NOTICE, E_STRICT, E_DEPRECATED); #E_NOTICE, E_STRICT, E_DEPRECATED #Look at http://www.php.net/manual/en/errorfunc.constants.php for constants.