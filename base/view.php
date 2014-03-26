<?php
class VIEW
{
  /**
   * add(<string> location, <array> arguments)
   * setTitle(<string> title) #overrides title
   * getTitle( void )
   */

  public static $websiteName = "";
  public static $displayFormat = 0;
  public static $seperator = ' | ';


  public static function add($view, $args = array())
  {
    $view = @str_replace('.', '/', $view);
    $path = substr($_SERVER['SCRIPT_FILENAME'], 0, -9).'views/'.$view.'.php'; #very strange fix to get the proper path..

    if(file_exists($path))
    {
      foreach($args as $k => $v) $$k = $v;
      include($path);
    }
    else
    {
      ERROR::generate(404, "View '$view' does not exist!!!");
    }
  }

#setters
  public static function setTitle($title)
  {
    self::$websiteName = $title;
    self::$displayFormat = 1;
  }

#getters
  public static function getTitle()
  {
    $return = '';
    $a = array(self::$websiteName, ucfirst(__CLASS), ucfirst(__FUNCTION));
    $dec = decbin(self::$displayFormat);
    if(strlen($dec) > count($a)) die("No");
    $bits = array_reverse(str_split($dec, 1));
    for($i = 0; $i < count($bits); $i++)
      if($bits[$i] == 1)
        $return .= $a[$i].self::$seperator;
    return substr($return, 0, -strlen(self::$seperator));
  }
}
?>