<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/questionTypes.php');
require_once('lib/shared_funcs.php');
 
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];

$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';
$template->pageData['breadcrumb'] .= "<li><a href='runsession.php?sessionID={$_REQUEST['sessionID']}'>Session {$_REQUEST['sessionID']}</a></li>";
$template->pageData['breadcrumb'] .= '<li>Responses</li>';
$template->pageData['breadcrumb'] .= '</ul>';

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
    // Work out where this questopn sits in list, show next and prev buttons...
    $qiIDs = explode(',',$thisSession->questions);
    $qiIDPos = array_flip($qiIDs);
    $pos = $qiIDPos[$_REQUEST['qiID']];
    $PrevNextLinks = '<div class="col-xs-4 question-prev">';
    if($pos > 0)
    {
        $PrevNextLinks .= "<a href='responses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos-1]}'>&lsaquo; Previous<span class='hidden-xs'> Question</span></a> ";
    }
    $PrevNextLinks .= '</div><div class="col-xs-4 col-xs-push-4 question-next">';
    if($pos < sizeof($qiIDs)-1)
    {
        $PrevNextLinks .= "<a href='responses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos+1]}'>Next<span class='hidden-xs'> Question</span> &rsaquo;</a> ";
    }
    $PrevNextLinks .= '</div>';
    if(!empty($PrevNextLinks))
    	$template->pageData['mainBody'] .= '<div class="question-nav question-nav-top">'.$PrevNextLinks.'</div>';

    // End of next/prev button stuff
	//$template->pageData['mainBody'] .= '<pre>'.print_r($qi,1).'</pre>';
    if((strlen($qi->screenshot))&&(file_exists($qi->screenshot)))
    {
        $template->pageData['mainBody'] .= "<img id='image' src='$qi->screenshot' style='float:right;'/>";
        $template->pageData['afterContent'] = getImageScript();
    }
    if((class_exists(get_class($qu->definition)))&&(get_class($qu->definition)!='__PHP_Incomplete_Class'))
    {
    	$template->pageData['mainBody'] .= $qu->definition->report($thisSession, $qi, (isset($_REQUEST['display']))&&($_REQUEST['display']=='detail'), $qu->anonymous);
    }
    else
    {
    	$template->pageData['mainBody'] .= "<br/><div>Question type is not currently enabled/supported.</div>";
    }
    $template->pageData['mainBody'] .= '<div class="question-nav question-nav-bottom">'.$PrevNextLinks.'</div>';
	$template->pageData['logoutLink'] = loginBox($uinfo);

}

if((isset($_REQUEST['updateAnotation']))&&(strpos($_REQUEST['updateAnotation'],' ')))
{
    if($pos < sizeof($qiIDs)-1)
    {
        header("Location: responses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos+1]}");
    }
}
else
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
