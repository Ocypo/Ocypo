<?php
if(!defined('BASE')) die("Nope.avi");

#Databases & LDAP is configured per dbtype: mysql, odbc, pgsql, sqlite, ldap (/!\ WIP /!\)
#The port is always the last argument and is optional.
#BASEMODEL::set( 'DATABASENAME' )->TYPE(DB_HOST, DB_USER, DB_PASS, DB_NAME [, DB_PORT]);
#Example: BASEMODEL::set( 'db1' )->mysql("localhost", "root", "root", "my_datbase");
#Example: BASEMODEL::set( 'db2' )->odbc("localhost", "my_database", 1337);
#Example: BASEMODEL::set( 'db3' )->pgsql("localhost", "root", "root", "my_datbase");
#Example: BASEMODEL::set( 'db4' )->sqlite("/path/to/database.sqlite");
#Example: BASEMODEL::set( 'db5' )->ldap("localhost", "me@local.domain", "myPassword123", "DC=local,DC=domain");

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
CONFIG::$forceSSL = false;

/*** CRYPT Settings ***/

#Your encrypted data can only be decrypted if the decryptor has the salt. Do not share this with anybody!
#Uncomment the line below and add a 52 character key to enable encrypting/decrypting.
//CRYPT::setSalt("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");

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

ERR::$log = true;
ERR::$customPages = false; #Replace with controller name, functions are the error codes, eg, 'function __404($errorMessage){ echo "Custom 404 page"; }'.
ERR::$exclude = array(E_NOTICE, E_STRICT, E_DEPRECATED); #E_NOTICE, E_STRICT, E_DEPRECATED #Look at http://www.php.net/manual/en/errorfunc.constants.php for constants.