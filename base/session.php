<?php
class SESSION
{
  /**
   * set(<string> name of session, <string> content)
   * get(<string> name of session)
   * remove(<string> name of session)
   */

  public static function set($name, $var)
  {
    $_SESSION[$name] = $var;
  }

  public static function get($name = false)
  {
    if($name === false)
      return $_SESSION;
    else
      return $_SESSION[$name];
  }

  public static function remove($name)
  {
    if(isset($_SESSION[$name]))
      unset($_SESSION[$name]);
  }
}