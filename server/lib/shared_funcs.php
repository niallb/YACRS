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

function getRosterStudentIDs($sessionID, $courseCode)
{
    global $CFG;
    $idlist = array();
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
                $idlist[] = $userID;
            }
        }
    }
    return $idlist;
}

function checkPermission($uinfo, $thisSession)
{
   if(($uinfo==false)||($thisSession == false))
       return false;
   if((!$thisSession->isStaffInSession($uinfo['uname']))&&(!$uinfo['isAdmin']))
       return false;
   return true;
}

function CheckDaySelect()
{
    if(requestSet('UpdateDay'))
    {
        $_SESSION['showday'] = requestInt('day', 0);
    }
    else
    {
        if(!isset($_SESSION['showday']))
        $_SESSION['showday'] = 0;
    }
}

function DaySelectForm($sessionID, $includeAll=true, $includeToday=true, $extraFields=array())
{
    $days = getSessionDates($sessionID, $includeToday, $includeAll);
    if(sizeof($days) <= 1)
        return '';
    $out = "<div style='float:right;'><form>Display day: <select name='day'>";
    foreach($days as $day)
    {
        $selected = $_SESSION['showday']==$day ? "selected='selected'":'';
        $out .= "<option value='$day'{$selected}>".strftime("%a %d %b %Y", $day).'</option>';
    }
    foreach($extraFields as $k=>$v)
    {
        $out .= "<input type='hidden' name='$k' value='$v'/>";
    }
    if($includeAll)
    {
	    $selected = $_SESSION['showday']==0 ? "selected='selected'":'';
	    $out .= "<option value='0'{$selected}>All days</option>";
    }
    $out .= "</select>";
    $out .= "<input type='hidden' name='sessionID' value='$sessionID'/>";
    $out .= "<input type='submit' name='UpdateDay' value='Update'/>";
    $out .= "</form></div>";
    return $out;
}

function getSessionDates($sessionID, $includeAll=true, $includeToday=true)
{
    $qis = questionInstance::retrieve_questionInstance_matching('inSession_id', $sessionID);
    $days = array();
    if($qis !== false)
    {
	    foreach($qis as $i)
	    {
	    	$day = intval($i->endtime / (24*3600)) * 24 * 3600;
	        if(($day>0)&&(!in_array($day, $days)))
	           $days[] = $day;
	    }
    }
    if($includeToday)
    {
		$day = intval(time() / (24*3600)) * 24 * 3600;
	    if(!in_array($day, $days))
	       $days[] = $day;
    }
    asort($days);
    if((isset($_SESSION['showday']))&&(!in_array($_SESSION['showday'], $days)))
    {
        if(($includeAll)||(sizeof($days)==0))
            $_SESSION['showday'] = 0;
        else
            $_SESSION['showday'] = $days[sizeof($days)-1];
    }
    return $days;
}

