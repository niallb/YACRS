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
    $removeMode = (isset($_REQUEST['display']))&&($_REQUEST['display']=='remove');
    $members = sessionMember::retrieve_sessionMember_matching('session_id', $thisSession->id);
    $template->pageData['mainBody'] = "<h2>{$thisSession->title}</h2>";
    if($detail)
		$template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}'>Hide responses</a></p>";
    else
    {
		$template->pageData['mainBody'] .= "<p><a href='sessionmembers.php?sessionID={$thisSession->id}&display=detail'>Show responses</a>";
        if(!$removeMode)
    		$template->pageData['mainBody'] .= " | <a href='sessionmembers.php?sessionID={$thisSession->id}&display=remove'>Select and remove members.</a>";
        $template->pageData['mainBody'] .= "</p>";
    }
    if($removeMode)
    {
        //# Remove selected ones here if it was a remove action.
        if(isset($_REQUEST['remove_submit']))
        {
            foreach($_REQUEST as $nm=>$val)
            {
               	if($val==1)
                {
	                if(preg_match('/\Ar([0-9]+)$/', $nm, $matches))
	                {
	                    $thisSession->removeSessionMember($matches[1]);
	                }
                }
            }
            $members = sessionMember::retrieve_sessionMember_matching('session_id', $thisSession->id);
        }
        $removeChoice = false;
        $notActiveDays = false;
        if(update_from_selectForRemoval($removeChoice, $notActiveDays))
        {
            $selForRemoval = getRemoveList($thisSession, $removeChoice, $notActiveDays, $members);
        }
        $template->pageData['mainBody'] .= show_selectForRemoval($removeChoice, $notActiveDays);
        $template->pageData['mainBody'] .=  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        $template->pageData['mainBody'] .=  '<div class="formfield">';
        $template->pageData['mainBody'] .=  '<input class="submit" name="remove_submit" type="submit" value="Remove Selected Members" />';
        $template->pageData['mainBody'] .=  "</div>";
        $template->pageData['mainBody'] .=  '<input type="hidden" name="display" value="remove"/>';
        $template->pageData['mainBody'] .=  '<input type="hidden" name="sessionID" value="'.$thisSession->id.'"/>';
    }
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
    elseif($removeMode)
    {
        $template->pageData['mainBody'] .= "<th>Remove</th>";
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
            if($thisSession->extras['allowFullReview'])
		        $template->pageData['mainBody'] .= "<tr><td><a href='review.php?sessionID={$thisSession->id}&user={$m->id}'>{$m->userID}</a></td><td>{$m->name}</td><td>$gaptext</td>";
            else
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
		    elseif($removeMode)
		    {
		        $template->pageData['mainBody'] .= "<td><input type='checkbox' name='r{$m->id}' value='1'";
                if(isset($selForRemoval))
                {
                    if(in_array($m->id, $selForRemoval))
                        $template->pageData['mainBody'] .= " checked='checked'";
                }
                else
                {
                    if(isset($_REQUEST["r{$m->id}"]))
                        $template->pageData['mainBody'] .= " checked='checked'";
                }
                $template->pageData['mainBody'] .= "/></td>";
		    }
	        $template->pageData['mainBody'] .= "</tr>";
	    }
    }
    $template->pageData['mainBody'] .= "</table>";
    if($removeMode)
        $template->pageData['mainBody'] .= "</form>";
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

/*
#form selectForRemoval
select choice "Select" {'all'=>"All users", 'notcl'=>'Users not in classlist'};
integer natime "not active in the last";
okcancel "Select" 'cancel';
*/
function show_selectForRemoval($choice, $natime)
{
    global $thisSession;
    $out = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
    $out .= '<fieldset><legend>Select members for removal (You will get a chance to confirm the selection before they are removed.)</legend>';
    $out .= '<input type="hidden" name="selectForRemoval_code" value="'.selectForRemoval_magic.'"/>';

    $out .= '<div class="formfield">';
    $out .= '<label for="choice">Select:';
    $out .= '</label>';
    $out .= '<span class="forminput"><select name="choice"/>';
    $options = array('all'=>"All users", 'notcl'=>'Users not in classlist');
	foreach($options as $nm => $v)
	{
	    $out .= "<option";
		if(trim($nm)==trim($choice))
		    $out .= " selected=\"1\"";
		$out .= " value='$nm'>{$v}</option>\n";
	}
    $out .= "</select></span></div>\n";
    $out .= '<div class="formfield">';
    $out .= '<label for="natime">not active in the last:';
    $out .= '</label>';
    $out .= '<span class="forminput"><input type="text" name="natime" value="'.$natime.'" size="8"';
    $out .= "/> days.</span></div>\n";
    $out .= '<input type="hidden" name="display" value="remove"/>';
    $out .= '<input type="hidden" name="sessionID" value="'.$thisSession->id.'"/>';

    $out .= '<div class="formfield">';
    $out .= '<input class="submit" name="selectForRemoval_submit" type="submit" value="Select" />';
    $out .= "</div>";
    $out .= '</fieldset>';
    $out .= '</form>';
    return $out;
}

function update_from_selectForRemoval(&$choice, &$natime)
{
    if(!isset($_REQUEST['selectForRemoval_submit']))
        return false;
    $choice = $_REQUEST['choice'];
    $natime = intval($_REQUEST['natime']);
    return true;
}

function getRemoveList($theSession, $choice, $natime, $members)
{
   	$notActiveSince = time() - 24*3600*$natime;
    if($choice =='notcl')
    	$excludeList = getRosterStudentIDs($theSession->id, $theSession->courseIdentifier);
    else
        $excludeList = array();
	$rlist = array();
    foreach($members as $m)
    {
       if(($m->lastresponse < $notActiveSince)&&(!in_array($m->userID, $excludeList)))
           $rlist[] = $m->id;
    }
    return $rlist;
}

?>
