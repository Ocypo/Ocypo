<?php
class test
{
  public function __construct()
  {
    #Do something when the class has been called.
  }

  public function index()
  {
    echo "Passing arguments to view.";
  }
  
  public function testFunc($arg)
  {
    #Do lot's of fancy stuff with your argument here.
    $arg = $arg.' fancy stuff';
    $value = 'value';
    
    #Prepare the variables you want to pass to the view
    $arr = array('name'=>$value, 'arguments'=>$arg);
    
    #Load the view. VIEW::add(VIEW_NAME, ARGS);
    VIEW::add('json', $arr);
  }
}