<?php
class CRYPT
{
  /**
   * setSalt(<string> $name, <string> $content [, <int> $duration (seconds)[, <bool> $encrypt]])
   * encode(<string> $str) - Simple encoding
   * decode(<string> $str) - Simple decoding
   * encrypt(<string> $str) - Advanced encrypting
   * decrypt(<string> $str) - Advanced decrypting
   */

  private static $salt = false;

  private static function getSalt()
  {
    if(self::$salt === false)
      ERR::generate(500, "No salt set, unable to encrypt/decrypt!");

    return self::$salt;
  }

  public static function setSalt($str)
  {
    if(self::$salt !== false)
      ERR::generate(500, "You can only set the salt once!");
    
    if(strlen($str) != 52)
      ERR::generate(500, "Salt must be 52 characters long!");
    
    //salt needs to be in hex...
    $salt = bin2hex(base64_decode($str));
    //make sure the salt is 32 bytes
    if(strlen($salt) < 68)
      ERR::generate(500, "Unknown salt error!");
    $salt = substr($salt, 2, 64);
    $salt = pack('H*', $salt);
    if(strlen($salt) != 32)
      ERR::generate(500, "Unknown salt error!");

    self::$salt = $salt;
  }

  public static function encode($str)
  {
    $data = base64_encode($str);
    $data = str_replace(array('+','/','='), array('-','_','', '+'), $data);
    $split = str_split($data, 5);
    $split = array_reverse($split);
    $data = implode("", $split);
    return $data;
  }

  public static function decode($str)
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

  public static function encrypt($str)
  {
    $salt = self::getSalt();
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $str, MCRYPT_MODE_CBC, $iv);
    $ciphertext = $iv . $ciphertext;
    $ciphertext = self::encode($ciphertext);
    return $ciphertext;
  } 

  public static function decrypt($str)
  {
    $salt = self::getSalt();
    $ciphertext = self::decode($str);
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
    $iv_dec = substr($ciphertext, 0, $iv_size);
    $ciphertext = substr($ciphertext, $iv_size);
    return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $ciphertext, MCRYPT_MODE_CBC, $iv_dec);
  }
}