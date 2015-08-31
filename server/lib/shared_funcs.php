<?php

function enrolStudents($sessionID, $courseCode)
{
    global $CFG;
    if(isset($CFG['rosterservice']))
    {
		$rosterService = $CFG['rosterservice'];
		if(substr($rosterService,0,4)!=='http') $rosterService = 'http://'.$_SERVER['HTTP_HOST'].substr($_SERVER['SCRIPT_NAME'],0,strrpos($_SERVER['SCRIPT_NAME'],'/')+1).$rosterService;
        if(isset($CFG['rostersecret']))
            $secret = sha1($courseCode.$CFG['rostersecret']);
        else
        	$secret = '';
		$students = file($rosterService.'?actualCourseKey='.$courseCode.'&secret='.$secret);
        //echo "Srudents:<pre>".print_r($students,1).'</pre>';
        for($n=1; $n<sizeof($students); $n++)
        {
        	$s = trim($students[$n]);
            if(strlen($s))
            {
                list($Forename,$Surname,$Email, $Telephone,$userID,$CourseCode,$Course,$AcademicLevel,$PersonID)=explode(',',$s);
                $smemb = sessionMember::retrieve($userID,$sessionID);
		        if($smemb == false)
		        {
		        	$smemb = new sessionMember();
		            $smemb->session_id = $sessionID;
					$smemb->userID = $userID;
					$smemb->name = $Forename.' '.$Surname;
					$smemb->email = $Email;
					$smemb->joined = 0;
					$smemb->lastresponse = 0;
					$smemb->insert();
		        }
            }
        }
    }
}

function checkPermission($uinfo, $thisSession)
{
   if(($uinfo==false)||($thisSession == false))
       return false;
   if((!$thisSession->isStaffInSession($uinfo['uname']))&&(!$uinfo['isAdmin']))
       return false;
   return true;
}


