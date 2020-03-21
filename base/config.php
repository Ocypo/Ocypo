<?php
class CONFIG
{
  public  static $forceSSL         = 0;
  public  static $shiftFunc        = false;
  public  static $debug            = true;
  public  static $obfuscateURLs    = false;
  public  static $hotlinking       = true;
  private static $version          = "9.0";
  public  static $stack            = array();

  public static function checkPHPVersion()
  {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));

    if (PHP_VERSION_ID < 50300) {
      ERR::GENERATE(0, "PHP outdated...</br>In order to use this framework you must at least run PHP 5.3.0!");
    }
  }

  public static function isSSL() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
  }

  private static function isIPv4() {
    return (count(explode(".", $_SERVER["REMOTE_HOST"])) == 4);
  }

  private static function isIPv6() {
    return (count(explode(":", $_SERVER["REMOTE_HOST"])) > 4);
  }

  private static function isBot() {
    return (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']));
  }

  public function checkSSL($force = false) #You can bruteforce ssl to be used for specific pages.
  {
    $forceSSL = self::$forceSSL;
    if($forceSSL == 0 || self::isSSL() == true)
      return; #Don't do anything if forceSSL is disabled or if already on SSL.

    if($forceSSL === true)
      $forceSSL = 1;

    $dec = decbin(8-$forceSSL);
    $dec = sprintf("%03d", $dec);

    $bits = str_split($dec, 1);
    list($IPv4, $IPv6, $crawlers) = $bits;

    if($IPv4 && self::isIPv4())
      $force = true;
    if($IPv6 && self::isIPv6())
      $force = true;
    if($crawlers && self::isBot())
      $force = true;

    if($force) {
      header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    }
  }

  public static function getBase($forceSSL = false)
  {
    if(php_sapi_name() === 'cli') {
      return "CLI";
    }
    else {
      $port = ($_SERVER['SERVER_PORT'] != 80 && !(self::isSSL() == true && $_SERVER['SERVER_PORT'] == 443)) ? ":".$_SERVER['SERVER_PORT'] : '';
      return ( (self::isSSL() || $forceSSL) ? 'https://' : 'http://') .$_SERVER['SERVER_NAME'].$port.substr($_SERVER["SCRIPT_NAME"], 0, -9);
    }
  }

  public static function getVersion()
  {
    return self::$version;
  }

  public static function debug()
  {
    self::$stack[] = func_get_args();
  }

  public static function showDebugStack()
  {
    if(CONFIG::$debug !== false) {
      echo "/***** DEBUG STACK *****/";
      echo "<br />";
      foreach(CONFIG::$stack as $event) {
        print_r($event);
        echo "<br />";
      }
      echo "/*** END DEBUG STACK ***/";
      CONFIG::$stack = array(); //Clear the stack!
    }
  }
}

#Set defines!
define('BASE', config::getBase());
define('OCYPO_BASE', config::getBase());
define('OCYPO_VERSION', config::getVersion());