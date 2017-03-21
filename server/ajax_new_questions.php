<?php
/**
 * User: Hamza Tanveer
 * YALIS Update
 */

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');

$sessionId = isset($_POST['sID'])? $_POST['sID']:"0";
$lastMsgID = isset($_POST['mID'])? $_POST['mID']:"0";

echo displayNewQuestions($sessionId, $lastMsgID);

/*
 * This function extracts all the questions from the database
 * belonging to the same session and displays them on the screen.
 * */
function displayNewQuestions($sessionId, $lastMsgID)
{
    $uinfo = checkLoggedInUser();
    $loggedInUser = $uinfo['uname'];
    $questions = studentsQuestion::retrieve_sessionNewQuestions($sessionId, $lastMsgID);
    $out = "";
    if($questions) {
        for ($i = 0; $i < sizeof($questions); $i++) {

            $question       = $questions[$i];
            $qId            = $questions[$i]->id;
            $needsAttention = $questions[$i]->needs_attention;
            $studentId      = $questions[$i]->student_id;
            $questionText   = $questions[$i]->question;

            if($question) {

                //dynamic font algorithm
                //changes size depending on question's importance.
                $fontSize = (13 + ($needsAttention / 0.5));
                if ($fontSize > 40) {
                    $fontSize = "40px";
                } else {
                    $fontSize = $fontSize . "px";
                }

                $ifActive       = ifActive($question);
                $beingDiscussed = ifBeingDiscussed($question);

                $liked = question_liked::checkIfLiked($qId, $loggedInUser);

                //to get new questions after the ID
                $hiddenQiD = "<input type='hidden' class='lastMsgID' value='$qId'>";

                $buttons = "<a class='card-buttons comments' href='ask_question_chat.php?quId=$qId&sessionID=$sessionId'>
                            <i class='fa fa-comments-o' aria-hidden='true'></i> discuss
                        </a>";

                if ($liked) {
                    $buttons .= "<span class='card-buttons button-pressed'onclick='plusplusLike($sessionId,$qId,0)'>
                                <i class='fa fa-exclamation' aria-hidden='true'></i> important
                              </span>";
                } else {
                    $buttons .= "<span class='card-buttons badge-question-$qId' onclick='plusplusLike($sessionId,$qId,1)'>
                                <i class='fa fa-exclamation' aria-hidden='true'></i> important
                              </span>";
                }

                $showBadge = "<span class='bubble-for-badge badge-discussion-$qId'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                $hideBadge = "<span class='bubble-for-badge badge-discussion-$qId' style='display: none'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

                //check if there is any reaction on question
                if ($ifActive || $beingDiscussed) {
                    $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                    $out .= "<div class='col-sm-12 ask-question question-$qId' data-attention='$needsAttention' data-id='$qId'>
                            <div class='question-content'>
                                $hiddenQiD
                                <p class='question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge badge-close-$qId' style='display: none'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                $buttons
                                </div>
                            </div>
                         </div>";
                } else {
                    $hide  = "hide-card-details";
                    $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
                    $out .= "<div class='col-sm-12 ask-question question-$qId hide-unImpQuestion' data-attention='$needsAttention' data-id='$qId'>
                            <div class='question-content $hide'>
                                $hiddenQiD
                                <p class='question-$qId' style='font-size:$fontSize' title='Asked By: " . $studentId . "'>" . $questionText . "</p>
                                <hr/>
                                <div style='display: flex; text-align: center; color: #888'>
                                $badge
                                <span class='bubble-for-badge badge-close-$qId' onclick='closeQuestionCard($qId)'>
                                    <img class='card-badge' src='html/icons/icon-close.png'/>
                                </span>
                                $buttons
                                </div>
                            </div>
                         </div>";
                }
            }
        }
    }
    return $out;
}

/*
 * check if question has been posted within last
 * two hours than keep it open!
 */
function ifActive($q){
    $posted         = $q->timeadded;

    $timePosted     = dataConnection::time2db($posted);

    if($timePosted){
        $timeNow = dataConnection::time2db(time());

        $date1Timestamp = strtotime($timePosted);
        $date2Timestamp = strtotime($timeNow);

        //find difference between two times
        $difference = round(abs($date1Timestamp - $date2Timestamp) / 60,2);
        if($difference < 60*2) return true;
    }
    return false;
}

/*
 * checks if the question has been discussed in the last two hours
 */
function ifBeingDiscussed($q){

    $qId            = $q->id;

    $messages = chat_messages::retrieve_chat_messages_matching("question_id",$qId,"","","id DESC");

    if($messages) {
        $date1 = dataConnection::time2db($messages[0]->posted);
        $date2 = dataConnection::time2db(time());

        $date1Timestamp = strtotime($date1);
        $date2Timestamp = strtotime($date2);

        $difference = round(abs($date1Timestamp - $date2Timestamp) / 60,2);

        if(($difference < 60*2)){
            return true;
        }else {
            return false;
        }
    } else {
        return false;
    }
}
