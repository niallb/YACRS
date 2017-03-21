<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */


/*
 * adds the question asked by student
 * to the database.
 */

require_once('config.php');
require_once('lib/database.php');

$uinfo = checkLoggedInUser();

$session_id = "";
$student_id = $uinfo['uname'];
$question   = "";

if(isset($_POST['session_id'])){
    $session_id = $_POST['session_id'];
}
if(isset($_POST['question'])){
    $question = htmlspecialchars($_POST['question']);
}

$students_question = new studentsQuestion();

$students_question->student_id  = $student_id;
$students_question->session_id  = $session_id;
$students_question->question    = $question;
$students_question->timeadded   = time();
$students_question->answer_id   = 0;
$students_question->viewed      = 0;
$id = $students_question->insert();

echo $id;