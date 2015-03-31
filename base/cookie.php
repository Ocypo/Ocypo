<?php
class COOKIE
{
  /**
   * set(<string> $name, <string> $content [, <int> $duration (seconds)[, <bool> $encrypt]])
   * get(<string> $name[, <bool> $decrypt])
   * remove(<string> $name)
   */

  public static function set($name, $str, $duration = 86400, $encrypt = true)
  {
    if($encrypt === true) $str = self::encrypt($str);
    return setcookie($name, $str, time() + $duration, '/');
  }

  public static function remove($name)
  {
    return setcookie($name, null, -1, '/');
  }

  public static function get($name, $decrypt = true)
  {
    $cookie = (isset($_COOKIE[$name])) ? $_COOKIE[$name] : false ;
    if($decrypt === true and $cookie !== false) $cookie = self::decrypt($cookie);
    return $cookie;
  }

  private static function encrypt($str)
  {
    $data = base64_encode($str);
    $data = str_replace(array('+','/','='), array('-','_','', '+'), $data);
    $split = str_split($data, 5);
    $split = array_reverse($split);
    $data = implode("", $split);
    return $data;
  }

  private static function decrypt($str)
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
}