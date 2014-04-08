<?php
class route
{
  private static $routes = array();
  private static $class = false;
  private static $function = false;

  public static function add($from, $to)
  {
    if(strpos($from, '@') !== false) $from = explode('@', $from);
    $to   = explode('@', $to);

    self::$routes[] = array('from'=>$from, 'to'=>$to);
  }

  public static function getClass()
  {
    return self::$class;
  }

  public static function getFunction()
  {
    return self::$function;
  }

  public static function check($class, $function)
  {
    self::$class = $class;
    self::$function = $function;
    foreach(self::$routes as $arr)
    {
      if($arr['from'] == $class or @$arr['from'][0] == $class and @$arr['from'][1] == $function)
      {
        self::$class = $arr['to'][0];
        if(count($arr['to']) == 2)
          self::$function = $arr['to'][1];
      }
    }
  }
}