<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
 
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';
$template->pageData['breadcrumb'] .= '| Run a session';


$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
$confirm = requestInt('confirm', 0);
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}
elseif($confirm == 1)
{
    session::deleteSession($thisSession->id);
    $template->pageData['mainBody'] = "<h1 style='text-align:center;'>Session ID {$thisSession->id} has been deleted.</h1>";
    $template->pageData['mainBody'] .= "<p><a href='index.php'>Continue</a></p>";
}
else
{
    $template->pageData['mainBody'] = "<h1 style='text-align:center;'>Session ID: {$thisSession->id}</h1>";
    $userCount = sessionMember::count("session_id", $thisSession->id);
    $activeCount = sessionMember::countActive($thisSession->id);
    $template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}'>Active users (total users): $activeCount ($userCount)</a></p>";
    $template->pageData['mainBody'] .= "<p>Deleting a session is not reversible, and all data relating to this session (questions and user answers) will be lost. Are you sure you want to continue?</p>";
    $template->pageData['mainBody'] .= "<p><a href='confirmdelete.php?sessionID={$thisSession->id}&confirm=1'>Yes, delete this session</a> | <a href='index.php'>Cancel</a></p>";

	$template->pageData['logoutLink'] = loginBox($uinfo);
}

echo $template->render();



?>
