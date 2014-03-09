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
/*
class NCRYPT {
  public static $key = "yourSecretKey";

  private static function safe_b64encode($string)
  {
    $data = base64_encode($string);
    $data = str_replace(array('+','/','='),array('-','_',''),$data);
    return $data;
  }

  private static function safe_b64decode($string)
  {
    $data = str_replace(array('-','_'),array('+','/'),$string);
    $mod4 = strlen($data) % 4;
    if ($mod4) {
      $data .= substr('====', $mod4);
    }
    return base64_decode($data);
  }

  public static function encode($value = false)
  {
    if(function_exists('mcrypt') && extension_loaded('mcrypt'))
      return $value; //return self::mcrypt_encode($value);
    else
      return self::safe_b64encode($value);
  }

  public static function decode($value = false)
  {
    if(function_exists('mcrypt') && extension_loaded('mcrypt'))
      return self::mcrypt_decode($value);
    else
      return self::safe_b64decode($value);
  }

  private static function mcrypt_encode($value)
  { 
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, self::$key, $value, MCRYPT_MODE_ECB, $iv);
    return trim(self::safe_b64encode($crypttext)); 
  }

  private static function mcrypt_decode($value)
  {
    $crypttext = self::safe_b64decode($value); 
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, self::$key, $crypttext, MCRYPT_MODE_ECB, $iv);
    return trim($decrypttext);
  }
}*/