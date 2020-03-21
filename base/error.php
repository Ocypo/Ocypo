<?php
error_reporting(E_ALL);
set_error_handler("ERR::errorHandler");
set_exception_handler("ERR::exceptionHandler");
register_shutdown_function("ERR::fatalHandler");

abstract class ERR
{
  /**
   * log(<string>)
   * errorHandler(<int> error number, <string> message, <string> file location, <int> line number)
   * exceptionHandler(<exception> Exception)
   * generate(<int> error number, <string> message)
   */

  public static $log = true;
  public static $customPages = false;
  public static $exclude = array();

  public static function fatalHandler()
  {
    if($error = error_get_last())
      self::errorHandler(E_CORE_ERROR, $error["message"], $error["file"], $error["line"]);
  }

  public static function log($log, $i = 0)
  {
    if(self::$log === true and ini_get('log_errors'))
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
      $log = '1Error: '.$errstr.'. File: '.$errfile.' on line: '.$errline.'.';
      self::log($log);
    }

    // Don't execute PHP internal error handler
    return true;
  }

  public static function exceptionHandler($exception)
  {
    if(ob_get_contents()) ob_end_clean();
    $log = 'Error: '.$exception->getMessage().'. File: '.$exception->getFile().' on line: '.$exception->getLine().'.';
    $log .= "\n".$exception->getTraceAsString();
    $log .= "\r\n";
    self::log($log);

    if(isset(CONFIG::$debug) && CONFIG::$debug) {
      self::displayDebugError($exception);
    }
    else {
      self::generate(500, "Contact the site administrator.");
    }
  }

  public static function displayDebugError($exception)
  {
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
$fileLocation = $exception->getFile();
$fileLine = $exception->getLine();
$first = array_shift($errorTrace);
if(!isset($first["args"][2]))
{
  $fileLocation = $first["file"];
}
  echo "<li>";
    echo 'File: '.$fileLocation.' ('.$fileLine.')<br/>';
    echo '<b>'.(isset($first["args"]) && isset($first["args"][1]) ? $first["args"][1] : "").'</b>';
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
$_CUSTOM = array(
  'OCYPO_BASE' => (class_exists("CONFIG") ? CONFIG::getBase() : "Unknown"),
  'OCYPO_VERSION' => 'v'.(class_exists("CONFIG") ? CONFIG::getVersion() : "Unknown"),
  'MEMORY_USAGE' => (memory_get_peak_usage(true) /1024 ).' KB',
  'EXECUTION_TIME' => (round((microtime(true) - TIME) * 1000, 2)).' MS',
  );
$data = array_merge($_CUSTOM, $_GET, $_POST, $_SERVER);
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

    #Check if a custom error page has been set
    if(self::$customPages !== false and method_exists(self::$customPages, '__'.$code)) {
      call_user_func(array(self::$customPages, '__'.$code), $text);
    }
    else {
      ob_end_flush();
      flush();
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
      '409'=>'Conflict',
      '500'=>'Internal Server Error'
    );
    $backgroundColor = "hsl(".rand(0,359).",55%,55%)";
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
  color: rgb(210, 210, 210);
}
body {
  background-color: <?php echo $backgroundColor;?>;
}
.wrapper {
  position: absolute;
  left: 50%;
  margin-left: -500px;
  width: 1000px;
}
.container {
  padding: 100px 0;
  text-align: center;
  border: 1px solid #03A9F4;
}
.whitebg {
  margin-top: 100px;
  background: rgba(50,50,50,1) url('data:image/png;base64,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') repeat-y scroll left top;

}
h1, h2 {
  font-weight: normal;
  margin-bottom: 0px;
}
h1 {
  font-size: 82px;
}
h2 {
  font-size: 32px;
  margin-top: 50px;
}
.container p {
  font-size: 32px;
  margin-top: -20px;
  text-align: right;
  width: 700px;
}
#footer {
  width: 100%;
  height: 39px;
  padding-bottom: 6px;
  position: absolute;
  bottom: 0;
  background-color: rgba(50,50,50,1);
  border-top: 4px solid #03A9F4;
}
#footer p {
  font-size: 10px;
  font-family: Verdana;
  line-height: 39px;
  text-align: center;
}
a {
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
</style>
  </head>
  <body>
    <div class="wrapper whitebg">
      <div class="container">
<?php if($code > 0) echo '<h1>ERROR '.$code.'</h1><p>'.$cmess[$code].'</p>'; ?>
        <?php if($text) echo '<h2>'.$text.'</h2>'; ?>
      </div>
    </div>
    <div id="footer">
      <div class="wrapper">
        <p>All rights reserved | <a href="http://nielsmeijer.eu/" target="_blank" alt="NielsMeijer.eu">NielsMeijer.eu</a></p>
      </div>
    </div>
  </body>
</html>
    <?php
    }
  exit();
  }
}