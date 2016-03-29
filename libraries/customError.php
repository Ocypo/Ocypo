<?php
class customError
{
  public static function __404($message = "unknown")
  {
    echo "Custom 404 error with error message: ".$message;
  }
}