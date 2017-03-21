<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */


/*
 * this script adds messages in the
 * questions discussion thread
 */


require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();

$session_id     = "";
$student_id     = $uinfo['uname'];
$question_id    = "";
$message        = "";

if(isset($_POST['session_id'])){
    $session_id=$_POST['session_id'];
}
if(isset($_POST['question_id'])){
    $question_id=$_POST['question_id'];
}
if(isset($_POST['chatMessage'])){
    $message = htmlspecialchars($_POST['chatMessage']);
}

$chat_message = new chat_messages();

$chat_message->session_id   = $session_id;
$chat_message->question_id  = $question_id;
$chat_message->student_id   = $student_id;
$chat_message->message      = $message;
$chat_message->posted       = time();
$chat_message->viewed       = false;

$id = $chat_message->insert();
echo $id;