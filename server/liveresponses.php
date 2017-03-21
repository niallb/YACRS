<html>

<head>
  <title></title>
</head>

<body>
<style>
body {
	font-family: "Verdana", "Helvetica", "Arial", sans-serif;
	font-size: 1.2em;
	padding: 20px;
}

.comment, .info {
	clear: both;
	max-width: 70%;
	margin-bottom: 20px;
}

.comment .meta, .info .meta {
	clear: both;
	margin-left: 10px;
	color: #777;
	font-size: 0.85em;
	margin-bottom: 0;
}

.meta .time {
	margin-left: 0.5em;
}

.meta .time:before {
	content: "\00b7";
	padding-right: 0.5em;
}

.comment .bubble, .info .bubble {
	position: relative;
	width: 100%;
	background-color: #eee;
	padding: 8px;
	border-radius: 8px;
	margin-left: 10px;
	float: left;
	margin-bottom: 5px;
}

.comment .bubble:after {
  content:"";
  position:absolute;
  bottom: 8px;
  left: -10px;
  top: auto;
  border-width:7px 10px 2px 0; /* vary these values to change the angle of the vertex */
  border-style:solid;
  border-color: transparent #eee;
}

.comment.me .bubble {
	background-color: #CCEC8C;
	margin-left: 0;
	margin-right: 10px;
}

.info .bubble {
	background-color: #AED7ED;
}

.info .bubble:after {
  border-color: transparent #AED7ED;
  content:"";
  position:absolute;
  bottom: 8px;
  left: -10px;
  top: auto;
  border-width:7px 10px 2px 0; /* vary these values to change the angle of the vertex */
  border-style:solid;
}

.comment.me .bubble:after {
  border-color: transparent #CCEC8C;
}

.comment.me {
	float: right;
	margin-right: 25px;
}

.comment.me .meta {
	text-align: right;
}

.comment.me .bubble:after {
  content:"";
  position:absolute;
  bottom: 8px;
  left: auto;
  right: -10px;
  top: auto;
  border-width: 7px 0 2px 10px; /* vary these values to change the angle of the vertex */
  border-style:solid;
  border-color: transparent #CCEC8C;
}

.session-header {
	background-color: #eee;
	border: 1px solid #ddd;
	padding: 10px;
	overflow: hidden;
}

.qr {
	float: right;
	width: 200px;
	height: 200px;
}

h1 {
	font-size: 2em;
	font-weight: normal;
	margin: 0;
	margin-bottom: 20px;
}

h2 {
	font-size: 1.5em;
	font-weight: normal;
	margin: 0;
}

</style>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
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
	if(!checkPermission($uinfo, $thisSession))
	{
	    header("Location: index.php");
	    exit();
	}
    echo "<div id='messages''></div>";
	echo getAJAXScript($thisSession->id);
}

function getAJAXScript($sessionID)
{
	return getLiveResponseUpdateAJAXScript($sessionID);
}


?>
</body>

</html>
