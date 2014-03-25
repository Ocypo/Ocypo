<?php
if(!function_exists('mysqli_init') && !extension_loaded('mysqli'))
  ERROR::generate(400, 'mysqli is not installed!!!');

abstract class database
{
  /**
   * debug(void)
   * get(<string> $databasename) return database object
   * addDB(<string> $name, <array> $connectDetails)
   * query(<string> $query)
   * fetch_all(<string> $query[, <bool> return first entry only[, <bool> print query]])
   * getAI(<string> $table)
   * escape(<string> $value)
   */

  private static $db = array();
  private static $called = array();

  public static function debug()
  {
    $calledClass = get_called_class();
    $var = strtoupper($calledClass);

    if($var !== false and isset(self::$db[$var])) echo print_r(self::$db[$var], true);
    else echo print_r(self::$db, true);
    if($var !== false and isset(self::$called[$var])) echo print_r(self::$called[$var], true);
    else echo print_r(self::$called, true);
  }

  public static function get($get = false)
  {
    $var = get_called_class();
    if($get !== false)
      $var = $get;
    $var = strtoupper($var);

    if($var == "DATABASE")
      ERROR::generate(0, "You can't run a query on the database class!" );
    elseif(!isset(self::$db[$var]))
      ERROR::generate(0, "Database '$var' could not be located.<br />Has it been setup properly?" );
    elseif(!isset(self::$called[$var]))
    {
      //ERROR::log("Added new database: $var");
      $db = self::$db[$var];
      @$database = new mysqli($db[0], $db[1], $db[2], $db[3], (isset($db[4])) ? $db[4] : 3306);
      if($database->connect_error)
        ERROR::generate(0, "Database '".$var."' Error (". $database->connect_errno .")<br /> ". $database->connect_error );
      else
        self::$called[$var] = $database;
    }

    return self::$called[$var];
  }

  public static function add($name, $conn)
  {
    self::$db[$name] = $conn;
  }

  public static function query($query, $die = false)
  {
    if($die === true) ERROR::generate(0, $query);
    $db = self::get();
    if($db) return $db->query($query);
    else return ;
  }

  public static function fetch_all($query, $return = true, $die = false)
  {
    if($die === true) ERROR::generate(0, $query);
    if($result = self::query($query))
    {
      $results = array();
      if($result->num_rows > 0)
      {
        while ($row = $result->fetch_assoc()) $results[] = $row;
      }
      $result->free();
      if(count($results)==0) return false;
      elseif(count($results)==1 and $return === true) return $results[0];
      else return $results;
    }
  }

  public static function getAI($tbl)
  {
    if($result = self::query("SHOW TABLE STATUS LIKE '".$tbl."'"))
    {
      $row = $result->fetch_array();
      $result->free();
      
      if($ai = $row['Auto_increment']) return $ai;
      else ERROR::generate(400, "No auto increment value found!");
    }
  }

  public static function escape($str)
  {
    $db = self::get();
    return $db->real_escape_string($str);
  }
}