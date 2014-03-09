<?php
class language
{
  public function index()
  {
    echo "How language files work.<br />";
    echo "Make sure you have created a language folder!";
  }

  public function setLang($lang = 'en')
  {
    if($lang) LANG::setLocale($lang);

    foreach(LANG::getLangFiles() as $lang)
        echo SITE::a('language/setLang/'.$lang, $lang).'<br/>';

    echo LANG::get("main.welcome", array('name'=>'Niels Meijer'));
  }

  public function choice($amountOfGames = 0)
  {
    if($amountOfGames == 0)
      $choice = 0;
    elseif($amountOfGames == 1)
      $choice = 1;
    else
      $choice = 2;

    echo LANG::choice("main.games", $choice, array("amount"=>$amountOfGames));
  }

  public function get($money = 10)
  {
    echo LANG::get("main.hello")."<br />";
    echo LANG::get("main.currency", array("amount"=>$money));
  }
}
?>