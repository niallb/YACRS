<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');

$uinfo = checkLoggedInUser();
$sessionID = requestInt('sessionID', 0);

$thisSession = $sessionID != 0 ? session::retrieve_session($sessionID):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(($thisSession==false)||(!checkPermission($uinfo, $thisSession)))
{
    header("Location: index.php");
    exit();
}

header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"chat_{$sessionID}.csv\";" );
header("Content-Transfer-Encoding: binary");

$messages = message::getSessionMessages($sessionID);

echo "Time, User, Displayed name, Message\r\n";
foreach($messages as $m)
{
    if($m->user_id > 0)
    {
		$user = sessionMember::retrieve_sessionMember($m->user_id);
    	echo date("c", $m->posted).','.$user->userID.',"'.preg_replace('/([\\\\"])/e', '\\\\\\1', $user->name).'","'.preg_replace('/([\\\\"])/', '\\\\\\1', $m->message).'"'."\r\n";
    }
    else
    {
    	echo date("c", $m->posted).', System,,"'.preg_replace('/([\\\\"])/','\\\\\\1', $m->message).'"'."\r\n";
    }
}

?>
