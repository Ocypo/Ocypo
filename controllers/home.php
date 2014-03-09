<?php
class home
{
  public function index()
  {
    #Prepare the variables you want to pass to the view
    $args = array( "text" => "This is the homepage!");
    
    #Load the view. VIEW::add(VIEW_NAME, ARGS);
    VIEW::add('home', $args);
  }
}
?>