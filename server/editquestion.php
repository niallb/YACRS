<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/questionTypes.php');
 
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><a href="index.php">YACRS</a></li>';

if(requestSet('sessionID'))
{
    $sessionID = requestInt('sessionID');
}

$thisSession = isset($sessionID)? session::retrieve_session($sessionID):false;

if(($uinfo==false)||($thisSession==false))
{
    header("Location: index.php");
}
else
{
    $template->addScript('scripts/ajax.js');
    $template->pageData['breadcrumb'] .= "<li><a href='runsession.php?sessionID={$sessionID}'>Session {$sessionID}</a></li>";
    $template->pageData['breadcrumb'] .= '<li>Add/Edit a question</li>';
    $template->pageData['breadcrumb'] .= '</ul>';
    $userDetail = userInfo::retrieve_by_username($uinfo['uname']);
	$theQu = requestSet('quID')? question::retrieve_question(requestInt('quID')):false;
    $template->pageData['mainBody'] = '';
	//$template->pageData['mainBody'] = '<pre>'.print_r($uinfo,1).'</pre>';

    $reuseQus = array();
    foreach($questionTypes as $key=>$val)
    {
        $reuseQus[$key] = "New ". $val['name'];
    }
    $modifyQus = question::getUserReuseList($uinfo['uname']);
    $modifyQus += question::getSessionReuseList($thisSession->id);
    foreach($modifyQus as $key=>$val)
        {
        $reuseQus[$key] = "Modify ". $val;
        }

    $template->pageData['mainBody'] .= '<div id="questionEditArea">'.show_selectQuestionType_form($sessionID, $qu, $reuseQus).'</div>';

//    $qtform = new selectQuestionType_form();
//    $qtform->sessionID = $sessionID;
//    $template->pageData['mainBody'] .= '<div id="quform">'.$qtform->getHtml().'</div>';;

/*    if(!$theQu)
    {
    	$qtform = new selectQuestionType_form();
        $qtform->sessionID = $sessionID;
        if($qtform->getStatus()==FORM_SUBMITTED_VALID)
        {
            $userDetail->teacherPrefs->lastQuType = $qtform->qu;
            $userDetail->update();
        }
        $template->pageData['mainBody'] .= $qtform->getHtml();
        if((isset($qtform->qu))&&(isset($questionTypes[$qtform->qu]))&&(class_exists($questionTypes[$qtform->qu]['edit'])))
    		$eqform = new $questionTypes[$qtform->qu]['edit']();
        else
    		$eqform = new editBasicQuestion_form();
    }
    else
    {
        $eqform = new editBasicQuestion_form();
		$eqform->sessionID = $theQu->session_id;
		$eqform->title = $theQu->title;
		$eqform->multiuse = $theQu->multiuse;
		$eqform->anonymous = $theQu->anonymous;
        //echo '<pre>'.print_r($theQu->definition, 1).'</pre>';
        $eqform->definition = $theQu->definition->source;
    }

	switch($eqform->getStatus())
	{
	case FORM_NOTSUBMITTED:
        $eqform->sessionID = $sessionID;
        $template->pageData['mainBody'] .= '<div style="float:right;">'.helpLink('addquestion').'</div>';
	    $template->pageData['mainBody'] .= $eqform->getHtml();
	    break;
	case FORM_SUBMITTED_INVALID:
        $template->pageData['mainBody'] .= '<div style="float:right;">'.helpLink('addquestion').'</div>';
	    $template->pageData['mainBody'] .= $eqform->getHtml();
	    break;
	case FORM_SUBMITTED_VALID:
        if(!$theQu)
        {
            $theQu = new question();
            $theQu->ownerID = $uinfo['uname'];
        }
	    //$eqform->getData($theQu);
		$theQu->session_id = $eqform->sessionID;
		$theQu->title = $eqform->title;
		$theQu->multiuse = $eqform->multiuse;
		$theQu->anonymous = $eqform->anonymous;
        $theQu->definition = $eqform->getNewQuestion();
	    if($theQu->id > 0)
        {
	        $theQu->update();
        }
	    else
	    {
            $theQu->id = $theQu->insert();
	    }
        $thisSession->addQuestion($theQu);
	    header('Location:runsession.php?sessionID='.$eqform->sessionID);
        //$template->pageData['mainBody'] = 'Location:runsession.php?sessionID='.$eqform->sessionID;

	    //header('Location:index.php?id='.$project->id);
	    break;
	case FORM_CANCELED:
	    header('Location:index.php');
	    break;
    }

*/

	$template->pageData['logoutLink'] = loginBox($uinfo);
}


echo $template->render();
