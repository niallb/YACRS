<?php
/*****************************************************************************
YACRS Copyright 2013-2016, University of Glasgow.
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

require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
$uinfo = checkLoggedInUser();

$thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}
if($thisSession->questionMode == 0)
{
    if($thisSession->currentQuestion == 0)
    {
        header( "Refresh: 10; url={$serverURL}?sessionID={$thisSession->id}" );
        echo "No active question";
    }
    else
    {
	    $qi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
	    $qu = question::retrieve_question($qi->theQuestion_id);
        $responses = response::retrieve_response_matching('question_id', $qi->id, 0, 20, "time DESC");
        //echo '<pre>'.print_r($responses, 1).'</pre>';
        foreach($responses as $r)
        {
    	     echo '<div class="comment"><p class="bubble">'.$r->value.'</p><p class="meta"><span class="time">'.ago($r->time).'</span></p></div>';
        }
    }
}
else
{
        echo __LINE__.'<br/>';
    echo "This view only works in 'teacher led' question mode.";
}

?>
