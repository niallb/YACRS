<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');
require_once('lib/vote_lib.php');

$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php"><i class="fa fa-home"></i>'.$CFG['sitetitle'].'</a></li>';


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
            if(isset($uinfo['user']->id))
                $smemb->user_id = $uinfo['user']->id;
            else
                $smemb->user_id = 0;
			$smemb->joined = time();
			$smemb->lastresponse = time();
			$smemb->insert();
        }
        else
        {
			$smemb->lastresponse = time();
			$smemb->update();
        }

		$template->pageData['breadcrumb'] .= "<li><i class='fa fa-question-circle'></i>{$thisSession->title}</li>";
		
		$template->pageData['breadcrumb'] .= '</ul>';
		
        if($thisSession->questionMode == 0)
        {
	        if($thisSession->currentQuestion == 0)
	        {
	            header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
			     if((isset($_REQUEST['submitans']))&&(isset($_REQUEST['qiID'])))
			         $template->pageData['mainBody'] .= "<div class='alert alert-danger'>Sorry, your answer was received after the question closed, so has been ignored.</div>";
	            $template->pageData['mainBody'] .= "<div class='alert alert-warning'>No active question.</div>";
	        }
	        else
	        {
                $template->pageData['mainBody'] .= displayQuestionBlock($thisSession->currentQuestion);
	        }
	        $template->pageData['mainBody'] .= "<div class='question-nav question-nav-bottom'><a href='vote.php?sessionID={$thisSession->id}&continue=1' class='pull-right'>Continue to Next Question &rsaquo;</a></div>";
        }
        else
        {
	        if((isset($thisSession->extras[currentQuestions]))&&(is_array($thisSession->extras[currentQuestions]))&&(sizeof($thisSession->extras[currentQuestions]) > 0))
            {
            	if(isset($_REQUEST['qiID']))
                    $cqiid = intval($_REQUEST['qiID']);
                else
                    $cqiid = $thisSession->extras[currentQuestions][0];
                $template->pageData['mainBody'] .= displayQuestionBlock($cqiid, true);

	            $positions = array_flip($thisSession->extras[currentQuestions]);
	            $cidx = $positions[$cqiid];
	            $template->pageData['mainBody'] .= "<p class='question-navigation'>";
	            $template->pageData['mainBody'] .= 'Question '.($cidx+1).' of '.sizeof($thisSession->extras[currentQuestions]);
	            if($cidx > 0)
	            {
	                $prev = $thisSession->extras[currentQuestions][$cidx-1];
		            $template->pageData['mainBody'] .= "<br/><a href='vote.php?sessionID={$thisSession->id}&qiID={$prev}'>&lsaquo; <span class='hidden-xs'>Back to </span>Previous<span class='hidden-xs'> Question</span></a> ";
	            }
	            if($cidx < sizeof($thisSession->extras[currentQuestions])-1)
	            {
	                $next = $thisSession->extras[currentQuestions][$cidx+1];
		            $template->pageData['mainBody'] .= "<br/><a href='vote.php?sessionID={$thisSession->id}&qiID={$next}'><span class='hidden-xs'>Continue to </span>Next<span class='hidden-xs'> Question</span> &rsaquo;</a>";
	            }
	            $template->pageData['mainBody'] .= "</p>";
                // Display which ones done, and quick links.
                $n = 0;
                $disps = array();
                foreach($thisSession->extras[currentQuestions] as $qiid)
                {
                    $n++;
                    $respExists = (response::retrieve($smemb->id, $qiid) != false);
                    $borderSize = $thisSession->extras[currentQuestions][$cidx] == $qiid ? 4:2;
                    if($respExists)
                        $style = "style='border:{$borderSize}px solid blue;padding:1px;margin:2px;background:#eeeeff;'";
                    else
                        $style = "style='border:{$borderSize}px solid gray;padding:1px;margin:2px;background:#ffeeee;'";
                    $disps[] = "<a href='vote.php?sessionID={$thisSession->id}&qiID={$qiid}' $style>$n</a>";
                }
                $template->pageData['mainBody'] .= '<p>'.implode(" ", $disps).'</p>';
            }
            else
	        {
	            header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
	            $template->pageData['mainBody'] .= "<div class='alert alert-warning'>No active questions.</div>";
                $template->pageData['mainBody'] .= "<p><a href='vote.php?sessionID={$thisSession->id}&continue=1'>Refresh</a></p>";
	        }
        }
        if($thisSession->ublogRoom)
        {
	        $template->pageData['mainBody'] .= "<a class='btn btn-success btn-block page-bottom-button' href='chat.php?sessionID={$thisSession->id}'><i class='fa fa-comments'></i> Discuss this class</a>";
        }
    }
	//$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
	//$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
	$template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function displayQuestionBlock($qiid, $forceTitle=false)
{
    global $thisSession, $smemb;
    $qi = questionInstance::retrieve_questionInstance($qiid);
    $out = '<div id="questionBlock">';
    $qu = question::retrieve_question($qi->theQuestion_id);
    $resp = response::retrieve($smemb->id, $qi->id);
    if((isset($_REQUEST['continue']))&&($resp!==false))
    {
        $out .= "<div class='alert alert-danger'>Sorry, the next queston is not active yet.</div>";
        header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
    }
    if((isset($_REQUEST['submitans']))&&(isset($_REQUEST['qiID']))&&($_REQUEST['qiID']!==$qi->id))
        $out .= "<div class='alert alert-danger'>Sorry, your answer was submitted after the question closed, so has been ignored.</div>";
    $out .= displayQuestion($qi, $resp, $forceTitle);
    $out .= '</div>';
    if($qu->anonymous)
    {
		$out .= "<div class='alert alert-info'>This question is set to 'anonymous'. Your identity will be stored with your response, however your teacher will not see it.</div>";
    }
//$template->pageData['mainBody'] .= '<pre>'.print_r($qu,1).'</pre>';
    return $out;
}


?>
