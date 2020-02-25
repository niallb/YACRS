<?php

$yacrsbaseopts = array(
      "https://classresponse.gla.ac.uk/html/bootstrap.css"
      );
if($_SERVER['SERVER_ADDR'] == '127.0.0.1')
{
    $yacrsbaseopts[] = "http://127.0.0.1/yacrs_gh/server/html/bootstrap.css";
}

$uid = sprintf("%04x", rand(0,65535));

if(!isset($_REQUEST['uid']))
{
   echo "<h1>$uid</h1>";
   echo strftime("%Y-%d-%m %H:%M:%S", time());
   foreach($yacrsbaseopts as $ybo)
   {
       echo "<p><a href='?uid=$uid&url=".urlencode($ybo)."'>$ybo</a></p>";
   }
    exit();
}
else
{
	$uid = $_REQUEST['uid'];
    $yacrsbase = $_REQUEST['url'];
}

    header("Refresh:10");
    //echo time();

    //exit();

    if(file_exists('mylog.txt'))
        $log = file_get_contents('mylog.txt');
    else
    	$log = '';
    $st = time();
	$ts = microtime();
    $cd = md5($ts . rand(0,1000));
 	$css = file_get_contents("{$yacrsbase}?asdas=$cd");
    $te = microtime();
    $dt = $te-$ts;
    echo strftime("%Y-%d-%m %H:%M:%S", $st) . " : $dt<br/>";
    $log .=  strftime("%Y-%d-%m %H:%M:%S", $st) . " : $dt\r\n";
    file_put_contents('mylog.txt', $log);
    flush();

   // set_time_limit (60);


//echo $html;

?>
