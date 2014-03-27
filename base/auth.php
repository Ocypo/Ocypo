<?php
class Auth
{
  /**
   * login(<string> Username, <string> Password )
   * getUser(void) returns current user object.
   * loggedin(void) returns boolean
   * requireLogin(<mixed> List[, <string> callback URL])
   * getSession(void)
   */

  protected static $id = 0;                 #User ID
  protected static $username = "Anonymous"; #Username
  protected static $accessLevel = 0;        #User access level, eg. #0 = banned, #1 = user, #2 = editor, #3 = admin
  protected static $salt = false;

  public static function login($username, $password)
  {
    #Override this function with your login method.
    #Check user + password in database.
    $id = 1;
    $username = 'Niels Meijer';
    $accessLevel = 3;
    self::setUser($id, $username, $accessLevel);
  }

  public static function logout()
  {
    COOKIE::remove('user');
    SESSION::remove('user');
  }

  public static function createHash($id, $username)
  {
    #possibility to add ip addr in hash by adding $_SERVER['REMOTE_ADDR']
    if(self::$salt !== false)
      return md5(self::$salt.$id.$username.self::$salt);
    else
      return false;
  }

  public static function setSalt($salt)
  {
    if(preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,30}$/', $salt))
      self::$salt = $salt;
    else
      ERROR::generate(400, 'Salt not secure enough!');
  }

  protected static function setUser($id, $username, $accessLevel)
  {
    self::$id = $id;
    self::$username = $username;
    self::$accessLevel = $accessLevel;

    $var = $id.':'.$username.':'.$accessLevel; #Uses default cookie encryption.
    SESSION::set('user', $var); #Save data server side.
    COOKIE::set('user', $var);  #Save data client side.
  }

  private static function getSession()
  {
    $user = COOKIE::get('user');
    $userInfo = explode(':', $user);
    #Right so now we need to check if the server and client data is the same
    if(SESSION::get('user') == $user)
    {
      self::setUser($userInfo[0], $userInfo[1], $userInfo[2]);
      return true;
    }
    return false;
  }

  public static function getUser()
  {
    if(self::loggedin())
      return (object) array('id'=>self::$id, 'username'=>self::$username, 'accessLevel'=>self::$accessLevel);
    else
      return false;
  }

  public static function loggedin()
  {
    if(self::$id == 0 or self::$username == "Anonymous")
      return self::getSession();
    else
      return true;
  }
}