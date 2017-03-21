<?php

/*****************************************************************************
YACRS Copyright 2013, University of Glasgow.
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

$messages = message::getSessionMessages($_REQUEST['sessionID'], 25);
foreach($messages as $m)
{
    if($m->user_id > 0)
    {
		$user = sessionMember::retrieve_sessionMember($m->user_id);
    	echo '<div class="comment';
    	if($uinfo['uname'] == $user->userID)
    		echo ' me';
    	echo '"><p class="bubble">'.$m->message.'</p><p class="meta"><span class="username">'.$user->name.'</span><span class="time">'.ago($m->posted).'</span></p></div>';
    }
    else
    {
	    echo '<div class="info"><p class="bubble">'.$m->message.'</p><p class="meta"><span class="username">General Information</span><span class="time">'.ago($m->posted).'</span></p></div>';
    }
}

?>
