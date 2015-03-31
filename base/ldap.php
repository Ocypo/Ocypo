<?php
if(function_exists('ldap_connect'))
{
  class ldap
  {
    private static $db = array();
    private static $called = array();
    
    public static function get($get = false)
    {
      $var = get_called_class();
      if($get !== false)
        $var = $get;
      $var = strtoupper($var);

      if($var == "LDAP")
        ERROR::generate(0, "You can't run a query on the LDAP class!" );
      elseif(!isset(self::$db[$var]))
        ERROR::generate(0, "LDAP '$var' could not be located.<br />Has it been setup properly?" );
      elseif(!isset(self::$called[$var]))
      {
        
        $db = self::$db[$var];
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
        @$conn = ldap_connect("ldap://".$db[0]) or ERROR::generate(500, 'Internal server error!');
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        if(count($db) == 2) //anonymous login
          @$bind = ldap_bind($conn);
        elseif(count($db) == 4) {
          @$bind = ldap_bind($conn, $db[1], $db[2]);
        }else
          ERROR::generate(0, "LDAP configuration error.");
        
        if(!$bind)
          ERROR::generate(0, "LDAP '".$var."' Error (". ldap_error($conn) .")");
        else
          self::$called[$var] = $conn;
      }

      return self::$called[$var];
    }

    public static function add($name, $conn)
    {
      $name = strtoupper($name);
      if(count($conn) == 2 || count($conn) == 4)
        self::$db[$name] = $conn;
      else
        ERROR::generate(0, "LDAP configuration error.");
    }
    
    public static function search($filter, $attributes = false)
    {
      $db = self::get();
      if($db) {
        $var = get_called_class();
        $var = strtoupper($var);
        $var = self::$db[$var];
        $dn  = "";
        
        if(count($var) == 2) //anonymous login
          $dn = $var[1];
        elseif(count($var) == 4)
          $dn = $var[3];
        
        if(!$attributes)
          $search = ldap_search($db, $dn, $filter);
        else
          $search = ldap_search($db, $dn, $filter, $attributes);
        
        return ldap_get_entries($db, $search);
      }
      else return ;
    }
  }
}