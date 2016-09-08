<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');

$uinfo = checkLoggedInUser();

$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}

if($thisSession->questionMode == 0)
{
    $thisSession->currentQuestion = 0;
    $thisSession->questionMode = 1;
}
else
{
    $thisSession->extras[currentQuestions] = array();
    $thisSession->questionMode = 0;
}
$thisSession->update();

header("Location: runsession.php?sessionID={$thisSession->id}");
echo "<a href='runsession.php?sessionID={$thisSession->id}'>Continue</a>";




