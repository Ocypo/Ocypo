<?php
#Start by setting the initiation time.
define('TIME', microtime(true));

#Load framework core files
foreach(glob("./base/*.php") as $file)
  include($file);

session_start();
ob_start();

include('./config.php');

/**** Check if PHP > 5.3.0 and if SSL should be used ****/
CONFIG::checkPHPVersion();
CONFIG::checkSSL();

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

#load external libraries.
SITE::addDir('./libraries');

#Execute the magic!
if(CONFIG::$obfuscateURLs && $class == "assets" && $function == "obfuscated")
{
  $asset = CRYPT::decode(current($args));
  $ext = strtolower(substr($asset, strrpos($asset, '.')+1));
  $file = 'assets/'.$asset;

  if(!CONFIG::$hotlinking && strpos($_SERVER['HTTP_REFERER'], BASE) === false) {
    ERR::generate(401, 'Hotlinking is not allowed!');
  }

  #See if the file exists and if not throw an error.
  try {
    $content = file_get_contents($file);
  }
  catch (Exception $e) {
    ERR::generate(404, 'File not found!');
  }

  $mime_types = array(
    'txt' => 'text/plain',
    'htm' => 'text/html',
    'html' => 'text/html',
    'php' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'xml' => 'application/xml',
    'swf' => 'application/x-shockwave-flash',
    'flv' => 'video/x-flv',

    // images
    'png' => 'image/png',
    'jpe' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'bmp' => 'image/bmp',
    'ico' => 'image/vnd.microsoft.icon',
    'tiff' => 'image/tiff',
    'tif' => 'image/tiff',
    'svg' => 'image/svg+xml',
    'svgz' => 'image/svg+xml',

    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    'exe' => 'application/x-msdownload',
    'msi' => 'application/x-msdownload',
  );

  $content_type = (array_key_exists($ext, $mime_types)) ? $mime_types[$ext] : 'application/octet-stream';

  #Headers
  header("Content-Type: ".$content_type);
  header('Cache-Control: public');
  header("X-Content-Type-Options: nosniff");
  $last_modified_time = filemtime($file);
  $etag = md5_file($file); 

  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $last_modified_time).' GMT');
  header("Etag: $etag");

  #Only use caching if debug is disabled!
  if(!CONFIG::$debug) {
    if(@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) { 
      header("HTTP/1.1 304 Not Modified"); 
      exit();
    }
  }

  echo $content;
}
elseif($class != "" && file_exists('./controllers/'.$class.'.php'))
{
  $models = SITE::openDir('./models', array('php'), true);
  asort($models);
  foreach($models as $model)
    include('./models/'.$model);

  include('./controllers/'.$class.'.php');
  $$class = new $class();

  if(method_exists($$class, $function))
    call_user_func_array(array($$class, $function), $args);
  elseif(method_exists($$class, 'index') and (CONFIG::$shiftFunc === true or (is_array(CONFIG::$shiftFunc) and in_array($class, CONFIG::$shiftFunc))))
  {
    #Undo the function magic we applied earlier and push it back on the args stack!
    array_unshift($args, str_replace('_', '-', $function));
    call_user_func_array(array($$class, 'index'), $args);
  }
  else
    ERR::generate(404, 'Function doesn\'t exist!');
}
elseif($class == "assets") {
  ERR::generate(404, 'File not found!');
}
else {
  ERR::generate(404, 'Class "'.$class.'" doesn\'t exist!');
}
?>