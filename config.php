<?php
if(!defined('BASE')) die("Nope.avi");

#DATABASE::add( DATABASENAME, array(DB_HOST, DB_USER, DB_PASS, DB_NAME [, DB_PORT]));
#NOTE: DATABASENAME should be capital case!

#routes = array( FROM => TO, ...);
$routes = array(
  '' => 'home',
  'lang' => 'language',
);

#config
$config = array(
  'debug' => false, #Don't use on live version!
  'shiftFunc' => false, #If func doesn't exist use index($func) instead. <boolean> true for all controllers OR <array> controllers. E.Q. array('controller1', 'controller2');
);

#defaults
VIEW::UpdateGlobals(array(
  'baseTitle' => 'My website!',
  'appendPageTitle' => true, #append or overwrite
  'pageTitleSeperator' => ' | ',
  'useControllerIfEmpty' => true,
  ));

LANG::setDefault('en'); #Set default language to English.

ERROR::$log = true;
ERROR::$customPages = false #Replace with controller name, functions are the error codes, eg, 'function __404($errorMessage){ echo "Custom 404 page"; }'.
ERROR::$debug = false;
ERROR::$exclude = array(); #E_NOTICE, E_STRICT, E_DEPRECATED #Look at http://www.php.net/manual/en/errorfunc.constants.php for constants.
?>