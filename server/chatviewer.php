<html>

<head>
  <title></title>
</head>

<body>
<style>
.mupost{
	margin-bottom: 8px;
	margin-top: 8px;
	border: 0;
	font-size : large;
}

.muname{
	font-family : Arial, Helvetica, sans-serif;
	font-weight : bolder;
    color: Gray;
	width: 10%;
}
.muinfo{
	font-family : Arial, Helvetica, sans-serif;
	font-weight : bold;
	color: Green;
	width: 10%;
}
.mumsg{
	font-family : Arial, Helvetica, sans-serif;
}
</style>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/ajax.php');

$uinfo = checkLoggedInUser();

if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
	$serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
	$serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];
$serverURL = substr($serverURL, 0, strrpos($serverURL, '/')+1);

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
	$thisSession = requestSet('sessionID')? session::retrieve_session(requestInt('sessionID')):false;
    if($thisSession == false)
    {
        echo "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        $joinURL = "{$serverURL}join.php?sessionID={$thisSession->id}";
        echo "<img align='right' src='qrimg.php?url={$joinURL}'/>";
        echo "<h1>{$thisSession->title}</h1>";
        echo "<h2>$joinURL</h2>";
        echo "<div id='messages' style='border : 1px solid #00008B;'></div>";
    }
	echo getAJAXScript($thisSession->id);
}

function getAJAXScript($sessionID)
{
	return getUBlogUpdateAJAXScript($sessionID);
}


?>
</body>

</html>
