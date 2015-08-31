<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$_REQUEST['sessionID']}&mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = "<a href='http://www.gla.ac.uk/'>University of Glasgow</a> | <a href='http://www.gla.ac.uk/services/learningteaching/'>Learning & Teaching Centre</a> ";
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';

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

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
	$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        $smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);
        if($smemb == false)
        {
        	$smemb = new sessionMember();
            $smemb->session_id = $thisSession->id;
			$smemb->userID = $uinfo['uname'];
			$smemb->name = $uinfo['gn'].' '.$uinfo['sn'];
			$smemb->email = $uinfo['email'];
			$smemb->joined = time();
			$smemb->lastresponse = time();
			$smemb->insert();
        }
        else
        {
			$smemb->lastresponse = time();
			$smemb->update();
        }

		$template->pageData['breadcrumb'] .= "| {$thisSession->title}";
        if($thisSession->questionMode == 0)
        {
	        if($thisSession->currentQuestion == 0)
	        {
	            header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
	            $template->pageData['mainBody'] .= "<p>No active question.</p>";
	        }
	        else
	        {
                $template->pageData['mainBody'] .= displayQuestion($thisSession->currentQuestion);
	        }
	        $template->pageData['mainBody'] .= "<p><a href='vote.php?sessionID={$thisSession->id}&continue=1'>Next</a></p>";
        }
        else
        {
	        if((isset($thisSession->extras[currentQuestions]))&&(is_array($thisSession->extras[currentQuestions]))&&(sizeof($thisSession->extras[currentQuestions]) >= 0))
            {
            	if(isset($_REQUEST['qiID']))
                    $cqiid = intval($_REQUEST['qiID']);
                else
                    $cqiid = $thisSession->extras[currentQuestions][0];
                $template->pageData['mainBody'] .= displayQuestion($cqiid, true);

	            $positions = array_flip($thisSession->extras[currentQuestions]);
	            $cidx = $positions[$cqiid];
	            $template->pageData['mainBody'] .= "<p>";
	            $template->pageData['mainBody'] .= '(Question '.($cidx+1).' of '.sizeof($thisSession->extras[currentQuestions]).') ';
	            if($cidx > 0)
	            {
	                $prev = $thisSession->extras[currentQuestions][$cidx-1];
		            $template->pageData['mainBody'] .= "<a href='vote.php?sessionID={$thisSession->id}&qiID={$prev}'>Prev.</a> ";
	            }
	            if($cidx < sizeof($thisSession->extras[currentQuestions])-1)
	            {
	                $next = $thisSession->extras[currentQuestions][$cidx+1];
		            $template->pageData['mainBody'] .= "<a href='vote.php?sessionID={$thisSession->id}&qiID={$next}'>Next</a>";
	            }
	            $template->pageData['mainBody'] .= "</p>";
            }
            else
	        {
	            header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
	            $template->pageData['mainBody'] .= "<p>No active questions.</p>";
                $template->pageData['mainBody'] .= "<p><a href='vote.php?sessionID={$thisSession->id}&continue=1'>Refresh</a></p>";
	        }
        }
        if($thisSession->ublogRoom)
        {
	        $template->pageData['mainBody'] .= "<p><a href='chat.php?sessionID={$thisSession->id}'>&mu;blog and discuss</a></p>";
        }
    }
	//$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
	//$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
	$template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function displayQuestion($qiid, $forceTitle=false)
{
    global $thisSession, $smemb;
    $out = '';
     $qi = questionInstance::retrieve_questionInstance($qiid);
     $qu = question::retrieve_question($qi->theQuestion_id);
     $resp = response::retrieve($smemb->id, $qi->id);
     if((isset($_REQUEST['continue']))&&($resp!==false))
     {
         $out .= "<p style='color:red;'>Sorry, the next queston is not active yet.</p>";
         header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
     }
     if((isset($_REQUEST['submitans']))&&(isset($_REQUEST['qiID']))&&($_REQUEST['qiID']!==$qi->id))
         $out .= "<p style='color:red;'>Sorry, your answer was submitted after the question closed, so has been ignored.</p>";
     if($qu)
     {
	     $qu->definition->checkResponse($qi->id, $resp);
	     // New & replacement responses added here, partial responses for questions that support it done by checkResponse
	     if(($resp == false)&&($qu->definition->responseValue !== false))
	     {
	         $resp = new response();
	         $resp->user_id = $smemb->id;
	         $resp->question_id = $qi->id;
	         $resp->value = $qu->definition->responseValue;
	         if(isset($qu->definition->partialResponse))
	             $resp->isPartial = $qu->definition->partialResponse;
	         else
	             $resp->isPartial = false;
	         $resp->insert();
	         $smemb->lastresponse = time();
	         $smemb->update();
	     }
         elseif($qu->definition->responseValue !== false)
         {
	         $resp->value = $qu->definition->responseValue;
	         $resp->update();
	         $smemb->lastresponse = time();
	         $smemb->update();
         }
	     //$qu->definition
        $out .= '<fieldset>';
        if(($resp == false)||($resp->isPartial))
            $out .= '<legend>Input:</legend>';
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
         if(($qu->definition->responseValue === false)||(($thisSession->allowQuReview)&&(isset($_REQUEST['doupdate']))))
         {
             $out .= "<div class='submit'><input type='submit' name='submitans' value='Submit answer'/></div>";
         }
         elseif(($thisSession->allowQuReview)&&($qu->definition->allowReview()))
         {
             $out .= "<div class='submit'><a href='vote.php?sessionID={$thisSession->id}&qiID={$qi->id}&doupdate=1'>Change answer</a></div>";
         }
         $out .= '</fieldset>';
	     $out .= "</form>";
     }
//$template->pageData['mainBody'] .= '<pre>'.print_r($qu,1).'</pre>';
    return $out;
}


?>
