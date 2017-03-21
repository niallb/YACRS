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

$qId = $_REQUEST['questionID'];

$messages = chat_messages::retrieve_chat_messages_matching("question_id", $qId, 0, -1, "id DESC");

if($messages){
    $bestAnswer = "";
    $uinfo = checkLoggedInUser();

    foreach($messages as $m)
    {
        //if user is admin display a best answer selector button
        if($uinfo['isAdmin']) {
            $bestAnswer = "<button class=\"btn btn-primary btn-best-answer\" onclick=\"selectBestAsnwer($qId,$m->id)\"><i class=\"fa fa-check\" aria-hidden=\"true\"></i></button>";
        }
        if($m->student_id > 0)
        {
            $temp_user = sessionMember::retrieve_sessionMember_matching("userID",$m->student_id);
            $user      = $temp_user[0];
            echo '<div class="comment';
            if($uinfo['uname'] == $user->userID)
                echo ' me';
            echo '"><p class="bubble">'.$m->message . $bestAnswer .'</p><p class="meta"><span class="username">'.$user->name.'</span><span class="time">'.ago($m->posted).'</span></p></div>';
        }
        else
        {
            echo '<div class="info"><p class="bubble">'.$m->message. $bestAnswer .'</p><p class="meta"><span class="username">Anonymous</span><span class="time">'.ago($m->posted).'</span></p></div>';
        }
    }
} else {
    echo '<center><div class="info"><i class="bubble" style="border-radius: 3px; background: #eee">no comments on the question yet!</i></div></center>';
}

?>
