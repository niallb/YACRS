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
if(($uinfo==false)||($thisSession==false)||(!$thisSession->allowQuReview))
{
    header("Location: index.php");
    exit();
}
$smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);

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

$questionInsts = array();
$questions = array();
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
        $questions[$questionInsts[$n]->id] = question::retrieve_question($questionInsts[$n]->theQuestion_id);
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
    $correct = $questions[$qi->id]->definition->getCorrectForDisplay($qi);
    $resp = response::retrieve($smemb->id, $qi->id);
    $dresp = $questions[$qi->id]->definition->getResponseForDisplay($resp);

    $template->pageData['mainBody'] .= "<tr><td>{$qi->title}</td><td>$cat</td><td>$dresp</td><td>$correct</td></tr>";
}
$template->pageData['mainBody'] .= '</table>';


echo $template->render();

?>
