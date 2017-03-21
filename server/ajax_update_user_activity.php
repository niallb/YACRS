<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */


/*
 * keeps track of the activity of the user when they are on viewQuestion page.
 */

require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();
$student_id = $uinfo['uname'];

if(isset($_POST['session_id'])){
    $session_id = $_POST['session_id'];
}

$smemb = sessionMember::retrieve($student_id , $session_id);
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