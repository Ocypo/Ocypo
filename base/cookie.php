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
    setcookie($name, $str, time() + $duration, '/');
  }

  public static function remove($name)
  {
    setcookie($name, null, -1, '/');
  }

  public static function get($name, $decrypt = true)
  {
    $cookie = (isset($_COOKIE[$name])) ? $_COOKIE[$name] : false ;
    if($decrypt === true and $cookie !== false) $cookie = self::decrypt($cookie);
    return $cookie;
  }

  private static function encrypt($string)
  {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
  }

  private static function decrypt($string)
  {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }
}