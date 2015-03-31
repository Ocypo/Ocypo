<?php
if(!extension_loaded('PDO'))
  ERROR::generate(400, 'PDO is not installed!!!');

abstract class database
{
  /**
   * debug(void)
   * get(<string> $databasename) return database object
   * addDB(<string> $name, <array> $connectDetails)
   * query(<string> $query[, <mixed> argument, ..., [, <bool> return first return row only]])
   */

  private static $db = array();
  private static $called = array();
  private static $replaceCount = 0;

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
      //if debug then -> ERROR::log("Added new database: $var");
      $db = self::$db[$var];
      try {
        $engine = "mysql";
        $dsn = $engine.':dbname='.$db[3].";host=".$db[0].";port=".((isset($db[4])) ? $db[4] : 3306); 
        self::$called[$var] = new PDO($dsn, $db[1], $db[2]);
      } catch (PDOException $e) {
        ERROR::generate(0, "Database '".$var."' Error (". $e->getMessage .")");
      }
    }

    return self::$called[$var];
  }

  public static function add($name, $conn)
  {
    $name = strtoupper($name);
    self::$db[$name] = $conn;
  }

  private static function queryReplaceCallback($matches) {
    $return  = ":args".self::$replaceCount;
    self::$replaceCount++;
    return $return;
  }

  public static function query() //$query = string, $parms = array(), $returnOnlyFirstRow = false
  {
    $args = func_get_args();
    $return = false;
    $returnOnlyFirstRow = false;

    if(count($args) < 1) {
      throw new Exception("Not enough arguments", 1);
    }

    self::$replaceCount = 0;
    $query = array_shift($args);
    $query = preg_replace_callback('/(\'|")?(%d|%s)(\'|")?/', 'self::queryReplaceCallback', $query); //Sprintf like replace all %s and %d.
    if(count($args) == 0 && self::$replaceCount > 0) {
      throw new Exception("Trying to bind non-existent parameter!", 1);
    }

    $st = self::get()->prepare( $query );
    if(count($args) > self::$replaceCount) //We have additional arguments!
    {
      $stBind = array_slice($args, 0, self::$replaceCount);
      $numberOfAdditionalArguments = count($args) - self::$replaceCount;
      $additionalArguments = array_slice($args, -$numberOfAdditionalArguments, $numberOfAdditionalArguments);
      
      if(count($additionalArguments) == 1 && $additionalArguments[0] === true) {
        $returnOnlyFirstRow = true;
      }
    }
    else
      $stBind = $args; //Since there are no additional arguments, just pass the whole lot!

    if(count($stBind) > 0) {
      foreach ($stBind as $key => $value) {
        if(!is_int($value) && $value == NULL) {
          $value = (string) "";
          //throw new Exception("Trying to bind empty parameter!", 1);
        }

        $st->bindValue(":args".$key, $value);
      }
    }
    
    if(strstr(strtoupper($query), "INSERT")) {
      $return = $st->execute();
    }
    elseif(strstr(strtoupper($query), "UPDATE") || strstr(strtoupper($query), "DELETE")) {
      $st->execute();
      $return = ($st->rowCount() > 0) ? true : false;
    } else {
      if($st->execute()) {
        $return = $st->fetchAll(PDO::FETCH_CLASS);
        
        if(count($return)==1 and $returnOnlyFirstRow === true) $return = $return[0];
      }
    }
    //var_dump($st->errorInfo());
    return $return;
  }
}