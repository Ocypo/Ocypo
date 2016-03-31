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
    if($encrypt === true) $str = CRYPT::encode($str);
    return setcookie($name, $str, time() + $duration, '/');
  }

  public static function remove($name)
  {
    return setcookie($name, null, -1, '/');
  }

  public static function get($name, $decrypt = true)
  {
    $cookie = (isset($_COOKIE[$name])) ? $_COOKIE[$name] : false ;
    if($decrypt === true and $cookie !== false) $cookie = CRYPT::decode($cookie);
    return $cookie;
  }
}