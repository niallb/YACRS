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
$template->pageData['breadcrumb'] .= '<li><a href="index.php"><i class="fa fa-home"></i>'.$CFG['sitetitle'].'</a></li>';
$template->pageData['breadcrumb'] .= "<li><a href='runsession.php?sessionID={$_REQUEST['sessionID']}'><i class='fa fa-play'></i>Session {$_REQUEST['sessionID']}</a></li>";
$template->pageData['breadcrumb'] .= '<li><i class="fa fa-users"></i>Members</li>';
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
    $detail = (isset($_REQUEST['display']))&&($_REQUEST['display']=='detail');
    $removeMode = (isset($_REQUEST['display']))&&($_REQUEST['display']=='remove');
    $members = sessionMember::retrieve_sessionMember_matching('session_id', $thisSession->id);
    $template->pageData['mainBody'] = "<h2>{$thisSession->title}</h2>";
    $template->pageData['mainBody'] .= "<div class='btn-toolbar'>";
    if($detail)
		$template->pageData['mainBody'] .= "<a href='sessionmembers.php?sessionID={$thisSession->id}' class='btn btn-info mr-3'><i class='fa fa-eye'></i>Hide Responses</a>";
    else
    {
		$template->pageData['mainBody'] .= "<a href='sessionmembers.php?sessionID={$thisSession->id}&display=detail' class='btn btn-info mr-3'><i class='fa fa-eye'></i>Show Responses</a>";
        if(!$removeMode)
    		$template->pageData['mainBody'] .= "<a href='sessionmembers.php?sessionID={$thisSession->id}&display=remove' class='btn btn-danger mr-3'><i class='fa fa-user-times'></i>Remove Members</a>";
        $template->pageData['mainBody'] .= "</div>";
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
        $template->pageData['mainBody'] .=  '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" class="form-horizontal">';
        $template->pageData['mainBody'] .=  '<div class="formfield">';
        $template->pageData['mainBody'] .=  '<input class="submit btn btn-danger" name="remove_submit" type="submit" value="Remove Selected Members" />';
        $template->pageData['mainBody'] .=  "</div>";
        $template->pageData['mainBody'] .=  '<input type="hidden" name="display" value="remove"/>';
        $template->pageData['mainBody'] .=  '<input type="hidden" name="sessionID" value="'.$thisSession->id.'"/>';
    }
    $template->pageData['mainBody'] .= "<table class='table table-striped'><thead><tr><th>User</th><th>Name</th><th>Last active</th>";
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
            $template->pageData['mainBody'] .= "<tr><td><a href='review.php?sessionID={$thisSession->id}&user={$m->id}'>{$m->userID}</a></td><td>{$m->name}</td><td>$gaptext</td>";
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
    $out = '<form action="'.$_SERVER['PHP_SELF'].'" method="POST" class="form-removal form-horizontal">';
    $out .= '<input type="hidden" name="selectForRemoval_code" value="'.selectForRemoval_magic.'"/>';
    $out .= '<div class="form-group">';
    $out .= '<label for="choice" class="col-sm-4 control-label">Select';
    $out .= '</label>';
    $out .= '<div class="col-sm-8"><select name="choice" class="form-control" />';
    $options = array('all'=>"All users", 'notcl'=>'Users not in classlist');
	foreach($options as $nm => $v)
	{
	    $out .= "<option";
		if(trim($nm)==trim($choice))
		    $out .= " selected=\"1\"";
		$out .= " value='$nm'>{$v}</option>\n";
	}
    $out .= "</select></div></div>";
    $out .= '<div class="form-group">';
    $out .= '<label class="col-sm-4 control-label" for="natime">Not active in the last';
    $out .= '</label>';
    $out .= '<div class="col-sm-2"><input type="text" name="natime" value="'.$natime.'" size="8" class="form-control"';
    $out .= "/></div><div class='col-sm-2'><p class='form-control-static'>days</p></div>";
    $out .= '<div class="col-sm-4"><input type="hidden" name="display" value="remove"/>';
    $out .= '<input type="hidden" name="sessionID" value="'.$thisSession->id.'"/>';

    $out .= '<input class="submit btn btn-block btn-success" name="selectForRemoval_submit" type="submit" value="Select Users" />';
    $out .= "</div></div>";
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
