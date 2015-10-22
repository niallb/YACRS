<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
include_once('corelib/mobile.php');
require_once('lib/shared_funcs.php');
include_once('lib/lti_funcs.php');
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
$template->pageData['breadcrumb'] .= '| Create/Edit a session';

if($uinfo==false)
{
    header("Location: index.php");
}
else
{
	$esform = new editSession_form();
    if($esform->getStatus() == FORM_NOTSUBMITTED)
    {
        $esform->visible = true; // default to showing sessions.
    }
    //$esform->disable('questionMode');
    //$esform->disable('defaultQuActiveSecs');
    //$esform->disable('allowQuReview');
    $esform->disable('maxMessagelength');
    $esform->disable('allowTeacherQu');
    if(requestSet('sessionID'))
    {
        $thisSession = session::retrieve_session(requestInt('sessionID'));
    }
    else
    {
        $thisSession = false;
    }
	switch($esform->getStatus())
	{
	case FORM_NOTSUBMITTED:
        if($thisSession)
        {
	        $esform->setData($thisSession);
            $esform->sessionID = $thisSession->id;
        }
        else
        {
        	$esform->maxMessagelength = 140;
        }
	    $template->pageData['mainBody'] = $esform->getHtml();
	    break;
	case FORM_SUBMITTED_INVALID:
	    $template->pageData['mainBody'] = $esform->getHtml();
	    break;
	case FORM_SUBMITTED_VALID:
        if(!$thisSession)
        {
            $thisSession = new session();
            $thisSession->ownerID = $uinfo['uname'];
        }
	    $esform->getData($thisSession);
	    if($thisSession->id > 0)
	        $thisSession->update();
	    else
	    {
            $thisSession->created = time();
	        $thisSession->id = $thisSession->insert();
	    }
        if(strlen($thisSession->courseIdentifier))
            enrolStudents($thisSession->id, $thisSession->courseIdentifier);
	    header('Location:index.php?sessionID='.$thisSession->id);

	    break;
	case FORM_CANCELED:
	    header('Location:index.php');
	    break;
    }
	$template->pageData['logoutLink'] = loginBox($uinfo);
}

if(($thisSession !== false)&&($ltiSessionID = getLTISessionID())&&(isLTIStaff()))
{
    $template->pageData['mainBody'] .= "<p>To use the teacher control app for this session login with username: <b>{$thisSession->id}</b> and password <b>".substr($thisSession->ownerID, 0, 8)."</b></p>";
}

echo $template->render();



?>
