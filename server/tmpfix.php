<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');

removeNullSessions();
$users = usersWithPhones();

foreach($users as $user)
{
	$phoneSMs = sessionMember::retrieve_sessionMember_matching('mobile', $user->phone);
    if($phoneSMs===false) $phoneSMs = array();
	$normalSMs = sessionMember::retrieve_sessionMember_matching('userID', $user->username);
    if($normalSMs===false) $normalSMs = array();
    echo "$user->name ".sizeof($phoneSMs)." ".sizeof($normalSMs)."<br/>";
    $sns = array();
    foreach($phoneSMs as $pp)
    {
        if(in_array($pp->session_id, $sns))
            echo "<span style='color:red;'>{$pp->session_id}</span>, ";
        else
        {
            echo "<span style='color:green;'>{$pp->session_id}</span>, ";
            $pp->userID = $user->username;
            $pp->name = $user->name;
            $pp->email = $user->email;
            $pp->user_id = $user->id;
            //echo '<pre>'.print_r($pp,1).'</pre>';
        }
        $sns[] = $pp->session_id;
    }
	foreach($normalSMs as $pp)
    {
        if(in_array($pp->session_id, $sns))
            echo "<span style='color:red;'>{$pp->session_id}</span>, ";
        else
        {
            echo "<span style='color:blue;'>{$pp->session_id}</span>, ";
            $pp->mobile = $user->phone;
            //echo '<pre>'.print_r($pp,1).'</pre>';
        }
        $sns[] = $pp->session_id;
    }
    echo '</p>';
}


function usersWithPhones()
{
    $query = "SELECT * FROM yacrs_userinfo WHERE phone REGEXP '[0-9]+';";
    $result = dataConnection::runQuery($query);
    $output = array();
    if(sizeof($result)!=0)
    {
        foreach($result as $r)
            $output[] = new userInfo($r);
    }
    return $output;
}

function removeNullSessions()
{
    $query = "DELETE FROM yacrs_sessionMember WHERE session_id IS NULL;";
    $result = dataConnection::runQuery($query);
    return $result;
}



?>
