<?php
class VIEW
{
  /**
   * add(<string> location, <array> arguments)
   * setPageTitle(<string> title)
   * useControllerIfEmpty(<bool>)
   * appendPageTitle(<bool>)
   * setPageTitleSeperator(<sting> seperator)
   * getPageTitle( void )
   * UpdateGlobals( <array> config )
   */

  private static $baseTitle = "";
  private static $pageTitle = "My Website!";
  private static $appendPageTitle = true;
  private static $pageTitleSeperator = " | ";
  private static $useControllerIfEmpty = true;

  public static function add($view, $args = array())
  {
    $view = @str_replace('.', '/', $view);

    if(file_exists('./views/'.$view.'.php'))
    {
      foreach($args as $k => $v) $$k = $v;
      include('./views/'.$view.'.php');
    }
    else
    {
      ERROR::generate(404, "View '$view' does not exist!!!");
    }
  }

#setters
  public static function setPageTitle($title)
  {
    self::$pageTitle = $title;
  }

  public static function useControllerIfEmpty($bool)
  {
    if(is_bool($bool)) self::$useControllerIfEmpty = $bool;
  }

  public static function appendPageTitle($bool)
  {
    if(is_bool($bool)) self::$appendPageTitle = $bool;
  }

  public static function setPageTitleSeperator($str)
  {
    self::$pageTitleSeperator = $str;
  }

#getters
  public static function getPageTitle()
  {
    $return = self::$baseTitle;
    if(self::$appendPageTitle === true)
    {
      if(self::$useControllerIfEmpty === true)
      {
        $uri = SITE::getURI();
        foreach($uri as $var)
        {
          $return .= self::$pageTitleSeperator . ucfirst($var);
        }
      }
      elseif(self::$pageTitle != '')
        $return .= self::$pageTitleSeperator . ucfirst(self::$pageTitle);
    }
    else
    {
      if(self::$useControllerIfEmpty === true)
      {
        $uri = SITE::getURI();
        foreach($uri as $var)
        {
          $return = self::$pageTitleSeperator . ucfirst($var);
        }
      }
      elseif(self::$pageTitle != '')
        $return = ucfirst(self::$pageTitle);
    }
    return $return;
  }

  public static function UpdateGlobals($config)
  {
    foreach($config as $var => $val)
    {
      self::$$var = $val;
    }
  }
}
?>