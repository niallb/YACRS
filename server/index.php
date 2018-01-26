<?php
/*****************************************************************************
YACRS Copyright 2013-2015, The University of Glasgow.
Written by Niall S F Barr (niall.barr@glasgow.ac.uk, niall@nbsoftware.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*****************************************************************************/
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
include_once('corelib/mobile.php');
include_once('lib/lti_funcs.php');
$template = new templateMerge($TEMPLATE);
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?mode=mobile'>Use mobile mode</a>";

$loginError = '';
$uinfo = checkLoggedInUser(true, $loginError);

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '<li><i class="fa fa-home"></i>'.$CFG['sitetitle'].'</li>';
$template->pageData['breadcrumb'] .= '</ul>';
if($uinfo==false)
{
	$template->pageData['headings'] = "<h1  style='text-align:center; padding:10px;'>Login</h1>";
    $template->pageData['loginBox'] = '';
    if((isset($CFG['MicrosoftClientID']))&&($CFG['MicrosoftClientID']!=''))
    {
        $template->pageData['loginBox'] .= '<div class="loginLink" style="text-align:center; padding:10px;"><a href="oidc.php" class="btn btn-primary">Login with Microsoft</a><div><hr/>';
    }
    if((isset($CFG['ldaphost']))&&($CFG['ldaphost']!=''))
    {
        $template->pageData['loginBox'] .= loginBox($uinfo, $loginError);
    }
    if(file_exists('logininfo.htm'))
	    $template->pageData['mainBody'] = file_get_contents('logininfo.htm').'<br/>';
    $template->pageData['logoutLink'] = "";
}
else
{
    $thisSession = requestSet('sessionID') ? session::retrieve_session(requestInt('sessionID')):false;
    if($thisSession)
    {
        if(checkPermission($uinfo, $thisSession))
        {
            $template->pageData['mainBody'] .= "<a href='runsession.php?sessionID={$thisSession->id}'>Run session</a>";
            header("Location: runsession.php?sessionID={$thisSession->id}");
        }
        elseif(($thisSession->currentQuestion==0)&&($thisSession->ublogRoom>0))
        {
            $template->pageData['mainBody'] .= "<a href='chat.php?sessionID={$thisSession->id}'>Join session</a>";
            header("Location: chat.php?sessionID={$thisSession->id}");
        }
        else
        {
            $template->pageData['mainBody'] .= "<a href='vote.php?sessionID={$thisSession->id}'>Join session</a>";
            header("Location: vote.php?sessionID={$thisSession->id}");
        }
    }
    elseif($ltiSessionID = getLTISessionID())
    {
        if(isLTIStaff())
	    {
	        $template->pageData['mainBody'] .= '<ul>';
            $s = session::retrieve_session($ltiSessionID);
            if($s !== false)
            {
	            $ctime = strftime("%A %e %B %Y at %H:%M", $s->created);
	            $template->pageData['mainBody'] .= "<li><p class='session-title'><a href='runsession.php?sessionID={$s->id}'>{$s->title}</a><span class='user-badge session-id'><i class='fa fa-hashtag'></i> {$s->id}</span></p><p class='session-details'> Created $ctime</p><a href='editsession.php?sessionID={$s->id}'>Edit</a> <a href='confirmdelete.php?sessionID={$s->id}'>Delete</a></li>";
	            //$template->pageData['mainBody'] .= "<li>Session number: <b>{$s->id}</b> <a href='runsession.php?sessionID={$s->id}'>{$s->title}</a> (Created $ctime) <a href='editsession.php?sessionID={$s->id}'>Edit</a> <a href='confirmdelete.php?sessionID={$s->id}'>Delete</a></li>";
                $template->pageData['mainBody'] .= "<li>To use the teacher control app for this session login with username: <b>{$s->id}</b> and password <b>".substr($s->ownerID, 0, 8)."</b></li>";

            }
            else
            {
                $template->pageData['mainBody'] .= "<li>No session found for this LTI link. To create a new session return to the VLE/LMS and click the link again.</li>";
            }
	        $template->pageData['mainBody'] .= '</ul>';
	    }
        else
        {
            $template->pageData['mainBody'] .= "<a href='vote.php?sessionID={$thisSession->id}'>Join session</a>";
            header("Location: vote.php?sessionID={$thisSession->id}");
        }
    }
    else
    {
	    $template->pageData['mainBody'] = sessionCodeinput();
	    if($uinfo['sessionCreator'])
	    {
	        $template->pageData['mainBody'] .= "<div class='row'><div class='col-sm-8 col-sm-push-4'><a class='btn btn-primary' href='editsession.php'><i class='fa fa-plus-circle'></i> Create a new clicker session</a></div></div>";
		    $sessions = session::retrieve_session_matching('ownerID', $uinfo['uname']);
	        if($sessions === false)
	            $sessions = array();
	        $sessions = array_merge($sessions, session::teacherExtraSessions($uinfo['uname']));
		    $template->pageData['mainBody'] .= '<h2 class="page-section">My sessions (staff)</h2>';
		    if(sizeof($sessions) == 0)
		    {
		        $template->pageData['mainBody'] .= "<p>No sessions found</p>";
		    }
		    else
		    {
		        $template->pageData['mainBody'] .= '<ul class="session-list">';
		        foreach($sessions as $s)
		        {
		            $ctime = strftime("%A %e %B %Y at %H:%M", $s->created);
		            $template->pageData['mainBody'] .= "<li><p class='session-title'><a href='runsession.php?sessionID={$s->id}'>{$s->title}</a><span class='user-badge session-id'><i class='fa fa-hashtag'></i> {$s->id}</span></p><p class='session-details'> Created $ctime</p><span class='feature-links'><a href='editsession.php?sessionID={$s->id}'><i class='fa fa-pencil'></i> Edit</a> <a href='confirmdelete.php?sessionID={$s->id}'><i class='fa fa-trash-o'></i> Delete</a></span></li>";
		            //$template->pageData['mainBody'] .= "<li>Session number: <b>{$s->id}</b> <a href='runsession.php?sessionID={$s->id}'>{$s->title}</a> (Created $ctime) <span class='feature-links'><a href='editsession.php?sessionID={$s->id}'><i class='fa fa-pencil'></i> Edit</a> <a href='confirmdelete.php?sessionID={$s->id}'><i class='fa fa-trash-o'></i> Delete</a></span></li>";
		        }
		        $template->pageData['mainBody'] .= '</ul>';
		    }
	    }
		$slist = sessionMember::retrieve_sessionMember_matching('userID', $uinfo['uname']);
	    $template->pageData['mainBody'] .= '<h2 class="page-section">My sessions</h2>';
	    $sessions = array();
	    if($slist)
	    {
	        foreach($slist as $s)
	        {
	            $sess = session::retrieve_session($s->session_id);
	            if(($sess)&&($sess->visible))
	                $sessions[] = $sess;
	        }
	    }
	    if(sizeof($sessions) == 0)
	    {
	        $template->pageData['mainBody'] .= "<p>No sessions found</p>";
	    }
	    else
	    {
	        $template->pageData['mainBody'] .= '<ul>';
	        foreach($sessions as $s)
	        {
	            $ctime = strftime("%A %e %B %Y at %H:%M", $s->created);
	            $template->pageData['mainBody'] .= "<li><a href='vote.php?sessionID={$s->id}'>{$s->title}</a>";
                if((isset($s->extras['allowFullReview']))&&($s->extras['allowFullReview']))
                     $template->pageData['mainBody'] .= " (<a href='review.php?sessionID={$s->id}'>Review previous answers</a>)";
                $template->pageData['mainBody'] .= "</li>";
	        }
	        $template->pageData['mainBody'] .= '</ul>';
	    }
        $user = userInfo::retrieve_by_username($uinfo['uname']);
        if($user !== false)
        {
	        $template->pageData['mainBody'] .= '<h2 class="page-section">My settings</h2>';
            if((isset($CFG['smsnumber']))&&(strlen($CFG['smsnumber'])))
            {
	            $code = substr(md5($CFG['cookiehash'].$user->username),0,4);
	            if(strlen($user->phone))
	            {
	                $template->pageData['mainBody'] .= "<p>Current phone for SMS: {$user->phone}</p>";
	            }
	            $template->pageData['mainBody'] .= "<p>To associate a phone with your username text \"link {$user->username} $code\" (without quotes) to {$CFG['smsnumber']}.</p>";
            }
	        //$template->pageData['mainBody'] .= '<pre>'.print_r($user,1).'</pre>';

        }
	    if($uinfo['isAdmin'])
	    {
	        $template->pageData['mainBody'] .= '<a href="admin.php" class="btn btn-danger"><i class="fa fa-wrench"></i> YACRS administration</a>';
	    }
	    //$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
		$template->pageData['logoutLink'] = loginBox($uinfo);
    }
}
echo $template->render();

?>
