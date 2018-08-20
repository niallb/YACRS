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

function CheckDaySelect($sessionID=false, $includeAll=true, $includeToday=true)
{
    if($sessionID)
    {
        $days = array_keys(getSessionDates($sessionID, $includeToday, $includeAll));
        $defDay = (sizeof($days)) ? $days[sizeof($days)-1] : 0;
    }
    else
    	$defDay = 0;
    if(requestSet('UpdateDay'))
    {
        $_SESSION['showday'] = requestInt('day', $defDay);
    }
    else
    {
        if(!isset($_SESSION['showday']))
        {
            $_SESSION['showday'] = $defDay;
        }
    }
}

function DaySelectForm($sessionID, $includeAll=true, $includeToday=true, $extraFields=array())
{
    $days = getSessionDates($sessionID, $includeToday, $includeAll);
    if(sizeof($days) <= 1)
        return '';
    $out = "<div style='float:right;'><form class='form-inline'><label for='day'>Display day</label> <select name='day' class='form-control'>";
    foreach($days as $day => $count)
    {
        $selected = $_SESSION['showday']==$day ? "selected='selected'":'';
        $out .= "<option value='$day'{$selected}>".strftime("%a %d %b %Y", $day)." ($count Qus)</option>";
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
    $out .= " <input type='submit' class='btn btn-default' name='UpdateDay' value='Update'/>";
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
	        //if(($day>0)&&(!in_array($day, $days)))
	        //   $days[] = $day;
            if($day>0)
            {
                if(isset($days[$day]))
                    $days[$day]++;
                else
                    $days[$day] = 1;
            }
	    }
    }
    if($includeToday)
    {
		$day = intval(time() / (24*3600)) * 24 * 3600;
	    if(!isset($days[$day]))
	       $days[$day] = 0;
    }
    ksort($days);
    if((isset($_SESSION['showday']))&&(!isset($days[$_SESSION['showday']])))
    {
        if(($includeAll)||(sizeof($days)==0))
            $_SESSION['showday'] = 0;
        else
        {
            $days2 = array_keys($days);
            $_SESSION['showday'] = $days2[sizeof($days2)-1];
        }
    }
    return $days;
}

function s($number, $singular="", $plural="s") {
	// Usage: "There ".s($count, "is ", "are ").$count." comment".s($count);
	return ($number==1)?$singular:$plural;
}

function ago($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0 seconds';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}
