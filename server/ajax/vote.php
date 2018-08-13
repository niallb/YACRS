<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');
require_once('../lib/database.php');
require_once('../lib/forms.php');
require_once('../lib/questionTypes.php');
require_once('../lib/vote_lib.php');

$uinfo = checkLoggedInUser();
$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
$smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);

$cqiid = 0;
if(($thisSession->questionMode == 0)&&($thisSession->currentQuestion != 0))
{
    $cqiid = $thisSession->currentQuestion;
    $forceTitle = false;
}
elseif((isset($thisSession->extras[currentQuestions]))&&(is_array($thisSession->extras[currentQuestions]))&&(sizeof($thisSession->extras[currentQuestions]) > 0))
{
    if(isset($_REQUEST['qiID']))
        $cqiid = intval($_REQUEST['qiID']);
    else
        $cqiid = $thisSession->extras[currentQuestions][0];
    $forceTitle = true;
}

$qi = questionInstance::retrieve_questionInstance($cqiid);
$qu = question::retrieve_question($qi->theQuestion_id);
$resp = response::retrieve($smemb->id, $qi->id);

if((isset($_REQUEST['submitans']))&&(isset($_REQUEST['qiID']))&&($_REQUEST['qiID']!==$qi->id))
    echo json_encode(array('questionBlock'=>"<div class='alert alert-danger'>Sorry, your answer was submitted after the question closed, so has been ignored.</div>"));
else
    echo json_encode(array('questionBlock'=>displayQuestion($qi, $resp, $forceTitle)));





//print_r($uinfo);
//print_r($thisSession);
//print_r($_REQUEST);

