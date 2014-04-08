<?php
if(!defined('BASE')) die("Nope.avi");

#DATABASE::add( DATABASENAME, array(DB_HOST, DB_USER, DB_PASS, DB_NAME [, DB_PORT]));
#NOTE: DATABASENAME should be capital case!

#ROUTE::add( FROM, TO );
ROUTE::add('', 'home');
ROUTE::add('lang', 'language');
ROUTE::add('test@wtf', 'home@test');

#config
$config = array(
  'debug' => false, #Don't use on live version!
  'shiftFunc' => false, #If func doesn't exist use index($func) instead. <boolean> true for all controllers OR <array> controllers. E.Q. array('controller1', 'controller2');
);

#Title settings
VIEW::$websiteName = 'My website!';
VIEW::$displayFormat = 3;
# 1 = website name only
# 2 = class name only
# 3 = website name + class name
# 4 = function name only
# 5 = website name + function name
# 6 = class name + function name
# 7 = website name + class name + function name
VIEW::$seperator = ' | ';

#Language settings
LANG::setDefault('en'); #Set default language to English.

#Error settings
ERROR::$log = true; #Will log more detailed infomation
ERROR::$customPages = false; #Replace with controller name, functions are the error codes, eg, 'function __404($errorMessage){ echo "Custom 404 page"; }'.
ERROR::$exclude = array(); #E_NOTICE, E_STRICT, E_DEPRECATED #Look at http://www.php.net/manual/en/errorfunc.constants.php for constants.
?>