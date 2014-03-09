<?php
class SITE
{
  /**
   * generateURL(<string> controller/view)
   * redirect(<string> controller/view)
   * isMobile(void)
   * opendir(<string> path to open[, <array> only open files in array[, <bool> return list]])
   * addDir(<string> path to open[, <array> only open files in array])
   * asset(<string> link or path to file[, <string> custom extension])
   * img(<string> link or path to file[, <string> image location path[, <array> args]])
   * createThumbnail(<string> path to image, <string> name of image [, <string> thumbnail prefix])
   * a(<string> link, <string> content[, <array> args])
   * getURI()
   */

  public static function generateURL($controller)
  {
    $url = $controller;
    if($view != "")
      $url .= '/'. $view;
    if($view != "" and $args != "")
      $url .= '/'. $args;
    return BASE.$url;
  }

  public static function redirect($to)
  {
    if(substr($to, 0, 7) == "http://" || substr($to, 0, 8) == "https://")
      $url = $to;
    else
    {
      if(substr($to, 0, 1) == "/")
        $to = substr($to, 1);
      $url = BASE.$to;
    }
    header('Location: '.$url);
  }

  public static function isMobile()
  {
    return (bool)preg_match('#\b(ip(hone|od)|android\b.+\bmobile|opera m(ob|in)i|windows (phone|ce)|blackberry'.
                      '|s(ymbian|eries60|amsung)|p(alm|rofile/midp|laystation portable)|nokia|fennec|htc[\-_]'.
                      '|up\.browser|[1-4][0-9]{2}x[1-4][0-9]{2})\b#i', $_SERVER['HTTP_USER_AGENT'] );
  }

  public static function opendir( $dir, $filter = array('php'), $r = false)
  {
    $return = array();
    if(file_exists($dir) and $handle = opendir($dir))
    {
      while (false !== ($entry = readdir($handle)))
      {
        $ext = explode(".", $entry);
        if(count($ext) > 1 and count($filter) > 0 and in_array($ext[1], $filter))
          $return[] = $entry;
      }
      closedir($handle);
    }
    return ($r === true) ? $return : false;
  }

  public static function addDir( $dir, $filter = array('php'))
  {
    if($files = self::opendir( $dir, $filter, true))
    {
      foreach($files as $file)
        include($dir.'/'.$file);

      return true;
    }
    else
      return false;
  }

  public static function asset($link, $ext = '')
  {
    $exp = explode('.', $link);
    $ext = (empty($ext)) ? $exp[count($exp)-1] : $ext;

    $extToType = array(
                  'js'  => '<script type="text/javascript" src=";;"></script>',
                  'css' => '<link rel="stylesheet" type="text/css" href=";;" />',
                  'ico' => '<link rel="shortcut icon" href=";;" type="image/x-icon" />'
                  );

    if(substr($link, 0, 7) == "http://" || substr($link, 0, 8) == "https://")
      $http = $link;
    else
      $http = BASE.'assets/'.$ext.'/'.$link;

    $link = str_replace(';;', $http, $extToType[$ext]);
    return $link;
  }

  public static function img($link, $path = "", $args = array())
  {
    if(substr($link, 0, 7) == "http://" || substr($link, 0, 8) == "https://")
      $http = $link;
    else
    {
      $path = (empty($path) or $path == "") ? "img" : $path;
      if(substr($path, -1) == "/")
        $path = substr($path, 0, -1);
      $http = BASE.'assets/'.$path.'/'.$link;
    }
    
    $exp = explode('.', $link);
    $alt = $exp[count($exp)-2];

    $str = "";
    if(count($args)>0)
      foreach ($args as $key => $value)
        $str .= ' '.$key.'="'.$value.'"';

    return '<img'.$str.' src="'.$http.'" alt="'.$alt.'">';
  }

  public static function a($to, $inner, $args = array())
  {
    if(substr($to, 0, 7) == "http://" || substr($to, 0, 8) == "https://")
      $http = $to;
    else
      $http = BASE.$to;

    $str = "";
    if(count($args)>0)
      foreach ($args as $key => $value)
        $str .= ' '.$key.'="'.$value.'"';

    return '<a'.$str.' href="'.$http.'">'.$inner.'</a>';
  }

  private static function removeEmptyVarsFromArray($array)
  {
    $return = array();
    foreach($array as $temp)
      if(!empty($temp))
        $return[] = $temp;

    return $return;
  }

  public static function getURI()
  {
    $uri = explode('/', $_SERVER['REQUEST_URI']);
    $url = self::removeEmptyVarsFromArray($uri);

    $script = explode('/', $_SERVER["SCRIPT_NAME"]);
    array_pop($script);
    $scr = self::removeEmptyVarsFromArray($script);

    for($i = 0; $i < count($scr); $i++)
      array_shift($url);

    return $url;
  }

  public static function error($code = 404, $text = "")
  {
    throw new \ErrorException('Error deprecated function "error"', 1);
  }
}