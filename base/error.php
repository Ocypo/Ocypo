<?php
error_reporting(E_ALL);
set_error_handler("error::errorHandler");
set_exception_handler("error::exceptionHandler");
register_shutdown_function("error::fatalHandler");

abstract class error
{
  /**
   * log(<string>)
   * errorHandler(<int> error number, <string> message, <string> file location, <int> line number)
   * exceptionHandler(<exception> Exception)
   * generate(<int> error number, <string> message)
   */

  public static $log = true;
  public static $debug = false;
  public static $exclude = array();

  public static function fatalHandler()
  {
    if($error = error_get_last())
      self::errorHandler(E_CORE_ERROR, $error["message"], $error["file"], $error["line"]);
  }

  public static function log($log, $i = 0)
  {
    error_log($log, $i);
  }

  public static function errorHandler($errno, $errstr, $errfile, $errline)
  {
    if(error_reporting() && $errno)
    {
      $exit = false;
      switch( $errno )
      {
        case E_USER_ERROR:
          $type = 'Fatal Error';
          $exit = true;
        break;
        case E_USER_WARNING:
        case E_WARNING:
          $type = 'Warning';
        break;
        case E_USER_NOTICE:
        case E_NOTICE:
        case @E_STRICT:
          $type = 'Notice';
        break;
        case @E_RECOVERABLE_ERROR:
          $type = 'Catchable';
        break;
        case @E_USER_DEPRECATED:
        case @E_DEPRECATED:
          $type = 'Deprecated';
        break;
        default:
          $type = 'Unknown Error';
          $exit = true;
        break;
      }

      $exception = new \ErrorException($errstr, 0, $errno, $errfile, $errline);
      if($exit)
      {
        self::exceptionHandler($exception);
        exit();
      }
      else
      {
        if(!in_array($errno, self::$exclude))
        {
          throw $exception;
        }
      }
    }
    else
    {
      $log = 'Error: '.$errstr.'. File: '.$errfile.' on line: '.$errline.'.';
      self::log($log);
    }

    // Don't execute PHP internal error handler
    return true;
  }

  public static function exceptionHandler($exception)
  {
    if(ob_get_contents()) ob_end_clean();
    $log = 'Error: '.$exception->getMessage().'. File: '.$exception->getFile().' on line: '.$exception->getLine().'.';
    if(self::$debug)$log .= "\n".$exception->getTraceAsString();
    $log .= "\r\n";
    if(self::$log === true and ini_get('log_errors'))
      self::log($log, 0);

    $errorTrace = $exception->getTrace();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title>Whoops! We've found an error...</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="copyright" content="NielsMeijer.eu" />
</head>
<body>
<style type="text/css">
* {
  margin: 0;
  padding: 0;
}
body {
  min-width: 1000px;
}
h3 {
  color: #FF8500;
  padding: 2px 0 0 2px;
  border-bottom: 1px solid #CECECE;
  font-family: Arial;
}
#trace {
  width: 30%;
  height: 100%;
  overflow-y: auto;
  background-color: #ffffff;
  position: absolute;
  left: 0;
  background-color: #E9E9E9;
  font-family: Arial;
}
#trace li {
  background-color: #FFF;
  padding: 20px;
  font-size: 12px;
  border-left: 2px solid #E9E9E9;
  border-bottom: 1px solid #E9E9E9;
}
#trace li:hover {
  background-color: #FF8500;
  cursor: pointer;
}
#info {
  width: 70%;
  position: absolute;
  right: 0;
}
#title {
  background-color: #E9E9E9;
  border-left: 5px solid #FF3D00;
  padding: 10px;
  color: #535353;
  font-weight: 700;
  font-size: 22px;
  font-family: Arial;
}
#code, #code div, #code {
  font-family: Consolas, Menlo, Monaco, Lucida Console, Liberation Mono, DejaVu Sans Mono, Bitstream Vera Sans Mono, Courier New, monospace, serif;
}
#code {
  border-left: 5px solid #FFEB00;
  padding: 20px;
  background-color: #ffffff;
}
#code table {
  padding: 4px 0 4px 4px;
  background-color: #2C2C2C;
  color: #DADADA;
}
.line {
  color: #949494;
  width: 50px;
  text-align: right;
  padding: 0 5px 0 0;
}
table tr td {
  padding-left: 4px;
}
.highlight {
  background-color: #474747;
}
.preHighlight {
  background-color: #383838;
}
#comments {
  padding: 8px;
  background-color: #444444;
  color: #C0C0C0;
  font-size: 12px;
}
#head {
  padding: 8px;
  background-color: #FF3D00;
  font-size: 12px;
  color: #fff;
}
#head p {
  display: inline;
  background-color: #1F1F1F;
  padding: 1px 5px;
  margin-right: 5px;
  font-family: Arial;
}
#data {
  border-left: 5px solid #D1D1D1;
  background-color: #FAFAFA;
  padding: 20px;
  font-family: Arial;
}
table {
  border-spacing: 0px;
  width: 100%;
  font-size: 14px;
}
#data table {
  padding-top: 10px;
}
.type {
  color: black;
  width: 250px;
}
.value {
  color: #999;
  font-style: italic;
}
.even {
  background-color: #E9E9E9;
}
</style>
  <ul id="trace">
    <h3>Error Exception Trace</h3>
<?php
$first = array_shift($errorTrace);
$fileLocation = $exception->getFile();
$fileLine = $exception->getLine();
if(!isset($first["args"][2]))
{
  $fileLocation = $first["file"];
  $fileLine = $first["line"];
}
echo "<li>";
  echo 'File: '.$fileLocation.' ('.$first["line"].')<br/>';
  echo '<b>'.$first["args"][1].'</b>';
echo "</li>";

foreach($errorTrace as $trace)
{
  echo "<li>";
    echo (isset($trace["file"])) ? 'File: '.$trace["file"].' ('.$trace["line"].')<br/>' : '';
    echo "Function: ";
    if(isset($trace["class"])) echo $trace["class"];
    if(isset($trace["type"])) echo $trace["type"];
    if(isset($trace["function"])) echo $trace["function"];
    echo '<div style="margin-top: 10px;">'.print_r($trace, true).'</div>';
  echo "</li>";
}
echo "
  </ul>
  <div id=\"info\">
    <div id=\"title\">".$exception->getMessage()."</div>";
/* error code */
if($fileLocation)
{
  //$fileLocation = $errorTrace[0]['file'];
  $file = file($fileLocation);
  //$totalLines = count($file);
  echo '<div id="code">';
  echo '<div id="head">';
  echo '<p>File:</p>';
  echo $fileLocation;
  echo '</div>';
  echo '<table id="adsfasdf">';
  $from = $fileLine - 7;
  $to   = $fileLine + 3;

  if($from < 1) $from = 1;
  for($i=$from; $i<$to; $i++)
  {
    $class = '';
    if($i+1 == $fileLine) $class = ' class="preHighlight"';
    if($i   == $fileLine) $class = ' class="highlight"';
    if($i-1 == $fileLine) $class = ' class="preHighlight"';
    echo '<tr><td class="line">'.$i.'.</td><td'.$class.'>'.htmlspecialchars(@$file[$i-1]).'</td></tr>';
  }
  echo '</table>';
  $comment = 'No comments';
  if(substr($exception->getMessage(), 8, 25) == 'Use of undefined constant')
  {
    $comment = 'Did you forget an "quote" somewhere?';
  }
  echo '<div id="comments">'.$comment.'</div>';
  echo '</div>';
}
/* request data */
echo '<div id="data"><h3>Server/Request Data</h3><table>';
$even = false;
$data = array_merge($_GET, $_POST, $_SERVER);
foreach($data as $type => $value)
{
  if(is_array($value))$value = implode(', ', $value);
  echo '<tr'.($even ? ' class="even"' : '').'><td class="type">'.$type.'</td><td class="value">'.$value.'</td></tr>';
  $even = !$even;
}
?>
      </table>
    </div>
  </div>
</body>
</html>
<?php
exit();
  }

  public static function generate($code = 404, $text = "")
  {
    if(ob_get_contents()) ob_end_clean();
    $code = (is_integer($code)) ? $code : 404;
    $cmess = array(
      '300'=>'Multiple Choices',
      '301'=>'Moved Permanently',
      '307'=>'Temporary Redirect',
      '400'=>'Bad Request',
      '401'=>'Unauthorized',
      '403'=>'Forbidden',
      '404'=>'Not Found',
      '405'=>'Method Not Allowed',
      '406'=>'Not Acceptable',
      '409'=>'Conflict'
    );
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>ERROR</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="copyright" content="NielsMeijer.eu" />
<style type="text/css">
* {
  margin: 0;
  padding: 0;
}
body {
  background-color: #e9e9e9;
}
#container {
  width: 1000px;
  margin: 100px auto 0 auto;
  color: #212121;
  text-align: center;
}
h1 {
  font-size: 82px;
  font-weight: normal;
  margin-bottom: 0px;
}
h2 {
  font-size: 32px;
  font-weight: normal;
  margin-top: 50px;
  margin-bottom: 0px;
}
p {
  font-size: 32px;
  margin-top: -20px;
  text-align: right;
  width: 700px;
}
#footer {
  width: 1000px;
  height: 29px;
  margin-left: -500px;
  margin-bottom: 20px;
  text-align: center;
  font-size: 10px;
  font-family: Verdana;
  color: #888888;
  position: absolute;
  bottom: 0;
  left: 50%;
}
</style>
  </head>
  <body>
    <div id="container">
<?php if($code > 0) echo '<h1>ERROR '.$code.'</h1><p>'.$cmess[$code].'</p>'; ?>
      <h2><?php echo $text; ?></h2>
    </div>
  <div id="footer">
    All rights reserved | <a href="http://nielsmeijer.eu">NielsMeijer</a>
  </div>
  </body>
</html>
    <?php
exit();
  }
}