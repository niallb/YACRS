<?php
/*****************************************************************************
YACRS Copyright 2013-2015, The University of Glasgow.
Written by Niall S F Barr (niall.barr@glasgow.ac.uk, niall@nbsoftware.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*****************************************************************************/
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');
include_once('lib/lti_funcs.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=mobile'>Use mobile mode</a>";

$loginError = '';
$uinfo = checkLoggedInUser(true, $loginError);
$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
if(($uinfo==false)||($thisSession==false)||(!$thisSession->extras['allowFullReview']))
{
    header("Location: index.php");
    exit();
}
$smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);

if((isset($thisSession->extras['customScoring']))&&(file_exists('locallib/customscoring/'.$thisSession->extras['customScoring'])))
{
    include_once('locallib/customscoring/'.$thisSession->extras['customScoring']);
}

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';
$template->pageData['breadcrumb'] .= '| <a href="vote.php?sessionID='.$thisSession->id.'">'.$thisSession->id.'</a>';
$template->pageData['breadcrumb'] .= '| Review anwers';

    session_start();
    CheckDaySelect();
    $showday = isset($_SESSION['showday']) ? $_SESSION['showday'] : 0;


$template->pageData['mainBody'] .= DaySelectForm($thisSession->id, false, false);
$template->pageData['mainBody'] .= "<h2>Questions</h2>";

$ViewQI = requestInt('qiID');
if($ViewQI)
{
    $allowShowCorrect=requestInt('asc');
    $template->pageData['mainBody'] .= displayQuestion($ViewQI, false, $allowShowCorrect);
}
else
{
	$questionInsts = array();
	$questions = array();
    $responses = array();
	if(strlen(trim($thisSession->questions)))
	{
	    $qiIDs = explode(',',$thisSession->questions);
	    foreach($qiIDs as $qid)
	    {
	        $qi =  questionInstance::retrieve_questionInstance($qid);
	        if(($showday == 0)||(($qi->endtime >= $showday)&&($qi->endtime < $showday+3600*24)))
	            $questionInsts[] = $qi;
	    }
	    for($n=0; $n < sizeof($questionInsts); $n++)
        {
	        $questions[$questionInsts[$n]->id] = question::retrieve_question($questionInsts[$n]->theQuestion_id);
	        $responses[$questionInsts[$n]->id] = response::retrieve($smemb->id, $questionInsts[$n]->id);
        }
	}
	//$template->pageData['mainBody'] .= '<pre>'.print_r($questions,1).'</pre>';
	//$template->pageData['mainBody'] .= '<pre>'.print_r($questionInsts,1).'</pre>';

	$template->pageData['mainBody'] .= '<table border="1">';
	    $template->pageData['mainBody'] .= "<tr><th>Question</th><th>Category</th><th>Response</th><th>Correct</th></tr>";
	foreach( $questionInsts as $qi)
	{
	    if(isset($qi->extras['category']))
	    	$cat = $qi->extras['category'];
	    else
	    	$cat = '';
	    //$resp = response::retrieve($smemb->id, $qi->id);
	    $dresp = $questions[$qi->id]->definition->getResponseForDisplay($responses[$qi->id]);
        if(($questions[$qi->id]->definition->getCorrectStr($qi)==false)||($questions[$qi->id]->definition->getCorrectStr($qi)==''))
            $correct = 'n/a';
        else
	        $correct = $questions[$qi->id]->definition->score($qi, $responses[$qi->id]) > 0 ? 'yes' : 'no';
        $allowShowCorrect = $correct=='no' ? '&asc=1':'';

	    $template->pageData['mainBody'] .= "<tr><td><a href='review.php?qiID={$qi->id}&sessionID={$thisSession->id}{$allowShowCorrect}'>{$qi->title}</a></td><td>$cat</td><td>$dresp</td><td>$correct</td></tr>";
	}
	$template->pageData['mainBody'] .= '</table>';

    if(function_exists('customScoring'))
    {
	    $template->pageData['mainBody'] .= '<br/>'.customScoring($questionInsts, $questions, $responses);
    }
}

echo $template->render();

//# Bad cut and paste programming here (modified from vote.php and responses.php)

function displayQuestion($qiid, $forceTitle=false, $allowShowCorrect=false)
{
    global $thisSession, $smemb;
    $out = '';
    $qi = questionInstance::retrieve_questionInstance($qiid);
    $qu = question::retrieve_question($qi->theQuestion_id);
    $resp = response::retrieve($smemb->id, $qi->id);
    if($qu)
    {
        if($allowShowCorrect==2)
            $resp->value = $qu->definition->getCorrectStr($qi);
        $qu->definition->checkResponse($qi->id, $resp);
	    //$qu->definition
	    if((strlen($qi->screenshot))&&(file_exists($qi->screenshot)))
	    {
	        $out .= "\n\n<img id='image' src='$qi->screenshot'/>\n\n";
	    }
        $out .= '<fieldset>';
        if($allowShowCorrect==2)
            $out .= '<legend>Correct response:</legend>';
        else
            $out .= '<legend>You answered:</legend>';
       //$out .= '<pre>'.print_r($resp,1).'</pre>';
	    $out .= "<form id='questionForm' method='POST' action='vote.php'>";
	    $out .= "<input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
	    $out .= "<input type='hidden' name='qiID' value='{$qi->id}'/>";
        if($forceTitle)
        {
            $qu->definition->displayTitle = true;
        }
	    $out .= $qu->definition->render($qi->title);

        if($allowShowCorrect==1)
            $out .= "<div class='submit'><a href='review.php?qiID={$qi->id}&sessionID={$thisSession->id}&asc=2'>Show correct response</a></div>";
        if($allowShowCorrect==2)
            $out .= "<div class='submit'><a href='review.php?qiID={$qi->id}&sessionID={$thisSession->id}&asc=1'>Show your response</a></div>";
        $out .= "<div class='submit'><a href='review.php?sessionID={$thisSession->id}'>Continue</a></div>";

        $out .= '</fieldset>';
	    $out .= "</form>";
    }
//$template->pageData['mainBody'] .= '<pre>'.print_r($qu,1).'</pre>';
    return $out;
}

function getImageScript()
{
return '<script lang="JavaScript">
        var shrunkWidth = 350;
        var img = document.getElementById("image");
        var oldWidth = img.width;
        img.width = shrunkWidth;
        img.onmouseenter = function () { img.width = oldWidth; }
        img.onmouseleave = function () { img.width = shrunkWidth; }
        </script>';
}



?>
