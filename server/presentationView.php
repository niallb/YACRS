<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" media="Screen" href="html/yacrs-new-theme-presentation.css" />
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
include_once('corelib/mobile.php');
require_once('lib/shared_funcs.php');

$template = new templateMerge($TEMPLATE);

$uinfo = checkLoggedInUser();

$template->pageData['pagetitle'] = $CFG['sitetitle'];
$template->pageData['homeURL'] = $_SERVER['PHP_SELF'];

$pinnedQuestions = "";


if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
    $serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
    $serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];

if($uinfo==false)
{
    header("Location: index.php");
    // actually should allow join a session as guest...
}
else
{
    $template->pageData['mainBody'] = '';
    $thisSession = isset($_REQUEST['sessionID'])? session::retrieve_session($_REQUEST['sessionID']):false;
    if($thisSession == false)
    {
        $template->pageData['mainBody'] = "<p><b>Invalid session or missing number.</b></p>".sessionCodeinput();
    }
    else
    {
        //load all the necessary elements in the page
        if(isset($_REQUEST['sessionID']))
            $sessionId = intval($_REQUEST['sessionID']);
        $template->pageData['mainBody'] .= addToggleButton();
        $template->pageData['mainBody'] .= addOpenPinButton();
        $template->pageData['mainBody'] .= displayQuestions($sessionId);
        $template->pageData['mainBody'] .= pinContainer();

    }
    $template->pageData['logoutLink'] = loginBox($uinfo);

}

echo $template->render();

function displayQuestions($sessionId)
{

    global $uinfo;
    global $pinnedQuestions;
    $loggedInUser = $uinfo['uname'];
    $questions = studentsQuestion::retrieve_sessionQuestions($sessionId);
    $out = "<div class='message-container'>";

    for ($i = 0; $i < sizeof($questions); $i++) {

        $question       = $questions[$i];
        $qId            = $questions[$i]->id;
        $needsAttention = $questions[$i]->needs_attention;
        $questionText   = $questions[$i]->question;
        $pinLocation    = $questions[$i]->pin_location;
        $timeadded      = $questions[$i]->timeadded;

        if($question) {

            //dynamic font algorithm
            //changes size depending on question's importance.
            $fontSize = (13 + ($needsAttention / 0.5));
            if ($fontSize > 20) {
                $fontSize = "20px";
            } else {
                $fontSize = $fontSize . "px";
            }

            $timeAdded     = dataConnection::time2db($timeadded);
            if($timeAdded){
                $timeNow = dataConnection::time2db(time());

                $date1Timestamp = strtotime($timeAdded);
                $date2Timestamp = strtotime($timeNow);

                //find difference between two times
                $difference = round(abs($date1Timestamp - $date2Timestamp));
            }

            //float the question to the right place
            //even after the page refreshes.
            $JUMP   = 1;
            $bottom = "bottom: ".($difference*$JUMP)."px";

            $pinned = 0;
            //add question to the pin container
            if(intval($pinLocation) > 0){
                $clear = "<span class='bubble-for-clear' style='cursor: pointer'>
                            <i class='fa fa-minus-circle pin-clear' onclick='clearPinned($qId)' aria-hidden='true'></i>
                          </span>";
                $link = "<a href='ask_question_chat.php?quId=$qId&sessionID=$sessionId'>$questionText</a>";
                $pinnedQuestions .= "<div class='pin-container-question pinned-$qId'>$link $clear</div>";
                $pinned = 1;
            }

            $uinfo = checkLoggedInUser();

            if($i % 2 == 0){
                $pos    = "right";
                $float  = "float: right !important;";
                $badgeSide = "right: -10px !important";
                $arrow = "<div class='arrow-right'></div>";
                $clear = "";
                if(true) {
                    $pin = "<span onclick='pinQuestion($qId, this, $pinned, $sessionId)' class='bubble-for-badge pin' style='left: -10px !important; right: auto !important; background-color: #009688;'>
                            <img class='card-badge' src='html/icons/icon-pin-right.png'/>
                        </span>";
                }
            } else {
                $pos    = "left";
                $float  = "float: left !important;";
                $badgeSide = "left: -16px !important; right: auto !important";
                $arrow = "<div class='arrow-left'></div>";
                $clear = "<div style='clear:both'></div>";
                if(true) {
                    $pin = "<span onclick='pinQuestion($qId, this, $pinned, $sessionId)' class='bubble-for-badge pin' style='background-color: #009688;'>
                            <img class='card-badge' src='html/icons/icon-pin-left.png'/>
                        </span>";
                }
            }

            $beingDiscussed = ifBeingDiscussed($question);

            $showBadge = "<span class='bubble-for-badge badge-discussion-$qId faa-pulse animated' style='$badgeSide'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

            $hideBadge = "<span class='bubble-for-badge badge-discussion-$qId faa-pulse animated' style='display: none; $badgeSide'>
                            <img class='card-badge' src='html/icons/badge-discussion.png'/>
                          </span>";

            //to get new questions after the ID
            $hiddenQiD = "<input type='hidden' class='lastMsgID' style='float: $pos' value='$qId'>";

            //check if there is any reaction on question
            $badge = ($beingDiscussed) ? $showBadge : $hideBadge;
            $out .= "<a href='ask_question_chat.php?quId=$qId&sessionID=$sessionId'>
                     <div class='col-sm-12 ask-question question-$qId' data-attention='$needsAttention' style='$float $bottom'>
                        <div class='question-content'>
                            $hiddenQiD
                            <a class='question-link' href='ask_question_chat.php?quId=$qId&sessionID=$sessionId'>
                                <p class='txt-question-$qId' style='font-size:$fontSize'>" . $questionText . "</p>
                            </a>
                            <div style='display: flex; text-align: center; color: #888'>
                            $badge
                            $pin
                            </div>
                            $arrow
                        </div>
                     </div></a>$clear";
        } else { $out .= ""; }
    }
    $out .= "</div>";
    return $out;
}

function pinContainer(){

    global $pinnedQuestions;

    $closeBtn = "<span class='bubble-for-badge badge-container-close' style='cursor: pointer; right: 10px; padding: 10px; top: 10px;'>
                    <img class='card-badge' src='html/icons/icon-close.png'/>
                </span>";

    $out = "<div class='pin-container'> $closeBtn 
                <h1 style='margin-left: 20px'>Pinned Question</h1>
                <div class='pinned-questions'></div>
                $pinnedQuestions
            </div>";

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
        if($difference < 60*100) return true;
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


function addToggleButton(){
    $out = "<span class='bubble-for-badge badge-toggle' style='cursor: pointer'>
            <i class='fa fa-pause' aria-hidden='true' style='color: #ececec'></i>
            </span>";
    return $out;
}

function addOpenPinButton(){
    $out = "<span class='bubble-for-badge badge-pin-container' style='cursor: pointer; right: auto; left: 10px; padding: 10px; top: 10px;'>
            <img class='card-badge' src='html/icons/icon-pin-left.png'/>
            </span>";
    return $out;
}

?>
<script src="javascript/jquery.min.js"></script>
<script src="javascript/presentationView.js"></script>


<script>
    //checks for the new questions every second.
    setInterval("updateQuestions()",1000);
    function updateQuestions() {
        var lastMsgVal = 0;
        var position        = "";
        var divList    = $(".lastMsgId");
        divList.sort(function(a, b){
            return parseInt(a.value) > parseInt(b.value) ? 1 : -1;
        });
        var lastIdInput = divList.last()[0];
        if(lastIdInput != null){
            lastMsgVal  = lastIdInput.value;
            position    = $(lastIdInput).css("float");
        }
        $.ajax({
            type: "POST",
            url: 'ajax_new_presentation_questions.php',
            data: {
                sID: <?php echo $sessionId; ?>,
                mID: lastMsgVal,
                pos: position
            },
            success: function(html) {
                if (html.indexOf("ask-question") >= 0){
                    $(".message-container").append(html);
                }
            }
        });
    }

    //checks if the state of question has changed, every 3 seconds.
    setInterval("checkFontSize()",3000);
    function checkFontSize() {
        $.ajax({
            type: "POST",
            url: 'ajax_attention_changes.php',
            data: {
                sID: <?php echo $sessionId; ?>
            },
            success: function(output) {
                if (output){
                    var pair = output.split(',');
                    for(var i = 0; i < (pair.length-1); i++){
                        var val = pair[i].split("|");

                        var q       = parseInt(val[0]);
                        var font    = parseInt(val[1]);
                        var convo   = parseInt(val[2]);

                        //change font size if attention changes
                        var fontSize = (13+(font/0.5));
                        if(fontSize > 20) fontSize = "20px";
                        else fontSize = fontSize+"px";
                        $(".txt-question-"+q).css("font-size",fontSize);

                        //open question if it is being discussed
                        if(convo == 1){
                            $(".badge-discussion-"+q).show();
                            $(".badge-close-"+q).hide();
                            $(".question-"+q).click();
                        }
                    }
                }
            }
        });
    }
</script>