<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/ajax.php');
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
$template->pageData['breadcrumb'] .= '| Run a session';


$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}
else
{
    $template->pageData['afterContent'] = getAJAXScript();
	//$template->pageData['mainBody'] = '<pre>'.print_r($uinfo,1).'</pre>';
    if(isset($_REQUEST['activate']))
    {
        if($thisSession->currentQuestion > 0)
        {
        	$cqi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
            $cqi->endtime = time();
            $cqi->update();
        }
        $thisSession->currentQuestion = $_REQUEST['activate'];
        if($thisSession->currentQuestion > 0)
        {
        	$cqi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
            $cqi->starttime = time();
            $cqi->update();
        }
        $thisSession->update();
        if(($thisSession->ublogRoom>0)&&($_REQUEST['activate']>0))
        {
	        $msg = new message();
	        $msg->user_id = 0;
	        $msg->posted = time();
	        $msg->message = "<a href='vote.php?sessionID={$thisSession->id}'>Question available.</a>";
	        $msg->session_id = $thisSession->id;
	        $msg->insert();
        }
    }

    $reuseQus = question::getUserReuseList($uinfo['uname']);
    $reuseQus += question::getSessionReuseList($thisSession->id);

    $aqform = new addQuestion_form($thisSession->id, $reuseQus);
    if($aqform->getStatus()==FORM_SUBMITTED_VALID)
    {
    	if($aqform->qu == 0)
            header("Location: editquestion.php?sessionID={$thisSession->id}");
        else
        {
            $theQu = question::retrieve_question($aqform->qu);
            if($theQu)
	            $thisSession->addQuestion($theQu);
        }
    }

    $template->pageData['mainBody'] = "<h1 style='text-align:center;'>Session ID: {$thisSession->id}</h1>";
    $userCount = sessionMember::count("session_id", $thisSession->id);
    $activeCount = sessionMember::countActive($thisSession->id);
    $template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}'>Active users (total users): $activeCount ($userCount)</a></p>";
    $template->pageData['mainBody'] .= "<h2>Session Questions</h2>";
    if(isset($_REQUEST['moveitem']))
    {
        performMove($thisSession);
    }
    elseif(isset($_REQUEST['delete']))
    {
        deleteQi($thisSession);
    }
    $quTitles = array();
    if(strlen(trim($thisSession->questions)))
    {
        $template->pageData['mainBody'] .= '<table border="1"><thead><tr><th>#</th><th>Question</th><th>Used</th><th>Control</th><th>Responses</th><th>Actions</th></tr></thead><tbody>';

        $qiIDs = explode(',',$thisSession->questions);
        // check current is valid, display make active stuff otherwise
	    if(!in_array($thisSession->currentQuestion, $qiIDs))
	        $thisSession->currentQuestion = 0;

        $qunum = 0;
        if(isset($_REQUEST['move']))
            $moveMode = 'before';
        foreach($qiIDs as $qiID)
        {
            $qunum++;
            $qi = questionInstance::retrieve_questionInstance($qiID);
            $quTitles[] = array('id'=>$qi->id, 'title'=>$qi->title);
            $qu = question::retrieve_question($qi->theQuestion_id);
            if($qu)
            {
                if($thisSession->questionMode == 1)
                {
                    if((isset($thisSession->extras[currentQuestions]))&&(in_array($qiID, $thisSession->extras[currentQuestions])))
                    {
		                $template->pageData['mainBody'] .= "\n<tr style='background-color: palegreen;'><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a href='#' OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                    }
                    else
                    {
		                $template->pageData['mainBody'] .= "\n<tr><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a href='#' OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                    }
                }
                else
                {
	                if($thisSession->currentQuestion == $qiID)
		                $template->pageData['mainBody'] .= "\n<tr style='background-color: palegreen;'><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a href='#' OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
	                else
		                $template->pageData['mainBody'] .= "\n<tr><td>$qunum.</td><td id='title{$qiID}'><span id='title{$qiID}_txt'>{$qi->title}</span>&nbsp;<a href='#' OnClick='EditTitle(\"{$qiID}\");'>(Edit title)</a></td>";
                }
                if($qi->endtime > 0)
                {
	                $template->pageData['mainBody'] .= "<td>".strftime("%d %b %H:%M", $qi->endtime)."</td>";
                }
                else
                {
	                $template->pageData['mainBody'] .= "<td>&nbsp;</td>";
                }
                if($thisSession->questionMode == 1)
                {
                    if((isset($thisSession->extras[currentQuestions]))&&(in_array($qiID, $thisSession->extras[currentQuestions])))
                    {
		            	$template->pageData['mainBody'] .= "<td>Active <input type='checkbox' value='$qiID' name='Activate_$qiID' checked='checked'/></a></td>";
                    }
                    else
                    {
		            	$template->pageData['mainBody'] .= "<td>Active <input type='checkbox' value='$qiID' name='Activate_$qiID'/></a></td>";
                    }
                }
                else
                {
	                if($thisSession->currentQuestion == 0)
	                {
		            	$template->pageData['mainBody'] .= "<td><a href='runsession.php?sessionID={$thisSession->id}&activate=$qiID'>Make active</a></td>";
	                }
	                elseif($thisSession->currentQuestion == $qiID)
	                {
		            	$template->pageData['mainBody'] .= "<td><a href='runsession.php?sessionID={$thisSession->id}&activate=0'>Close</a></td>";
	                    $template->pageData['afterContent'] .= getMonitorResponsesJS($thisSession->id, $qiID);
	                }
	                else
	                {
		            	$template->pageData['mainBody'] .= "<td>&nbsp;</td>";
	                }
                }
                $count = response::countCompleted($qi->id);
                if(($count == 0)&&($thisSession->currentQuestion != $qiID))
	            	$template->pageData['mainBody'] .= "<td>No responses</td>";
                else
		            $template->pageData['mainBody'] .= "<td><a href='responses.php?sessionID={$thisSession->id}&qiID=$qiID'><span id='rc$qiID'>$count</span> response(s)</a></td>";
                if(isset($_REQUEST['move']))
                {
                    if($_REQUEST['move'] == $qiID)
                    {
            			$moveMode = 'after';
            		    $template->pageData['mainBody'] .= "<td><i><a href='runsession.php?sessionID={$thisSession->id}'>(Cancel move)</a></i></td>";
                    }
                    else
                    {
            		    $template->pageData['mainBody'] .= "<td><a href='runsession.php?sessionID={$thisSession->id}&moveitem={$_REQUEST['move']}&$moveMode=$qiID'>(To $moveMode this)</a> ";
                    }
                }
                else
                {
            		$template->pageData['mainBody'] .= "<td><a href='runsession.php?sessionID={$thisSession->id}&move=$qiID'>(Move)</a> ";
                    $template->pageData['mainBody'] .= "<a href='runsession.php?sessionID={$thisSession->id}&delete=$qiID'>(Delete)</a></td>";
                }
	            $template->pageData['mainBody'] .= "</tr>";
            }
        }
        $template->pageData['mainBody'] .= "</table>";
    }
    else
        $template->pageData['mainBody'] .= "<p>No questions added yet.</p>";
    $template->pageData['mainBody'] .= $aqform->getHtml();

    if(sizeof($quTitles))
    {
        //$template->pageData['mainBody'] .= "<p><a href='export.php?sessionID={$thisSession->id}'>Export response data (CSV)</a></p>";
        $template->pageData['mainBody'] .= "<form action='export.php'>Export response data (CSV):<input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
        $template->pageData['mainBody'] .= "From <select name='from'>";
        foreach($quTitles as $qt)
           $template->pageData['mainBody'] .= "<option value='{$qt['id']}'>{$qt['title']}</option>";
        $template->pageData['mainBody'] .= "</select>";
        $template->pageData['mainBody'] .= " To <select name='to'><option value='".$quTitles[sizeof($quTitles)-1]['id']."'></option>";
        foreach($quTitles as $qt)
           $template->pageData['mainBody'] .= "<option value='{$qt['id']}'>{$qt['title']}</option>";
        $template->pageData['mainBody'] .= "</select>";
        $template->pageData['mainBody'] .= "<input type='submit' value='Export'/></form>";

    }

    if($thisSession->ublogRoom)
    {
	    $template->pageData['mainBody'] .= "<h2>Chat</h2><a href='chatviewer.php?sessionID={$thisSession->id}' target='_new'>QR code and chat display</a>";
	    $template->pageData['mainBody'] .= " | <a href='chatlisting.php?sessionID={$thisSession->id}'>Download full chat list</a>";
	    $template->pageData['mainBody'] .= "<div id='messages' style='border : 1px solid #00008B;'></div>";
        $template->pageData['afterContent'] .= getUBlogUpdateAJAXScript($thisSession->id);
    }




	$template->pageData['logoutLink'] = loginBox($uinfo);
}

echo $template->render();


function getAJAXScript()
{
	return "<script lang=\"JavaScript\">
function httpGet(theUrl)
{
    var xmlHttp = null;

	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlHttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlHttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
	  }
    xmlHttp.open( \"GET\", theUrl, false );
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

function EditTitle(id)
{
    name = \"title\"+id;
    document.getElementById(name).innerHTML = \"<input type='text' id='edt' size='60' maxlength='80' value='\"+document.getElementById('title'+id+'_txt').innerHTML+\"'/><a href='#' OnClick='UpdateTitle(\\\"\"+id+\"\\\");'>Update</a>\";
}

function UpdateTitle(id)
{
    var updateURL = 'updateTitle.php?qiID='+id+'&text='+encodeURIComponent(document.getElementById('edt').value);
    var text = httpGet(updateURL);
    var name = \"title\"+id;
    document.getElementById(name).innerHTML = \"<span id='title\"+id+\"_txt'>\"+text + \"</span>&nbsp;<a href='#' OnClick='EditTitle(\\\"\"+id+\"\\\");'>(Edit)</a></td></tr></table>\";
}


</script>";
}

function getMonitorResponsesJS($sessionID, $qiID)
{
	return "<script lang=\"JavaScript\">
	function refreshResponseCount()
	{
	    document.getElementById('rc{$qiID}').innerHTML = httpGet(\"responseCounter.php?sessionID={$sessionID}&qiID={$qiID}\");
	    var refresher = setTimeout(\"refreshResponseCount()\", 1000);
	}
	refreshResponseCount();</script>";
}

function performMove(&$thisSession)
{
    $moveID = $_REQUEST['moveitem'];
    $qiIDs = explode(',',$thisSession->questions);
    $qiIndexes = array_flip($qiIDs);
    array_splice($qiIDs, $qiIndexes[$moveID],1);
    $qiIndexes = array_flip($qiIDs);
    if(isset($_REQUEST['before']))
    {
        array_splice($qiIDs, $qiIndexes[$_REQUEST['before']],0,array($moveID));
    }
    elseif(isset($_REQUEST['after']))
    {
        array_splice($qiIDs, $qiIndexes[$_REQUEST['after']]+1,0,array($moveID));
    }
    else
    {
        // should never happen, bale out before touching the database
        return false;
    }
    $thisSession->questions = implode(',',$qiIDs);
    $thisSession->update();
    return true;
}

function deleteQi(&$thisSession)
{
    $deleteID = $_REQUEST['delete'];
    $qiIDs = explode(',',$thisSession->questions);
    $qiIndexes = array_flip($qiIDs);
    array_splice($qiIDs, $qiIndexes[$deleteID],1);
    $thisSession->questions = implode(',',$qiIDs);
    if($thisSession->currentQuestion == $deleteID)
        $thisSession->currentQuestion = 0;
    $thisSession->update();
    // clean up database
    questionInstance::deleteInstance($deleteID);
    return true;
}


?>
