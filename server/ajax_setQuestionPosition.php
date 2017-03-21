<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */


/*
 * script adds the location of
 * the question in the database
 * when the question is pinned.
 */

require_once('config.php');
require_once('lib/database.php');

$question_id    = "";
$position       = "";

if(isset($_POST['questionID'])){$question_id=$_POST['questionID'];}
if(isset($_POST['position'])){$position=$_POST['position'];}


$question = new studentsQuestion();
$question->setQuestionPoistion($question_id, $position);
