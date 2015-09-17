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
    $qi = questionInstance::retrieve_questionInstance($_REQUEST['qiID']);
    $qu = question::retrieve_question($qi->theQuestion_id);
    //$template->pageData['mainBody'] = "<h2>$qu->title</h2>";
    $template->pageData['mainBody'] = "<h2>$qi->title</h2>";
    // Work out where this questopn sits in list, show next and prev buttons...
    $qiIDs = explode(',',$thisSession->questions);
    $qiIDPos = array_flip($qiIDs);
    $pos = $qiIDPos[$_REQUEST['qiID']];
    $PrevNextLinks = '';
    if($pos > 0)
    {
        $PrevNextLinks .= "<a href='responses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos-1]}'>Prev.</a> ";
    }
    if($pos < sizeof($qiIDs)-1)
    {
        $PrevNextLinks .= "<a href='responses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos+1]}'>Next</a> ";
    }
    $template->pageData['mainBody'] .= $PrevNextLinks.'<br/>';
    // End of next/prev button stuff
	//$template->pageData['mainBody'] .= '<pre>'.print_r($qu->definition,1).'</pre>';
    if((strlen($qi->screenshot))&&(file_exists($qi->screenshot)))
    {
        $template->pageData['mainBody'] .= "<img id='image' src='$qi->screenshot' style='float:right;'/>";
        $template->pageData['afterContent'] = getImageScript();
    }
    $template->pageData['mainBody'] .= $qu->definition->report($thisSession, $qi, (isset($_REQUEST['display']))&&($_REQUEST['display']=='detail'));
    $template->pageData['mainBody'] .= $PrevNextLinks.'<br/>';
	$template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

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
