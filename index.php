<?php
define('BASE', 'http://'.$_SERVER['SERVER_NAME'].substr($_SERVER["SCRIPT_NAME"], 0, -9));
define('VERSION', '8.2');
define('TIME', microtime(true));

foreach(glob("./base/*.php") as $file)
  include($file);

session_start();
ob_start();

include('./config.php');

/**** SEND SERVER INFO ****/
//if($config['debug'] !== false) sendDebugInformation();
/**** CONTINUE CONFIG ****/

$uri      = SITE::getURI();
$class    = array_shift($uri);
$function = array_shift($uri);
$args     = $uri;

#typically only used when shiftfunc is enabled.
if(strpos($class, '?')    !== false) $class    = explode('?', $class)[0];
if(strpos($function, '?') !== false) $function = explode('?', $function)[0];

#set function and replace - with _.
$function = str_replace('-', '_', $function);
if(empty($function) or substr($function, 0, 2) == '__')
  $function = 'index';

#check if a route exists for the current path, these will override controllers.
route::check($class, $function);
$class = route::getClass();
$function = route::getFunction();

#Add constants for function and class.
#You really should use route::getFunction() or route::getClass() though;
define('__FUNCTION', $function);
define('__CLASS', $class);

#load external libaries.
SITE::addDir('./libaries');

#Execute the magic!
if($class != "" and file_exists('./controllers/'.$class.'.php'))
{  
  $models = SITE::openDir('./models', array('php'), true);
  asort($models);
  foreach($models as $model)
    include('./models/'.$model);

  include('./controllers/'.$class.'.php');
  $$class = new $class();

  if(method_exists($$class, $function))
    call_user_func_array(array($$class, $function), $args);
  elseif(method_exists($$class, 'index') and ($config['shiftFunc'] === true or (is_array($config['shiftFunc']) and in_array($class, $config['shiftFunc']))))
  {
    #Undo the function magic we applied earlier and push it back on the args stack!
    array_unshift($args, str_replace('_', '-', $function));
    call_user_func_array(array($$class, 'index'), $args);
  }
  else
    ERROR::generate(404, 'Function doesn\'t exist!');
}
else
  ERROR::generate(404, 'Class "'.$class.'" doesn\'t exist!');
?>