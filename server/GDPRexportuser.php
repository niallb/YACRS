<?php
/*****************************************************************************
YACRS Copyright 2013-2018, The University of Glasgow.
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
$uinfo = checkLoggedInUser();

if(($uinfo==false)||(!$uinfo['isAdmin'])||(!isset($_REQUEST['id'])))
{
    header("Location: index.php");
}

$userID = requestInt('id');

$data = new stdClass();
$data->userInfo = userInfo::retrieve_userInfo($userID);

$data->ownedSessions = session::retrieve_session_matching('ownerID', $data->userInfo->username);
$data->staffInSessions = extraTeachers::retrieve_extraTeachers_matching('teacherID', $data->userInfo->username);
$data->questionDefinitions = question::retrieve_question_matching('ownerID', $data->userInfo->username);
$data->sessionMember = sessionMember::retrieve_sessionMember_matching('userID', $data->userInfo->username);
foreach($data->sessionMember as $key=>$sm)
{
    $data->sessionMember[$key]->responses = response::retrieve_response_matching('user_id', $sm->id);
    $data->sessionMember[$key]->messages = message::retrieve_message_matching('user_id', $sm->id);
}
header('Content-Type: application/json');
echo json_encode($data);

//echo '<pre>'.print_r($data, true).'</pre>';

