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

  public function href()
  {
    echo SITE::a('language', 'Go to language page.');
    echo "<br />";
    echo SITE::a('http://google.com', 'Open Google in a new page', array('target'=>'_blank'));
  }

  public function image()
  {
    echo SITE::img('logo.png');
    echo SITE::img('logo.png', 'img', array('style'=>'width: 64px;'));
  }

  public function redirect()
  {
    SITE::redirect("home/url");
  }

  public function base_url()
  {
    echo BASE . __CLASS . '/' . __FUNCTION;
  }

  public function asset()
  {
    echo htmlentities( SITE::asset("style.css") );
  }

  public function test()
  {
    self::base_url();
    echo "<br />";
    var_dump(input::get());
  }
}
?>