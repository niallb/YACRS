<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');

/*
 * find the change in the state of question
 * changes can be in:
 * if new important markers received
 * if question being discussed
 */

$sessionId = isset($_REQUEST['sID'])? $_REQUEST['sID']:"0";
echo getAllQuestions($sessionId);
function getAllQuestions($sessionId)
{
    $questions = studentsQuestion::retrieve_sessionQuestions($sessionId);
    $out = "";
    if($questions) {
        for ($i = 0; $i < sizeof($questions); $i++) {

            $qId            = $questions[$i]->id;
            $needsAttention = $questions[$i]->needs_attention;
            $activeConvo    = 0;

            $messages = chat_messages::retrieve_chat_messages_matching("question_id",$qId,"","","id DESC");

            if($messages) {
                $date1 = dataConnection::time2db($messages[0]->posted);
                $date2 = dataConnection::time2db(time());

                $date1Timestamp = strtotime($date1);
                $date2Timestamp = strtotime($date2);

                $difference = round(abs($date1Timestamp - $date2Timestamp) / 60, 2);

                if (($difference < 60 * 2)) {
                    $activeConvo = 1;
                }
            }
            $out .= $qId."|".$needsAttention."|".$activeConvo.",";
        }
    }
    return $out;
}
