<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/ajax.php');
include_once('corelib/mobile.php');
$template = new templateMerge($TEMPLATE);
$sessionID = requestInt('sessionID');
if($deviceType=='mobile')
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$sessionID}&mode=computer'>Use computer mode</a>";
else
    $template->pageData['modechoice'] = "<a href='{$_SERVER['PHP_SELF']}?sessionID={$sessionID}&mode=mobile'>Use mobile mode</a>";

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = "<a href='http://www.gla.ac.uk/'>University of Glasgow</a> | <a href='http://www.gla.ac.uk/services/learningteaching/'>Learning & Teaching Centre</a> ";
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';

if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
	$serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
	$serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
	$thisSession = requestSet('sessionID')? session::retrieve_session($sessionID):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        $smemb = sessionMember::retrieve($uinfo['uname'], $thisSession->id);
        if($smemb == false)
        {
        	$smemb = new sessionMember();
            $smemb->session_id = $thisSession->id;
			$smemb->userID = $uinfo['uname'];
			$smemb->name = $uinfo['gn'].' '.$uinfo['sn'];
			$smemb->email = $uinfo['email'];
			$smemb->joined = time();
			$smemb->lastresponse = time();
			$smemb->insert();
        }
        else
        {
			$smemb->lastresponse = time();
			$smemb->update();
        }

        if((requestSet('submit'))&&(requestSet('mublog')))
        {
            $post = trim(requestRaw('mublog',''));
            if(strlen($post))
            {
	            $msg = new message();
	            $msg->user_id = $smemb->id;
	            $msg->posted = time();
	            $msg->message = $post;
	            $msg->session_id = $thisSession->id;
	            $msg->insert();
	            preg_match_all('/#[^s]+/', $post, $matches);
	            foreach($matches[0] as $mtag)
	            {
	                $msg->addTag($mtag);
	            }
            }

        }

		$template->pageData['afterContent'] = getAJAXScript($thisSession->id);
        $template->pageData['breadcrumb'] .= "| <a href='vote.php?sessionID={$thisSession->id}'>{$thisSession->title}</a>";
		$template->pageData['breadcrumb'] .= "| Disscuss or &mu;blog";

        $template->pageData['mainBody'] .= "Add a post:<br/>";
        $template->pageData['mainBody'] .= "<form id='mublogForm' method='POST' action='chat.php'>";
        $template->pageData['mainBody'] .= "<input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
        $template->pageData['mainBody'] .= "<textarea name='mublog' rows='3' cols='25'></textarea><br/>";
        $template->pageData['mainBody'] .= "<input type='submit' name='submit' value='Submit'/>";
        $template->pageData['mainBody'] .= "</form>";
        $template->pageData['mainBody'] .= "<div id='messages' style='border : 1px solid #00008B;'></div>";
        $template->pageData['mainBody'] .= "<p><a href='vote.php?sessionID={$thisSession->id}&continue=1'>Questions</a></p>";
    }
	//$template->pageData['mainBody'] .= '<pre>'.print_r($uinfo,1).'</pre>';
	//$template->pageData['mainBody'] .= '<pre>'.print_r($smemb,1).'</pre>';
	$template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function getAJAXScript($sessionID)
{
	return getUBlogUpdateAJAXScript($sessionID);
}


?>
