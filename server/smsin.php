<?php
include('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');


$phoneNr = $_REQUEST[$CFG['sms_phone_field']];
$message = $_REQUEST[$CFG['sms_message_field']];

if(preg_match('/\s*link\s+(\w+)\s+(\w+)\s*/i', $message, $matches))
{
    $user = userInfo::retrieveByMobileNo($phoneNr);
    if($user === false) // phone not already associate
    {
        $user = userInfo::retrieve_by_username($matches[1]);
        if($user != false)
        {
            $code = substr(md5($CFG['cookiehash'].$user->username),0,4);
            if($code == $matches[2])
            {
                $user->phone = $phoneNr;
                $user->update();
            }
            else
                logError("Attempt to link phone $phoneNr to {$matches[1]}, incorrect code");
        }
        else
            logError("Attempt to link phone $phoneNr to nonexistant user {$matches[1]}");
    }
    else
        logError("Attempt to link phone $phoneNr to {$matches[1]}, linked already to {$user->username}");
    exit();
}

if(preg_match('/\s*(join)?\s*([0-9]+)?\s*(.*)/i', $message, $matches))
{
    if(strlen($matches[2]))
        $joinSession = intval($matches[2]);
    else
        $joinSession = false;
   $response = $matches[3];
}

$user = userInfo::retrieveByMobileNo($phoneNr); // false if num not registered with one user.
$smemb = sessionMember::retrieveByMobileNo($phoneNr);
$thisSession = connectToSession($smemb, $user, $joinSession);
//echo '<pre>'.print_r($thisSession,1).'</pre>';

if(($thisSession)&&(strlen(trim($response))))
{
    if($thisSession->questionMode == 0)
    {
	    if($thisSession->currentQuestion == 0)
	    {
	        logError("No current question");
	    }
	    else
	    {
	        $qi = questionInstance::retrieve_questionInstance($thisSession->currentQuestion);
	        $qu = question::retrieve_question($qi->theQuestion_id);
	        $resp = response::retrieve($smemb->id, $qi->id);
	        $qu->definition->checkResponse($qi->id, $resp, $response);
	        if(($resp == false)&&($qu->definition->responseValue !== false))
	        {
	            $resp = new response();
	            $resp->user_id = $smemb->id;
	            $resp->question_id = $qi->id;
	            $resp->value = $qu->definition->responseValue;
	            $resp->insert();
	            $smemb->lastresponse = time();
	            $smemb->update();
	            logError("Everything OK, Logging for debug purposes");
	        }
			elseif(($thisSession->allowQuReview)&&($qu->definition->responseValue !== false))
			{
				$resp->value = $qu->definition->responseValue;
				$resp->update();
				$smemb->lastresponse = time();
				$smemb->update();
				logError("Updated OK, Logging for debug purposes");
			}
	        else
	        {
	            logError("Second attempt to answer");
	        }
	    }
    }
    else
    {
        if(!isset($thisSession->extras[currentQuestions]))
            $thisSession->extras[currentQuestions] = array();
        if(sizeof($thisSession->extras[currentQuestions])==0)
        {
	        logError("No current question");
        }
        else
        {
	        list($qtitle, $response) = explode(' ',$response,2);
            $response = trim($response);
            $matchingqis = questionInstance::retrieve_questionInstanceIDs_matching($thisSession->id, $qtitle);
            //echo 'matchingqis<pre>';
            //print_r($matchingqis);
            //echo '</pre>currentQuestions<pre>';
            //print_r($thisSession->extras[currentQuestions]);
            //echo '</pre>';
            $found = false;
            if(sizeof($matchingqis)>0)
            {
                $idx = 0;
                while((!$found)&&($idx <sizeof($matchingqis)))
                {
                    if(in_array($matchingqis[$idx],$thisSession->extras[currentQuestions]))
                    {
                    	$found = $matchingqis[$idx];
                    }
                    $idx++;
                }
            }
            if($found)
            {
		        $qi = questionInstance::retrieve_questionInstance($found);
		        $qu = question::retrieve_question($qi->theQuestion_id);
		        $resp = response::retrieve($smemb->id, $qi->id);
		        $qu->definition->checkResponse($qi->id, $resp, $response);
		        if(($resp == false)&&($qu->definition->responseValue !== false))
		        {
		            $resp = new response();
		            $resp->user_id = $smemb->id;
		            $resp->question_id = $qi->id;
		            $resp->value = $qu->definition->responseValue;
		            $resp->insert();
		            $smemb->lastresponse = time();
		            $smemb->update();
		            logError("Everything OK, Logging for debug purposes");
		        }
		        elseif(($thisSession->allowQuReview)&&($qu->definition->responseValue !== false))
                {
		            $resp->value = $qu->definition->responseValue;
		            $resp->update();
		            $smemb->lastresponse = time();
		            $smemb->update();
		            logError("Updated OK, Logging for debug purposes");
                }
                else
		        {
		            logError("Second attempt to answer");
		        }
            }
            else
            {
	            logError("Unable to find question ($qtitle)");
            }

        }
    }
}


function connectToSession(&$smemb, $user, $joinSession)
{
    global $phoneNr;
	if($joinSession)
	    $thisSession = session::retrieve_session($joinSession);
	elseif($smemb)
	    $thisSession = session::retrieve_session($smemb->session_id);
	else
    {
        logError("Unable to identify session");
	    return false;
    }
    if(!$thisSession)
    {
        logError("Invalid session number");
        return false; 
    }
//    if((!$thisSession->allowGuests)&&(!$user))       // Temporally commented out to allow later association of numbers
//    {
//        logError("Attempt to join non-guest session with unknown phone number.");
//	    return false;
//    }
	if((!$smemb)||($smemb->session_id != $thisSession->id))
	{
	    $smemb = new sessionMember();
	    $smemb->session_id = $thisSession->id;
	    if($user !== false)
	    {
		    $smemb->userID = $user->username;
			$smemb->name = $user->name;
		    $smemb->email = $user->email;
		    $smemb->nickname = $user->nickname;
	    }
	    $smemb->mobile = $phoneNr;
		$smemb->joined = time();
		$smemb->lastresponse = time();
		$smemb->insert();
	    if(!$user)
	    {
			$smemb->userID = 'Guest'.$smemb->id;
	        $smemb->nickname = $smemb->userID;
	        $smemb->update();
	    }
	}
    return $thisSession;
}

function logError($msg)
{
    $fp = fopen('logs/smserrors.txt','a');
    foreach($_REQUEST as $k=>$v)
    $msg .= " $k=>$v;";
    fwrite($fp, $msg."\n");
    fclose($fp);
}


?>
