<?php
class INPUT
{
  /**
   * get([<string> variable name])
   * post([<string> variable name])
   */
  
  public static function get($get = false)
  {
    $return = false;
    if($get)
    {
      $get = SANITIZE::string($_GET[$get]);
      if(!empty($get)) $return = $get;
    }
    else
    {
      $get = $_GET;
      foreach($get as $key => $value)
        $get[$key] = SANITIZE::string($value);

      $return = $get;
    }
    return $return;
  }

  public static function post($post = false)
  {
    $return = false;
    if($post)
    {
      $post = SANITIZE::string($_POST[$post]);
      if(!empty($post)) $return = $post;
    }
    else
    {
      $post = $_POST;
      foreach($post as $key => $value)
        $post[$key] = SANITIZE::string($value);

      $return = $post;
    }
    return $return;
  }
}