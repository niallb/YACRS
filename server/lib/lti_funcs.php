<?php
include_once(ROOT_PATH.'corelib/lti_session.php');

function getLTISessionID()
{
    session_start();
	if(!isset($_SESSION['ltisession']))
        return false;
    $consumer = lticonsumer::retrieve_by_consumer_key($_SESSION['ltisession']->params['oauth_consumer_key']);
	$session_id = ltisessionlink::retrieve_session_id($consumer->id, $_SESSION['ltisession']->params['resource_link_id']);
    return $session_id;
}

function isLTIStaff() // Instructor or Administrator
{
    session_start();
	if(!isset($_SESSION['ltisession']))
        return false;
    elseif((strpos($_SESSION['ltisession']->params['roles'], 'Instructor')!==false)||(strpos($_SESSION['ltisession']->params['roles'], 'Administrator')!==false))
        return true;
    else
        return false;
}

?>
