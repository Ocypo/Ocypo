<?php
class Auth
{
  /**
   * login(<string> Username, <string> Password )
   * getUsername(void)
   * getID(void)
   * loggedin(void)
   * requireLogin(<mixed> List[, <string> callback URL])
   * SetSession(<array> ClientDetails)
   * GetSession(void)
   */

  protected static $username = "Anonymous";
  protected static $id = 0;
  private static $isAdmin = false;
  private static $adminId = 0;
  
  public static function login($user, $pass, $referal = "")
  {
    #Override this function with your login method.
    self::$isAdmin = true;
    self::$username = "Niels Meijer";
    self::$id = 1;
    $ClientDetails["id"] = 1;
    $ClientDetails["firstname"] = 'Niels';
    $ClientDetails["lastname"] = 'Meijer';
    self::SetSession($ClientDetails);
    if($referal) header('Location: '.$referal);
  }

  public static function getUsername()
  {
    return self::$username;
  }

  public static function getID()
  {
    return self::$id;
  }
  
  public static function logout()
  {
    foreach($_COOKIE as $k => $v)
      if($k != "PHPSESSID")
        setcookie($k, "", time() + 1, '/');
    session_destroy();
  }
  
  public static function loggedin()
  {
    self::GetSession();
    return (self::$id == 0 or self::$username == "Anonymous") ? false : true;
  }
  
  public static function requireLogin($list = '*', $callback = 'user/login')
  {
    //throw new \ErrorException("Error deprecated function \"error\"", 1);

    SESSION::set('referer', $_SERVER["REDIRECT_URL"]);
    if(!self::loggedin())
    {
      $uri = site::getURI();
      if(is_array($list))
      {
        foreach($list as $page)
        {
          if($uri['1'] == $page)
          {
            site::redirect($callback);
          }
        }
      }
      if(is_string($list))
      {
        if($list == "*")
        {
          site::redirect($callback);
        }
        else
        {
          if($uri['1'] == $list)
          {
            site::redirect($callback);
          }
        }
      }
    }
    /*
    $args = func_get_args();
    #If no arguments are parsed, all pages require the user to be logged in.
    if(count($args) == 0 and !self::loggedin())
      VIEW::add('login', $args);
    elseif(count($args) == 1 and $args[0] == '*' and !self::loggedin())
      VIEW::add('login', $args);
    else
    {
      foreach($args as $func)
        if($func == SITE::getURI(true)['function'] and !self::loggedin()) VIEW::add('login');
    }
    */
    return false;
  }
  
  public static function SetSession($ClientDetails)
  {
    $id = $ClientDetails["id"];
    $fname = $ClientDetails["firstname"];
    $lname = $ClientDetails["lastname"];
    
    $time = 60 * 60 * 24; # Session time
    $_SESSION['user'] = self::encrypt($id, $fname, $lname);
    setcookie('user', self::encrypt($id, $fname, $lname), time() + $time, '/');
  }
  
  public static function GetSession()
  {
    if(isset($_SESSION['user']) && isset($_COOKIE['user']))
    {
      $result = self::decrypt($_SESSION['user']);
      $result2 = self::decrypt($_COOKIE['user']);
      if($result == $result2)
        self::$username = $result["firstname"]." ".$result["lastname"];
        self::$id = $result["id"];
    }
  }
  
  private static function encrypt($id, $fname, $lname)
  {
    $rand = rand(1, 10);
    $u = randomHash(2 * $rand).($id * 4 * $rand).randomHash(3 * $rand).":".$rand;
    $rand = rand(3, 8);
    
    $u .= ":".randomHash(strlen($lname));
    $name = $fname.strrev($lname);
    $f = randomHash($rand).substr($name, 0, 1).randomHash(3 * $rand).substr(strrev($name), 0, 1).randomHash(2 * $rand);
    
    $rest = str_split(substr($name, 1, strlen($name)-2));
    $str = "";
    $rev = "";
    for($i = 0; $i < count($rest); $i++)
    {
      if($i%2)$str .= $rest[$i].randomHash($rand);
      else $rev .= $rest[$i].randomHash($rand);
    }
    $f .= $str.strrev($rev).$i.":".$rand;
    return $u.":".$f;
  }
  
  private static function decrypt($str)
  {
    $pieces = explode(":", $str);
    $result['id'] = substr(substr($pieces[0], $pieces[1]*2, strlen($pieces[0])), 0, -$pieces[1]*3)/($pieces[1]*4);
    
    $fname = substr($pieces[3], $pieces[4], 1);
    $lname = substr($pieces[3], $pieces[4]*4+1, 1);
    $rest = str_split(substr($pieces[3], $pieces[4]*6+2), $pieces[4]+1);
    $str = "";
    $rev = "";
    
    for($i = 0; $i < count($rest)-1; $i++)
    {
      $char = $rest[$i];
      if($i >= floor($rest[count($rest)-1]/2))
        $rev .= substr(strrev($char), 0, 1);
      else $str .= substr($char, 0, 1);
    }
    $str = str_split($str.$rev);
    $temp = "";
    $c = count($str);
    for($i = 0; $i < $c; $i++)
    {
      if($i%2)$temp .= array_shift($str);
      else $temp .= array_pop($str);
    }
    
    $lnamelen = strlen($pieces[2]);
    $result['firstname'] = $fname.substr($temp, 0, $lnamelen-2);
    $result['lastname']  = $lname.strrev(substr($temp, -$lnamelen+1));
    return $result;
  }
}