<?php
define('BASE', 'http://'.$_SERVER['SERVER_NAME'].substr($_SERVER["SCRIPT_NAME"], 0, -9));
define('VERSION', '8.1');

if($handle = opendir('./base'))
{
  while (false !== ($entry = readdir($handle)))
  {
    $ext = explode(".", $entry);
    if($ext[1] == "php")
      include('./base/'.$entry);
  }
}
closedir($handle);

session_start();
ob_start();

include('./config.php');

/**** SEND SERVER INFO ****/
//if($config['debug'] !== true) sendDebugInformation();
/**** CONTINUE CONFIG ****/

$uri      = SITE::getURI();
$class    = array_shift($uri);
$function = array_shift($uri);
$args     = $uri;
if(strpos($class, '?') !== false) $class = explode('?', $class)[0];
if(strpos($function, '?') !== false) $function = explode('?', $function)[0];

#check if a route exists for the current path, these will override controllers.
if(array_key_exists($class, $routes))
  $class = $routes[$class];

#before we do anything make sure these private arrays can never be called!
unset($routes);

SITE::addDir('./libaries');

if($class != "" and file_exists('./controllers/'.$class.'.php'))
{  
  $models = SITE::openDir('./models', array('php'), true);
  asort($models);
  foreach($models as $model)
    include('./models/'.$model);

  Auth::getSession();
  Lang::getLocale();

  include('./controllers/'.$class.'.php');
  $private = array('__construct', '__destruct');
  $$class = new $class();
  $function = str_replace('-', '_', $function);
  if(empty($function) or in_array($function, $private))
    $function = 'index';

  define('__FUNCTION', $function);
  define('__CLASS', $class);

  if(method_exists($$class, $function))
    call_user_func_array(array($$class, $function), $args);
  elseif(method_exists($$class, 'index') and ($config['shiftFunc'] === true or (is_array($config['shiftFunc']) and in_array($class, $config['shiftFunc']))))
  {
    array_unshift($args, $function);
    call_user_func_array(array($$class, 'index'), $args);
  }
  else
    ERROR::generate(404, 'Function doesn\'t exist!');
}
else
  ERROR::generate(404, 'Class doesn\'t exist!');
?>