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
    $allSMs = array();
    foreach($phoneSMs as $p)
        $allSMs[$p->id] = $p;
    foreach($normalSMs as $p)
        $allSMs[$p->id] = $p;
    echo "$user->id $user->name ".sizeof($phoneSMs)." ".sizeof($normalSMs)."<br/>";
    $sns = array();
    $firstsns = array();
    foreach($allSMs as $pp)
    {
        if(in_array($pp->session_id, $sns))
        {
            echo "<span style='color:red;'>{$pp->session_id} ($pp->id)</span>, ";
            merge($firstsns[$pp->session_id], $pp->id);
        }
        else
        {
            echo "<span style='color:green;'>{$pp->session_id} ($pp->id)</span>, ";
            $pp->userID = $user->username;
            $pp->name = $user->name;
            $pp->email = $user->email;
            $pp->user_id = $user->id;
            $pp->mobile = $user->phone;
            $pp->update();
            //echo '<pre>'.print_r($pp,1).'</pre>';
        }
        $sns[] = $pp->session_id;
        $firstsns[$pp->session_id] = $pp->id;
    }
    echo '</p>';
}

function merge($smtostay, $smtogo)
{
    $query = "UPDATE yacrs_message SET user_id='$smtostay' WHERE user_id='$smtogo';";
    $result = dataConnection::runQuery($query);
    $query = "UPDATE yacrs_response SET user_id='$smtostay' WHERE user_id='$smtogo';";
    $result = dataConnection::runQuery($query);
    $query = "DELETE FROM yacrs_sessionMember WHERE id='$smtogo';";
    $result = dataConnection::runQuery($query);
}


function usersWithPhones()
{
    $query = "SELECT * FROM yacrs_userInfo WHERE phone REGEXP '[0-9]+';";
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
