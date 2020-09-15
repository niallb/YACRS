<?php
require_once('../config.php');
require_once('../lib/forms.php');
require_once('../lib/database.php');
require_once('../lib/questionTypes.php');

session_start(array('name'=>'YACRSSESSION'));
$uinfo = checkLoggedInUser();
$sessionID = $_SESSION['sessionID'];

$output = array();

//exit(json_encode(array('alert'=>print_r($_SESSION, true))));

if(selectQuestionType_form_submitted())
{
    $qu = '';
    if(update_from_selectQuestionType_form($sessionID_tmp, $qu))
    {
        if($sessionID != $sessionID_tmp)
            exit(json_encode(array('alert'=>'Error: sessionIDs did not match..')));
        if((isset($questionTypes[$qu]))&&(class_exists($questionTypes[$qu]['edit'])))
        {
            $eqform = new $questionTypes[$qu]['edit']();
            $eqform->sessionID = $sessionID;
            $output['questionEditArea'] = $eqform->getHtml();
            if(file_exists(dirname(__DIR__)."/help/{$qu}.html"))
                $output['questionEditArea'] .= file_get_contents(dirname(__DIR__)."/help/{$qu}.html");

            echo json_encode($output);
            exit();
        }
        else
        {
            $oldQuID = requestInt('qu');
            $oldQuestion = question::retrieve_question($oldQuID);
            $eqform = $oldQuestion->definition->getModifiedCopyForm();
            $eqform->title = $oldQuestion->title;
            $eqform->anonymous = $oldQuestion->anonymous;
            $output['questionEditArea'] = $eqform->getHtml();
            $qu = $oldQuestion->definition->questionTypeName();
            if(file_exists(dirname(__DIR__)."/help/{$qu}.html"))
                $output['questionEditArea'] .= file_get_contents(dirname(__DIR__)."/help/{$qu}.html");

            echo json_encode($output);
            exit();
        }
    }
    else
    {
        echo json_encode(array('location'=>'runsession.php?sessionID='.requestInt('sessionID')));
        exit();
    }
}
elseif(isset($_REQUEST['qutype']))
{
    $thisSession = isset($sessionID)? session::retrieve_session($sessionID):false;

    $qutype = requestStr('qutype');
    if(isset($questionTypes[$qutype]))
    {
        $eqform = new $questionTypes[$qutype]['edit']();
        switch($eqform->getStatus())
        {
            //case FORM_NOTSUBMITTED:  break;
            case FORM_SUBMITTED_INVALID:
                //$template->pageData['mainBody'] .= '<div style="float:right;">'.helpLink('addquestion').'</div>';
                $output['questionEditArea'] = $eqform->getHtml();
                break;
            case FORM_SUBMITTED_VALID:
                if(!$theQu)
                {
                    $theQu = new question();
                    $theQu->ownerID = $uinfo['uname'];
                }
                //$eqform->getData($theQu);
                $theQu->session_id = $sessionID;
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
                $output['location'] = 'runsession.php?sessionID='.$sessionID;
                //$template->pageData['mainBody'] = 'Location:runsession.php?sessionID='.$eqform->sessionID;

                //header('Location:index.php?id='.$project->id);
                break;
            case FORM_CANCELED:
                $output['location'] = 'runsession.php?sessionID='.$sessionID;
                break;
        }
        echo json_encode($output);
        exit();
    }

}

echo json_encode(array('alert'=>__FILE__ .': Not implemented yet - question type '.$qu));