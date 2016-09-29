<?php
abstract class BASEMODEL
{
  /**
   * get(<string> $databasename) return database object
   * set(<string> $modelName, <bool> $overWrite)->connectionType(<array> $connectionDetails);
   */

  private static $db = array();
  private static $called = array();
  private static $replaceCount = 0;

  public static function set($modelName, $overWrite = false)
  {
    $modelName = strtoupper($modelName);
    if(isset(self::$db[$modelName]) && $overWrite === false)
      ERROR::GENERATE(500, "Database name already exists!");

    $model = new datamodel_instance($modelName);
    self::$db[$modelName] = $model;
    return $model;
  }

  public static function get($modelName = false)
  {
    $var = get_called_class();
    if($modelName !== false)
      $var = $modelName;
    $var = strtoupper($var);
    if(!isset(self::$db[$var]))
      ERROR::GENERATE(0, "DATAMODEL '$var' could not be located.<br />Has it been setup properly?" );
    elseif(!isset(self::$called[$var]))
    {
      $model = self::$db[$var];
      try {
        switch(strtoupper($model->type)) {
          case "PDO":
            self::$called[$var] = new PDO($model->dsn, $model->username, $model->password);
            break;
          case "LDAP":
            ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
            $conn = ldap_connect($model->dsn);
            ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
            if($model->username == false || $model->password == false) //anonymous login
              $bind = ldap_bind($conn);
            else {
              $bind = ldap_bind($conn, $model->username, $model->password);
            }
            self::$called[$var] = $conn;
            break;
          default:
            ERROR::GENERATE(0, "Unknown modeltype!");
            break;
        }
        CONFIG::debug("Opened new database: '$var'.");
      }
      catch (Exception $e) {
        ERROR::GENERATE(0, "DATAMODEL '".$var."' Error (". $e->getMessage .")");
      }
    }
    return self::$called[$var];
  }

  /** disfunct function handler **/
  public static function __callStatic($function, $args)
  {
    $db = self::get();
    $type = (!is_resource($db)) ? get_class($db) : str_replace(" ", "_", get_resource_type($db));
    CONFIG::debug("Running function '$function' on dataModel: '". strtoupper(get_called_class()) ."', type: '$type'. With arguments: ", $args);

    return array(false);
  }

  /** pdo extensions **/
  private static function queryReplaceCallback($matches) {
    $return  = ":args".self::$replaceCount;
    self::$replaceCount++;
    return $return;
  }
  
  public static function lastInsertId()
  {
    return self::get()->lastInsertId();
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

    $db = self::get();
    $type = (!is_resource($db)) ? get_class($db) : str_replace(" ", "_", get_resource_type($db));
    CONFIG::debug("Running function 'query' on dataModel: '". strtoupper(get_called_class()) ."', type: '$type'. With arguments: ", $args);
    CONFIG::debug("Query: ".$query);

    $st = $db->prepare( $query );
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
        CONFIG::debug("Bind '". $value ."' on ':args".$key ."'.");
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

  /** ldap extentions **/
  public static function search($filter, $attributes = false, $cleanFormat = true)
  {
    $db = self::get();

    $type = (!is_resource($db)) ? get_class($db) : str_replace(" ", "_", get_resource_type($db));
    CONFIG::debug("Running function 'search' on dataModel: '". strtoupper(get_called_class()) ."', type: '$type'. With arguments: ", $args);

    if($db) {
      $var = get_called_class();
      $var = strtoupper($var);
      $model = self::$db[$var];
      $dn  = $model->dn;
      
      if(!$attributes)
        $search = ldap_search($db, $dn, $filter);
      else
        $search = ldap_search($db, $dn, $filter, $attributes);
      
      $ldapResult = ldap_get_entries($db, $search);
      if($cleanFormat)
        $ldapResult = self::cleanFormat($ldapResult);
      return $ldapResult;
    }
    else return ;
  }
  
  private function cleanFormat($ldapResult)
  {
    $numRows = array_shift($ldapResult);
    $return = array();
    $row = 0;
    
    foreach($ldapResult as $result) {
      
      foreach($result as $key => $value) {
        if(!is_integer($key) and $key != "count" and $key != "dn") {
          $value = (isset($value[0]) && count($value) == 2) ? $value[0] : $value;
          $return[$row][$key] = $value;
        }
      }
      $row++;
    }
    
    return $return;
  }
}

class datamodel_instance
{
  public $name;
  public $dsn;
  public $username;
  public $password;
  public $type;

  public function __construct($name)
  {
    $this->name = $name;
  }

  public function mysql($DB_HOST, $DB_USER = 'root', $DB_PASS = false, $DB_NAME = false, $DB_PORT = 3306)
  {
    if(!extension_loaded('PDO'))
      ERROR::generate(400, "Cannot use PDO. PHP extension is not installed or enabled!");
    $this->type = "PDO";
    $this->dsn = 'mysql:dbname='.$DB_NAME.";host=".$DB_HOST.";port=".$DB_PORT;
    $this->username = $DB_USER;
    $this->password = $DB_PASS;
  }

  public function odbc($DB_HOST, $DB_NAME = false, $DB_PORT = 1433)
  {
    if(!extension_loaded('PDO'))
      ERROR::generate(400, "Cannot use PDO. PHP extension is not installed or enabled!");
    $this->type = "PDO";
    $this->dsn = 'odbc:DRIVER=FreeTDS;dbname='.$DB_NAME.";host=".$DB_HOST.";port=".$DB_PORT;
  }

  public function pgsql($DB_HOST, $DB_USER = 'root', $DB_PASS = false, $DB_NAME = false, $DB_PORT = 5432)
  {
    if(!extension_loaded('PDO'))
      ERROR::generate(400, "Cannot use PDO. PHP extension is not installed or enabled!");
    $this->type = "PDO";
    $this->dsn = 'pgsql:dbname='.$DB_NAME.";host=".$DB_HOST.";port=".$DB_PORT;
    $this->username = $DB_USER;
    $this->password = $DB_PASS;
  }

  public function sqlite($DB_NAME)
  {
    if(!extension_loaded('PDO'))
      ERROR::generate(400, "Cannot use PDO. PHP extension is not installed or enabled!");
    if(strpos($DB_NAME, '.db') === false)
      ERROR::GENERATE(0, "SQLite3 database must have a proper name. (eg: database.db)");
    $this->type = "PDO";
    $this->dsn = 'sqlite:'.$DB_NAME;
  }

  public function ldap($LDAP_HOST, $LDAP_USER = false, $LDAP_PASS = false, $LDAP_DN = "", $LDAP_PORT = 389)
  {
    if(!function_exists('ldap_connect')) {
      ERROR::GENERATE(400, "Cannot use LDAP, PHP extension is not installed or enabled!");
    }
    $this->type = "LDAP";
    $this->dsn = "ldap://".$LDAP_HOST;
    $this->username = $LDAP_USER;
    $this->password = $LDAP_PASS;
    $this->dn = $LDAP_DN;
  }
}