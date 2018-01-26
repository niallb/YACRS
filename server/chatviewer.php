<html>

<head>
  <title>Chat View</title>
  <link rel="stylesheet" type="text/css" href="html/yacrs-chat.css" />
  <link rel="stylesheet" type="text/css" href="html/yacrs-chat-theme.css" />
</head>

<body>

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
        echo "<div class='session-header'>";
        echo "<img class='qr' src='qrimg.php?url={$joinURL}'/>";
        echo "<h1>{$thisSession->title}</h1>";
        echo "<h2>$joinURL</h2></div>";
        echo "<div id='messages''></div>";
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
