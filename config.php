<?php
if(!defined('BASE')) die("Nope.avi");

#DATABASE::add( HOSTNAME, array(DB_HOST, DB_USER, DB_PASS, DB_NAME [, DB_PORT]));

#routes = array( FROM => TO, ...);
$routes = array(
  '' => 'home',
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

ERROR::$log = true;
ERROR::$debug = false;
ERROR::$exclude = array(); #E_NOTICE, E_STRICT, E_DEPRECATED #Look at http://www.php.net/manual/en/errorfunc.constants.php for constants.
?>