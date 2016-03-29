<?php
class SITE
{
  /**
   * generateURL(<string> controller/view)
   * redirect(<string> controller/view)
   * isMobile(void)
   * opendir(<string> path to open[, <array> only open files in array[, <bool> return list]])
   * addDir(<string> path to open[, <bool> also open sub directories?])
   * asset(<string> link or path to file[, <string> custom directory])
   * getContent(<string> link or path to file[, <string> custom directory])
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

  public static function openDir( $dir, $filter = array())
  {
    $ignore = array('.', '..', '.DS_Store', 'Thumbs.db','.gitignore');
    $return = array();
    if(file_exists($dir) and $handle = opendir($dir))
    {
      while (false !== ($entry = readdir($handle)))
      {
        $ext = explode(".", $entry);
        if((count($ext) > 1 and count($filter) > 0 and in_array($ext[1], $filter)) or (count($filter) == 0 and !in_array($entry, $ignore)))
          $return[] = $entry;
      }
      closedir($handle);
    }
    return $return;
  }

  public static function addDir( $dir, $recursion = true)
  {
    foreach(glob($dir."/*.php") as $file)
      include($file);
    if($recursion === true)
      foreach(glob($dir."/*", GLOB_ONLYDIR) as $folder)
        if(file_exists($folder.'/index.php'))
          include($folder.'/index.php');
  }

  public static function asset($linkOrFilename, $directory = '')
  {
    $exp = explode('.', $linkOrFilename);
    $directory = (empty($directory)) ? end($exp) : $directory;

    $extToType = array(
                  'js'  => '<script type="text/javascript" src=";;"></script>',
                  'css' => '<link rel="stylesheet" type="text/css" href=";;" media="screen, projection" />',
                  'ico' => '<link rel="shortcut icon" href=";;" type="image/x-icon" />'
                  );

    if(substr($linkOrFilename, 0, 7) == "http://" || substr($linkOrFilename, 0, 8) == "https://") {
      $http = $linkOrFilename;
    }
    else {
      #Check if we need to obfuscate
      if(class_exists(CONFIG) && CONFIG::$obfuscateURLs) {
        $http = BASE.'assets/obfuscated/'.self::encrypt($directory.'/'.$linkOrFilename);
      }
      else {
        $http = BASE.'assets/'.$directory.'/'.$linkOrFilename;
      }
    }

    $return = @str_replace(';;', $http, $extToType[$directory]);
    return $return;
  }

  public static function encrypt($str)
  {
    $data = base64_encode($str);
    $data = str_replace(array('+','/','='), array('-','_','', '+'), $data);
    $split = str_split($data, 5);
    $split = array_reverse($split);
    $data = implode("", $split);
    return $data;
  }

  public static function decrypt($str)
  {
    $data = str_replace(array('-','_'), array('+','/'), $str);
    $mod5 = strlen($data) % 5;
    $end = "";
    if ($mod5) {
      $end = substr($data, 0, $mod5);
      $data = substr($data, $mod5);
    }
    $split = str_split($data, 5);
    $split = array_reverse($split);
    $data = implode("", $split).$end;
    
    $mod4 = strlen($data) % 4;
    if ($mod4) {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  public static function getContent($linkOrFilename, $directory = '')
  {
    $exp = explode('.', $linkOrFilename);
    $directory = (empty($directory)) ? $exp[count($exp)-1] : $directory;

    if(substr($linkOrFilename, 0, 7) == "http://" || substr($linkOrFilename, 0, 8) == "https://")
      $file = $linkOrFilename;
    else
      $file = 'assets/'.$directory.'/'.$linkOrFilename;

    return file_get_contents($file);
  }

  public static function img($link, $path = "", $args = array())
  {
    if(substr($link, 0, 7) == "http://" || substr($link, 0, 8) == "https://")
      $http = $link;
    else {
      $path = (empty($path) or $path == "") ? "images" : $path;
      if(substr($path, -1) == "/")
        $path = substr($path, 0, -1);

      #Check if we need to obfuscate
      if(class_exists(CONFIG) && CONFIG::$obfuscateURLs) {
        $http = BASE.'assets/obfuscated/'.self::encrypt($path.'/'.$link);
      }
      else {
        $http = BASE.'assets/'.$path.'/'.$link;
      }
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
}