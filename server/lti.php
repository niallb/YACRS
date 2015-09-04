<?php

include_once('config.php');
include_once('corelib/lti_session.php');
include_once('lib/secretManager.php');

$userinfo = checkLTISession();
if($userinfo==false)
    echo "Failed to launch LTI session.";
echo '<pre>'.print_r($userinfo,1).'</pre>';

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
