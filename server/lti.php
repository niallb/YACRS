<?php

include_once('config.php');
include_once('corelib/lti_session.php');
include_once('lib/secretManager.php');
include_once('lib/login.php');

$userinfo = checkLTISession();
if($userinfo==false)
    exit("Failed to launch LTI session.");
if(!isset($userinfo->params['user_id']))
    exit("YACRS requires recommended LTI field user_id to be set.");

$consumer = lticonsumer::retrieve_by_consumer_key($userinfo->params['oauth_consumer_key']);
if($consumer != false)
{
    //Do login
    $user = false;
    // Check for user by e-mail
    if((isset($userinfo->params['lis_person_contact_email_primary']))&&(strlen($userinfo->params['lis_person_contact_email_primary'])))
    {
         $user = userInfo::retrieve_userInfo_matching('email', $userinfo->params['lis_person_contact_email_primary']);
         if(is_array($user))
             $user = $user[0];
    }
    if((strpos($userinfo->params['roles'], 'Instructor')!==false)||(strpos($userinfo->params['roles'], 'Administrator')!==false))
        $sessionMemberUserID = $userinfo->params['resource_link_id'];  // All LTI teachers are the same, and are a special user who owns the session but has no other existance
    elseif($user == false)
        $sessionMemberUserID = $userinfo->params['user_id'];
    else
        $sessionMemberUserID = $user->username;

    $gn = isset($userinfo->params['lis_person_name_given']) ? $userinfo->params['lis_person_name_given'] : '';
    $sn = isset($userinfo->params['lis_person_name_family']) ? $userinfo->params['lis_person_name_family'] : '(LTI)';
    $email = isset($userinfo->params['lis_person_contact_email_primary']) ? $userinfo->params['lis_person_contact_email_primary'] : '';
    $uinfo = array('uname'=>$sessionMemberUserID, 'gn'=>$smemb->name, 'sn'=>'(Guest)', 'email'=>'', 'isAdmin'=>false, 'sessionCreator'=>false);
    setcookie($CFG['appname'].'_login',CreateLoginCookie($uinfo));


	$session_id = ltisessionlink::retrieve_session_id($consumer->id, $userinfo->params['resource_link_id']);
    if($session_id == false)
    {
        if((strpos($userinfo->params['roles'], 'Instructor')!==false)||(strpos($userinfo->params['roles'], 'Administrator')!==false))
        {
            $lsl = new ltisessionlink();
            $lsl->client_id = $consumer->id;
            $lsl->resource_link_id = $userinfo->params['resource_link_id'];
            $thisSession = new session();
            $thisSession->ownerID = $sessionMemberUserID;
            $thisSession->allowGuests = true;
            $thisSession->title = isset($userinfo->params['resource_link_title']) ? $userinfo->params['resource_link_title'] : "LTI Session";
            $thisSession->insert();
            $lsl->session_id = $thisSession->id;
            $lsl->insert();
            header("Location: editsession.php?sessionID={$thisSession->id}\n");
        }
    }
    else
    {
        if((strpos($userinfo->params['roles'], 'Instructor')!==false)||(strpos($userinfo->params['roles'], 'Administrator')!==false))
        {
            header("Location: runsession.php?sessionID={$session_id}\n");
        }
        else
        {
            $sessionMember = sessionMember::retrieve($sessionMemberUserID, $session_id);
            header("Location: vote.php?sessionID={$session_id}\n");
        }
    }
}

function checkLTISession()
{
    $secretManager = new secretManager();
    session_start();
	if((isset($_REQUEST['lti_message_type']))||(!isset($_SESSION['ltisession'])))
    {
	    session_destroy();
        session_start();
    	$_SESSION['ltisession'] = ltiSession::Create($secretManager, $_POST);
    }
    return $_SESSION['ltisession'];
}


?>
