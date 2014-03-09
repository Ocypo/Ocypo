<?php
function dd($args)
{
  $args = func_get_args();
  $q = $args[0];
  if(!isset($q))die("Variable does not exist.");
  if(is_bool($q) === true and $q === true) $q = "true";
  if(is_bool($q) === true and $q === false) $q = "false";
  if(@$args[0] = get_class_methods($q)) call_user_func_array('dd', $args);

  if(count($args) == 2 and $args[1] === true)
    return print_r($q, true);

  if(is_array($q))
    die(var_dump($q));
  else
    die('> '.$q);
}

function randomHash($ln = 10)
{
  $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $str = '';
  for ($i = 0; $i < $ln; $i++) {
    $str .= $char[rand(0, strlen($char) - 1)];
  }
  return $str;
}
?>