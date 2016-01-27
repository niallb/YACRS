<?php

/*****************************************************************************
YACRS Copyright 2013, University of Glasgow.
Written by Niall S F Barr (niall.barr@glasgow.ac.uk, niall@nbsoftware.com)

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*****************************************************************************/
require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
require_once('lib/questionTypes.php');
$uinfo = checkLoggedInUser();

$errors = array();
$data = array();
if(!isset($_REQUEST['action']))
{
    sendResponse(false, $errors, false);
    exit();
}
elseif($_REQUEST['action'] == 'login')
{
	if($uinfo == false)
    {
        $errors[] = "Incorrect login";
        sendResponse('login', $errors, false);
    }
    elseif(!$uinfo['sessionCreator'])
    {
        $errors[] = "User {$uinfo['uname']} does not have permission to create and run sessions.";
        sendResponse('login', $errors, false);
    }
    else
    {
	    //# System wide q types, whether class lists available and eventually user prefs.
        $data = array('serverInfo'=>array('courseIdSupported'=>isset($CFG['rosterservice']), 'globalQuType'=>array()));
        $sqs = systemQuestionLookup::all();
        if(sizeof($sqs)==0)
        {
            createBasicGlobalQuestion("MCQ A-D", "A\nB\nC\nD\n");
            createBasicGlobalQuestion("MCQ A-E", "A\nB\nC\nD\nE\n");
	        $sqs = systemQuestionLookup::all();
        }
        if(sizeof($sqs)<3)
        {
            createBasicGlobalQuestion("MCQ A-F", "A\nB\nC\nD\nE\nF\n");
            createBasicGlobalQuestion("MCQ A-H", "A\nB\nC\nD\nE\nF\nG\nH");
	        $sqs = systemQuestionLookup::all();
        }
        if(sizeof($sqs)<5)
        {
            createBasicGlobalQuestion("MRQ A-D", "*A\n*B\n*C\n*D\n");
            createBasicGlobalQuestion("MRQ A-E", "*A\n*B\n*C\n*D\n*E\n");
            createBasicGlobalQuestion("MRQ A-F", "*A\n*B\n*C\n*D\n*E\n*F\n");
            createBasicGlobalQuestion("MRQ A-H", "*A\n*B\n*C\n*D\n*E\n*F\n*G\n*H");
            createTextinputGlobalQuestion();
	        $sqs = systemQuestionLookup::all();
        }
        foreach($sqs as $sq)
        {
        	$data['serverInfo']['globalQuType'][] = array('attributes'=>array('id'=>$sq->qu_id), 0=>$sq->name);
        }
        sendResponse('login', $errors, $data);
    }
    exit();
}
else
{
	if($uinfo == false)
    {
        $errors[] = "You must be logged in first.";
	    sendResponse($_REQUEST['action'], $errors, false);
        exit();
    }

    switch($_REQUEST['action'])
    {
    case 'activate':
        if(isset($_REQUEST['id']))
        {
        	$session = session::retrieve_session($_REQUEST['id']);
            if($session == false)
                $errors[] = "Session {$_REQUEST['id']} not found.";
            elseif(!checkPermission($uinfo, $session))
                $errors[] = "You do not have permission to run session {$_REQUEST['id']}.";
            else
            {
                if(isset($_REQUEST['qid']))
                {
	                $qu = question::retrieve_question($_REQUEST['qid']);
	                $qi = $session->addQuestion($qu);
                    $qi->title = "Q".(questionInstance::count("inSession_id", $session->id));
	                $session->currentQuestion = $qi->id;
	        		$session->update();
	                // Save screenshot and add it's name to $qi
                    $qi->starttime = time();
                    //print_r($_FILES);
                    if(isset($_FILES['screenshot']))
                    {
                    	$path = $CFG['screenshotpath'].'/'.md5($session->ownerID).'/'.$session->id.'/';
                        mymkdir($path);
                        $path .= $_FILES['screenshot']['name'];
                        copy($_FILES['screenshot']['tmp_name'], $path);
                        $qi->screenshot = $path;
                    }
                    if($session->defaultQuActiveSecs > 0)
                        $qi->endtime = $qi->starttime + $session->defaultQuActiveSecs;
                    else
                        $qi->endtime = 0;
                    $qi->update();
                    //print_r($session);
                    //print_r($qi);
                }
                else
                {
                    $errors[] = "Missing question ID";
                }
            }
        }
        break;
    case 'deactivate':
        if(isset($_REQUEST['id']))
        {
        	$session = session::retrieve_session($_REQUEST['id']);
            if($session == false)
                $errors[] = "Session {$_REQUEST['id']} not found.";
            elseif(!checkPermission($uinfo, $session))
                $errors[] = "You do not have permission to run session {$_REQUEST['id']}.";
            else
            {
                $qi = questionInstance::retrieve_questionInstance($session->currentQuestion);
                $session->currentQuestion = 0;
        		$session->update();
                $qi->endtime = time();
                $qi->update();
            }
        }
        break;
    case 'addtime':
        if(isset($_REQUEST['id']))
        {
        	$session = session::retrieve_session($_REQUEST['id']);
            if($session == false)
                $errors[] = "Session {$_REQUEST['id']} not found.";
            elseif(!checkPermission($uinfo, $session))
                $errors[] = "You do not have permission to run session {$_REQUEST['id']}.";
            else
            {
                $qi = questionInstance::retrieve_questionInstance($session->currentQuestion);
                $qi->endtime += 20;
                $qi->update();
            }
        }
        break;
    case 'getqids':
        if(isset($_REQUEST['id']))
        {
        	$session = session::retrieve_session($_REQUEST['id']);
            if($session == false)
                $errors[] = "Session {$_REQUEST['id']} not found.";
            elseif(!checkPermission($uinfo, $session))
                $errors[] = "You do not have permission to run session {$_REQUEST['id']}.";
            else
            {
                if(strlen($session->questions))
	                $data['qid'] = explode(',', $session->questions);
                else
                	$data['qid'] = array();

                //echo '<pre>'.print_r($data,1).'</pre>';
            }
        }
        break;
    case 'quinfo':
        if(isset($_REQUEST['qiID']))
            $qiID = $_REQUEST['qiID'];
        else
        {
        	$sess = session::retrieve_session($_REQUEST['id']);
            $qiID = $sess->currentQuestion;
        }
        $qi = questionInstance::retrieve_questionInstance($qiID);
	    $userCount = sessionMember::count("session_id", $_REQUEST['id']);
	    $activeCount = sessionMember::countActive($_REQUEST['id']);
        if($qi===false)
        {
            //$errors[] = "No active question or invalid question instance ID (qiID).";
	        $data['questionResponseInfo'] = array('attributes'=>array('questiontype'=>'none', 'id'=>0), 'activeUsers'=>$activeCount, 'totalUsers'=>$userCount);
        }
        else
        {
			$count = response::count('question_id', $qiID);
	        $data['questionResponseInfo'] = array('attributes'=>array('questiontype'=>$qi->title, 'id'=>$qiID), 'activeUsers'=>$activeCount, 'totalUsers'=>$userCount, 'responseCount'=>$count);
	        if(($qi->starttime > 0)&&($qi->endtime > $qi->starttime))
	        {
	            $ttg = $qi->endtime - time();
	            if($ttg < 0)
	                $ttg=0;
	            $data['questionResponseInfo']['timeToGo'] = $ttg;
	        }
            elseif(($qi->starttime > 0)&&($qi->endtime <= $qi->starttime))
	        {
	            $tg = time()-$qi->starttime;
	            if($tg < 0)
	                $tg=0;
	            $data['questionResponseInfo']['timeGone'] = $tg;
	        }
            //if(($qi->endtime > $qi->starttime)&&($qi->endtime <= time()))
            if($qi->endtime > $qi->starttime)  // support dynamic graphs
            {
                $data['questionResponseInfo']['optionInfo'] = array();
            	// add option counts if an MCQ
			    $qu = question::retrieve_question($qi->theQuestion_id);
                if($qu !== false)
                {
				    $labels = $qu->definition->getGraphLabels();
                    if((is_array($labels))&&(sizeof($labels)))
                    {
					    $count = array_fill_keys(array_keys($labels), 0);
					    $responses = response::retrieve_response_matching('question_id', $qiID);
	                    if($responses)
	                    {
						    foreach($responses as $r)
						    {
						        if(strlen($r->value))
						        {
							        $votes = explode(',',$r->value);
							        foreach($votes as $v)
							        {
						                $count[$v]++;
							        }
						        }
						    }
	                    }
		                foreach($count as $label=>$value)
		                {
		                    $data['questionResponseInfo']['optionInfo'][] = array('title'=>$labels[$label], 'count'=>$value);
		                }
                    }
                }
            }
        }
        break;
    case 'sessionlist':
   	    $sessions = session::retrieve_session_matching('ownerID', $uinfo['uname']);
        if($sessions === false)
            $sessions = array();
        $sessions = array_merge($sessions, session::teacherExtraSessions($uinfo['uname']));
        $data['sessionInfo'] = array();
        if($sessions !== false)
        {
	        foreach($sessions as $s)
	        {
	            $ctime = strftime("%Y-%m-%d %H:%M", $s->created);
	            $data['sessionInfo'][] = array('attributes'=>array('id'=>$s->id),'ownerID'=>$s->ownerID, 'title'=>$s->title, 'created'=>$ctime);
	        }
        }
        break;
    case 'sessiondetail':
        if(isset($_REQUEST['id']))
        {
        	$session = session::retrieve_session($_REQUEST['id']);
            if($session == false)
                $errors[] = "Session {$_REQUEST['id']} not found.";
            elseif(!checkPermission($uinfo, $session))
                $errors[] = "You do not have permission to modify session {$_REQUEST['id']}.";
        }
        else
        {
            $session = new session();
            $session->ownerID = $uinfo['uname'];
        }
        if(sizeof($errors) == 0)
        {
            $altered = false;
			if((isset($_REQUEST['title']))&&($_REQUEST['title'] != $session->title))
			{
			    $session->title = $_REQUEST['title'];
			    $altered = true;
			}
			if((isset($_REQUEST['allowGuests']))&&($_REQUEST['allowGuests'] != $session->allowGuests))
			{
			    $session->allowGuests = $_REQUEST['allowGuests'];
			    $altered = true;
			}
			if((isset($_REQUEST['visible']))&&($_REQUEST['visible'] != $session->visible))
			{
			    $session->visible = $_REQUEST['visible'];
			    $altered = true;
			}
			if((isset($_REQUEST['questionMode']))&&($_REQUEST['questionMode'] != $session->questionMode))
			{
			    $session->questionMode = $_REQUEST['questionMode'];
			    $altered = true;
			}
			if((isset($_REQUEST['defaultQuActiveSecs']))&&($_REQUEST['defaultQuActiveSecs'] != $session->defaultQuActiveSecs))
			{
			    $session->defaultQuActiveSecs = $_REQUEST['defaultQuActiveSecs'];
			    $altered = true;
			}
			if((isset($_REQUEST['allowQuReview']))&&($_REQUEST['allowQuReview'] != $session->allowQuReview))
			{
			    $session->allowQuReview = $_REQUEST['allowQuReview'];
			    $altered = true;
			}
			if((isset($_REQUEST['ublogRoom']))&&($_REQUEST['ublogRoom'] != $session->ublogRoom))
			{
			    $session->ublogRoom = $_REQUEST['ublogRoom'];
			    $altered = true;
			}
			if((isset($_REQUEST['maxMessagelength']))&&($_REQUEST['maxMessagelength'] != $session->maxMessagelength))
			{
			    $session->maxMessagelength = $_REQUEST['maxMessagelength'];
			    $altered = true;
			}
            if($session->id > 0)
                $session->update();
            else
                $session->insert();
            
			if((isset($_REQUEST['courseIdentifier']))&&($_REQUEST['courseIdentifier'] != $session->courseIdentifier))
			{
			    $session->courseIdentifier = $_REQUEST['courseIdentifier'];
                if(strlen($session->courseIdentifier))
			        enrolStudents($session->id, $session->courseIdentifier);
			    $altered = true;
			}
			$data['sessionDetail']['attributes'] = array('id'=>$session->id);
			$data['sessionDetail']['title'] = $session->title;
			$data['sessionDetail']['courseIdentifier'] = $session->courseIdentifier;
			$data['sessionDetail']['allowGuests'] = $session->allowGuests;
			$data['sessionDetail']['visible'] = $session->visible;
			$data['sessionDetail']['questionMode'] = $session->questionMode;
			$data['sessionDetail']['defaultQuActiveSecs'] = $session->defaultQuActiveSecs;
			$data['sessionDetail']['allowQuReview'] = $session->allowQuReview;
			$data['sessionDetail']['ublogRoom'] = $session->ublogRoom;
			$data['sessionDetail']['maxMessagelength'] = $session->maxMessagelength;
        }
        break;
    default:
        $errors[] = "Unrecognised action '{$_REQUEST['action']}'.";
        break;
    }
    if(sizeof($errors))
    {
	    sendResponse($_REQUEST['action'], $errors, false);
    }
    else
    {
	    sendResponse($_REQUEST['action'], $errors, $data);
    }
}

function sendResponse($messageName, $errors, $data)
{
 	header ("Content-Type:text/xml");
	echo "<?xml version=\"1.0\"?>\n";
	echo "<YACRSResponse version=\"0.4.7\"";
    if($messageName)
    {
    	echo " messageName='$messageName'";
    }
    echo ">\n";
    if(sizeof($errors) == 0)
		echo "<errors/>\n";
    else
    {
		echo "<errors>\n";
    	foreach($errors as $error)
            echo "<error>$error</error>\n";
		echo "</errors>\n";
    }
    if($data === false)
		echo "<data/>\n";
    else
    {
 	  	echo array2XML('data', $data);
    }
	echo "</YACRSResponse>";
}

function array2XML($name, $data)
{
   	$out = '';
    if(is_array($data))
    {
        if(is_assoc($data))
        {
            $out .= "<$name";
		    if(isset($data['attributes']))
		    {
		        foreach($data['attributes'] as $k=>$v)
		        {
			        if(is_bool($v))
			            $v2 = $v?'1':'0';
			        else
				       	$v2 = htmlentities($v);
		        	$out .= " $k=\"{$v2}\"";
		        }
		    }
		    if((isset($data['attributes']))&&(isset($data[0]))&&(sizeof($data)==2))
		    {
		   		$out .= ">";
		       	$out .= htmlentities($data[0]);
            }
            else
            {
 			    $out .= ">\n";
		        foreach($data as $k=>$v)
		        {
			        if($k !== 'attributes')
			        {
	    		 		$out .= array2XML($k, $v);
			        }
		        }
            }
            $out .= "</$name>\n";
        }
        else
        {
	        foreach($data as $k=>$v)
	        {
		 		$out .= array2XML($name, $v);
	        }
        }
    }
    else
    {
		$out = "<$name>";
        if(is_bool($data))
            $out .= $data?'1':'0';
        else
	       	$out .= htmlentities($data);
		$out .= "</$name>\n";
    }
    return $out;
}

function is_assoc($array)
{
    return (bool)count(array_filter(array_keys($array), 'is_string'));
}

function mymkdir($dir)
{
	$tpos = 0;
    while($tpos < strlen($dir))
    {
	    $tpos = strpos($dir, "/", $tpos+1);
	    if($tpos == false)
        	$tpos = strlen($dir);
	    $testdir = substr($dir, 0, $tpos);
        if(!file_exists($testdir))
        {
        	//echo "Attempting to create $testdir<br/>";
        	mkdir($testdir, 0775);
        }
    }
    if((!file_exists($dir))||(!is_dir($dir)))
		return false;
    else
    	return true;
}

function createBasicGlobalQuestion($title, $definition)
{
    $qu = new basicQuestion($title, false, $definition);
    $theQu = new question();
	$theQu->title = $title;
	$theQu->multiuse = true;
    $theQu->definition = $qu;
    $theQu->insert();
    $qlu = new systemQuestionLookup();
    $qlu->qu_id = $theQu->id;
    $qlu->name = $theQu->title;
    $qlu->insert();
}

function createTextinputGlobalQuestion()
{
    $qu = new ttcQuestion1("Text input", false, 0, 0);
    $theQu = new question();
	$theQu->title = "Text input";
	$theQu->multiuse = true;
    $theQu->definition = $qu;
    $theQu->insert();
    $qlu = new systemQuestionLookup();
    $qlu->qu_id = $theQu->id;
    $qlu->name = $theQu->title;
    $qlu->insert();
}

?>
