<?php
/*****************************************************************************
YACRS Copyright 2013, 2014, University of Glasgow.
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
 
$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();
$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];
$template->pageData['breadcrumb'] = $CFG['breadCrumb'];
$template->pageData['breadcrumb'] .= '| <a href="index.php">YACRS</a>';
$template->pageData['breadcrumb'] .= '| Join a session';
$template->pageData['mainBody'] = '';

$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;

//echo "<pre>".print_r($thisSession,1).'</pre>';

if(($uinfo==false)&&($thisSession==false))
{
	$template->pageData['mainBody'] .= sessionCodeinput('join.php')."<p>&nbsp;</p>";
	$template->pageData['logoutLink'] = "<a href='index.php'>Login</a>";
}
elseif(($uinfo==false)&&(isset($_REQUEST['submit'])))
{
    //sessionID selected, but no user login
    //# check if guest access allowed
    if($thisSession->allowGuests)
    {
        $smemb = new sessionMember();
        $smemb->session_id = $thisSession->id;
		$smemb->insert();
		$smemb->userID = 'Guest'.$smemb->id;
        if((isset($_REQUEST['nickname']))&&(strlen(trim($_REQUEST['nickname']))))
			$smemb->name = $_REQUEST['nickname'];
        else
            $smemb->name = 'Guest'.$smemb->id;
		$smemb->joined = time();
		$smemb->lastresponse = 0;
        $smemb->update();
        $uinfo = array('uname'=>$smemb->userID, 'gn'=>$smemb->name, 'sn'=>'(Guest)', 'email'=>'', 'isAdmin'=>false, 'sessionCreator'=>false);
        setcookie($CFG['appname'].'_login',CreateLoginCookie($uinfo), 0, '', '', false, true);
        if(($thisSession->currentQuestion==0)&&($thisSession->ublogRoom>0))
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
    else
    {
        $template->pageData['mainBody'] .= "<p style='color:red;'><b>Sorry, session {$thisSession->id} does not allow guest users. <a href='index.php'>Login</a> to join this session.</b></p>";
		$template->pageData['mainBody'] .= sessionCodeinput('join.php')."<p>&nbsp;</p>";
		$template->pageData['logoutLink'] = "<a href='index.php'>Login</a>";
    }
}
elseif($uinfo==false) //$thisSession not false, name input
{
    if($thisSession->allowGuests)
    {
		$template->pageData['mainBody'] .= sessionCodeinput('join.php', $thisSession->id)."<p>&nbsp;</p>";
		$template->pageData['logoutLink'] = "<a href='index.php?sessionID={$thisSession->id}'>Login</a>";
    }
    else
    {
        header("Location: index.php?sessionID={$thisSession->id}");
        $template->pageData['mainBody'] .= "<a href='index.php?sessionID={$thisSession->id}'>Login</a>";
    }
}
elseif($thisSession==false) //$uinfo not false, session input
{
	$template->pageData['mainBody'] .= sessionCodeinput('join.php')."<p>&nbsp;</p>";
}
else
{
    header("Location: vote.php?sessionID={$thisSession->id}");
}


echo $template->render();


?>
