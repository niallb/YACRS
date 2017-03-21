<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */

/*
 * this script adds and removes the
 * important marker from the question
 */

require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();

$session_id     = "";
$student_id     = $uinfo['uname'];
$question_id    = "";
$liked          = "";

if (isset($_POST['sessionID'])) {
    $session_id = $_POST['sessionID'];
}
if (isset($_POST['questionID'])) {
    $question_id = $_POST['questionID'];
}
if (isset($_POST['liked'])) {
    $liked = $_POST['liked'];
}

$question_liked = new question_liked();
$question = new studentsQuestion();

if($liked == 1) {
    $question_liked->session_id = $session_id;
    $question_liked->question_id = $question_id;
    $question_liked->student_id = $student_id;
    $question_liked->liked = $liked;
    $question_liked->posted = time();

    $id = $question_liked->insert();
    $question->plusOneAttention($question_id);
    echo $id;

} else {
    $question_liked->removeLiked($question_id,$student_id);
    $question->minusOneAttention($question_id);
}