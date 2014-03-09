<?php
class SANITIZE
{
  /**
   * clean_az(<string> string to sanitize) #alphabet
   * clean_naz(<string> string to sanitize) #numbers + alphabet
   * clean_nazu(<string> string to sanitize) #numbers + alphabet + underline
   * int(<string> string to sanitize) #numbers
   * float(<string> string to sanitize) #floats
   * email(<string> string to sanitize) #email
   * escape(<string> string to sanitize) #string
   */

  public static function clean_az($s)    {return preg_replace("/[^a-zA-Z]/","",$s);}
  public static function clean_naz($s)   {return preg_replace("/[^0-9a-zA-Z]/","",$s);}
  public static function clean_nazu($s)  {return preg_replace("/[^0-9a-zA-Z_]/","",$s);}
  public static function int($n)         {return filter_var($n,FILTER_SANITIZE_NUMBER_INT);}
  public static function float($n)       {return filter_var($n,FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);}
  public static function email($s)       {return filter_var($s,FILTER_VALIDATE_EMAIL);}
  public static function string($value)
  {
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
    return str_replace($search, $replace, $value);
  }
}