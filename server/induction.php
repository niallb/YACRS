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
?>
<html><head><title></title></head><body>
<?php

$quids = array();
if(($thisSession->questionMode == 0)&&($thisSession->currentQuestion != 0))
    $quids[] = $thisSession->currentQuestion;
if((isset($thisSession->extras[currentQuestions]))&&(is_array($thisSession->extras[currentQuestions]))&&(sizeof($thisSession->extras[currentQuestions]) >= 0))
    $quids = $thisSession->extras[currentQuestions];

$scores = array();
foreach($quids as $qiID)
{
    $qi = questionInstance::retrieve_questionInstance($qiID);
   // echo '<pre>'.print_r($qi, 1).'</pre>';
	$responses = response::retrieve_response_matching('question_id', $qi->id);
	if($responses)
    {
        $tot = 0;
        foreach($responses as $r)
        {
            $tot += 6 - intval(substr($r->value, 1));
        }
        $scores[$qi->title] = $tot/sizeof($responses);
    }
    else
        $scores[$qi->title] = 0;
}
arsort($scores);

foreach($scores as $k=>$v)
{
    echo "$k : ".sprintf("%0.2f", $v)."<br/>";
}


?>
</body></html>
