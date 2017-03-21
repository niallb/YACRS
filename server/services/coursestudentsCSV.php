<?php
include("../../../roster_config.php");
include("dataaccess.php");
$iv_ds = ldap_connect($CFG['ldaphost']);
$iv_r = ldap_bind( $iv_ds );
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="'.$_REQUEST['actualCourseKey'].'.csv"');
$securitysecret = $CFG['rostersecret'];

if(isset($_REQUEST['actualCourseKey']))
{
    if(!isset($_REQUEST['secret'])||(sha1($_REQUEST['actualCourseKey'].$securitysecret) !== $_REQUEST['secret']))
        exit('ERROR: Security checksum failed');
	$slist = getStudentList($_REQUEST['actualCourseKey']);
    $courseDetails = getCourseDetails($_REQUEST['actualCourseKey']);
    $courseName = $courseDetails['classTitle'];
    $courseYear = 'Year '.substr($courseDetails['catalogNBR'],0,1);
    echo "Forename,Surname,Email Address,Telephone Number,NETWARE ACCOUNT NAME,Course Code,Course,Academic Level,Person ID\r\n";
    if($slist)
    {
		foreach($slist as $s)
		{
		    $details = getStudentDetails($s['UserName']);
		    if($details)
		    {
	            echo "{$details['forename']},{$details['surname']},{$details['emailAddress']},,{$details['username']},{$_REQUEST['actualCourseKey']},{$courseName},{$courseYear},{$details['username']}\r\n";
		    }
		}
    }
}
else
{
	echo "ERROR: no course code provided.";
}

function getCourseDetails($coursecode)
{
	$sql="SELECT * FROM CourseCodes WHERE CourseCat='".dataConnection::safe($coursecode)."';";
	$result = dataConnection::runQuery($sql);
	if(sizeof($result)>0)
	{
	     //$crsnms =
         return array('classNBR'=>$result[0]['ID'], 'classTitle'=>$result[0]['Crse_name'], 'subject'=>$result[0]['Crse_cd_Subject'], 'catalogNBR'=>$result[0]['Crse_cd_nbr'], 'actualCourseKey'=>$coursecode) ;
	}
    else
        return array('classNBR'=>'', 'classTitle'=>'', 'subject'=>'', 'catalogNBR'=>'', 'actualCourseKey'=>$coursecode) ;;
}

function getStudentList($coursecode)
{
    if(substr($coursecode, 0, 8)=='PROGROCK')
    {
        $count = intval(substr($coursecode, 9));
        $out = array();
        for($n=1; $n<=$count; $n++)
        {
            $out[]=array('UserName'=>'ltutest'.$n);
        }
        return $out;
    }
    $sql="SELECT DISTINCT UserName FROM EnrolmentSLP WHERE courses='".dataConnection::safe($coursecode)."';";
    $result = dataConnection::runQuery($sql);
    if(sizeof($result)!=0)
        return $result;
    else
        return false;
}

function getStudentDetails($username)
{
	// setup ldap
    global 	$iv_ds,$iv_r;

	$sr = ldap_search( $iv_ds, 'o=Gla', "cn=$username");
	$count = ldap_count_entries( $iv_ds, $sr );
	if($count>0)
	{
		$entry = ldap_first_entry($iv_ds, $sr);
	    $attrs = ldap_get_attributes($iv_ds, $entry);
        $user = array(
             'partyNumber'=>isset($attrs['workforceID'][0])?$attrs['workforceID'][0]:0,
             'username'=>$attrs['uid'][0],
             'forename'=>$attrs['givenName'][0],
             'surname'=>$attrs['sn'][0],
             'mobile'=>'',
             'photoURL'=>'',
             'emailAddress'=>isset($attrs['mail'][0])?$attrs['mail'][0]:''
        );
	    ldap_free_result( $sr );
        return $user;
	}
	else
		return false;
}

?>

