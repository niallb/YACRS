<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */


/*
 * this script adds the sets the
 * best answer for the questions.
 */

require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();

$question_id    = "";
$message_id     = "";

if (isset($_POST['qID'])) {
    $question_id = $_POST['qID'];
}
if (isset($_POST['mID'])) {
    $message_id = $_POST['mID'];
}

$question = new studentsQuestion();
$question->setBestAnswer($question_id, $message_id);
