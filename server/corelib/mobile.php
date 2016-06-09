<?php
$deviceType = 'unknown';
if(requestSet('mode'))
{
   $deviceType = requestIdent('mode');
   setcookie($CFG['appname'].'_mode',$deviceType, time()+60*60*24*30, '', '', false, true);
}
elseif(isset($_COOKIE[$CFG['appname'].'_mode']))
{
   $deviceType = $_COOKIE[$CFG['appname'].'_mode'];
   setcookie($CFG['appname'].'_mode',$deviceType, time()+60*60*24*30, '', '', false, true);
}
else
{
	require_once('Mobile_Detect.php');
	$detect = new Mobile_Detect;
	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'computer');
	setcookie($CFG['appname'].'_mode',$deviceType, time()+60*60*24*30, '', '', false, true);
    //echo "<h1>$deviceType</h1>";
}

if((isset($MOBILETEMPLATE))&&($deviceType=='mobile'))
    $TEMPLATE = $MOBILETEMPLATE;

