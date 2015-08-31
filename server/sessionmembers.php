<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/questionTypes.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = "<a href='http://www.gla.ac.uk/'>University of Glasgow</a> | <a href='http://www.gla.ac.uk/services/learningteaching/'>Learning & Teaching Centre</a> ";
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';
$template->pageData['breadcrumb'] .= "| <a href='runsession.php?sessionID={$_REQUEST['sessionID']}'>Session {$_REQUEST['sessionID']}</a>";
$template->pageData['breadcrumb'] .= '| Responses';

$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}
else
{
	//$template->pageData['mainBody'] = '<pre>'.print_r($uinfo,1).'</pre>';
    $detail = (isset($_REQUEST['display']))&&($_REQUEST['display']=='detail');
    $members = sessionMember::retrieve_sessionMember_matching('session_id', $thisSession->id);
    $template->pageData['mainBody'] = "<h2>{$thisSession->title}</h2>";
    if($detail)
		$template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}'>Hide responses</a></p>";
    else
		$template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}&display=detail'>Show responses</a></p>";
    $template->pageData['mainBody'] .= "<table border='1'><thead><tr><th>User</th><th>Name</th><th>Last active</th>";
    if($detail)
    {
        $qiIDs = explode(',',$thisSession->questions);
        $qunum = 0;
        $responses = array();
        foreach($qiIDs as $qiID)
        {
            $qunum++;
            $template->pageData['mainBody'] .= "<th>Q{$qunum}</th>";
	        $responses += responsesList($thisSession->id, $qiID);
        }
    }
    $template->pageData['mainBody'] .= "</thead><tbody>";
    if($members)
    {
	    foreach($members as $m)
	    {
	        if($m->lastresponse > 0)
	        {
	        	$gap = time()-$m->lastresponse;
	            if($gap < 60)
	                $gaptext = "$gap second(s)";
	            elseif($gap < 3600)
	                $gaptext = intval($gap/60)." minute(s)";
	            elseif($gap < 24*3600)
	                $gaptext = intval($gap/3600)." hours(s)";
	            else
	                $gaptext = intval($gap/(3600*24))." day(s)";
	        }
            else
            {
                $gaptext = 'Never';
            }
	        $template->pageData['mainBody'] .= "<tr><td>{$m->userID}</td><td>{$m->name}</td><td>$gaptext</td>";
	        if($detail)
		    {
		        foreach($qiIDs as $qiID)
		        {
	                if(isset($responses[$m->id.'_'.$qiID]))
			            $template->pageData['mainBody'] .= "<th>".$responses[$m->id.'_'.$qiID]."</th>";
	                else
			            $template->pageData['mainBody'] .= "<th>n.a.</th>";
		        }
		    }
	        $template->pageData['mainBody'] .= "</tr>";
	    }
    }
    $template->pageData['mainBody'] .= "</table>";

	$template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function responsesList($sessionID, $qiID)
{
    $out = array();
	$resps = response::retrieve_response_matching("question_id", $qiID);
    if($resps)
    {
    	foreach($resps as $r)
        	$out[$r->user_id.'_'.$r->question_id] = $r->value;
    }
    return $out;
}


?>
