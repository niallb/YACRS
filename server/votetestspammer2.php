<?php

$testdata = file('../essaytesttext.txt');

$yacrsbaseopts = array(
      "https://learn.gla.ac.uk/yacrs/votetest.php",
      "https://learn.gla.ac.uk/yacrs/votetestajax.php"
      );
if($_SERVER['SERVER_ADDR'] == '127.0.0.1')
{
    $yacrsbaseopts[] = "http://127.0.0.1/yacrs_gh/server/votetest.php";
    $yacrsbaseopts[] = "http://127.0.0.1/yacrs_gh/server/votetestajax.php";

}

$uid = sprintf("%04x", rand(0,65535));

if(!isset($_REQUEST['uid']))
{
   echo "<h1>$uid</h1>";
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


$qiid = 0;
	$html = file_get_contents("{$yacrsbase}?sessionID=2&testuser=vt0001");
	if(preg_match('/\[QIID:([0-9]+)\]/', $html, $matches))
	{
		$qiid = $matches[1];
	    echo $qiid;
	}
if($qiid == 0)
{
    header("Refresh:2");
    echo time();

    exit();
}

$n=0;
$qiid2 = $qiid;
while(($qiid2 == $qiid)&&($n < sizeof($testdata)))
{
	$ts = time();
	$unum = sprintf("%04u", $n);
	$html = file_get_contents("{$yacrsbase}?sessionID=2&testuser=vt{$uid}{$unum}");
    $ans = urlencode($testdata[$n]);
	$html = file_get_contents("{$yacrsbase}?sessionID=2&testuser=vt{$uid}{$unum}&submitans=1&qiID={$qiid}&Ans={$ans}");
    $te = time();
    $dt = $te-$ts;
	if(preg_match('/\[QIID:([0-9]+)\]/', $html, $matches))
	{
		$qiid2 = $matches[1];
	}
    else
    {
        $qiid2 = 0;
    }
    echo "vt{$uid}{$unum} : $dt<br/>";
    flush();
    $n++;
    set_time_limit (60);
}

//echo $html;

?>
