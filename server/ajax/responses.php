<?php
require_once('../config.php');
require_once('../lib/database.php');
require_once('../lib/forms.php');
require_once('../lib/questionTypes.php');
require_once('../lib/vote_lib.php');

$uinfo = CheckValidLoginCookie($_REQUEST['loginCookie']);

$responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
$searchword = $_REQUEST['containing'];

$n = 0;
$colours = array('#e6f2ff', '#fff2c6');
$output = '<div><button onclick="hideResponses();">Close</button></div>';
foreach($responses as $r)
{
    if(contains($r->value, $searchword))
    {
        $output .= "<div style='background-color: {$colours[$n]};'>{$r->value}</div>";
        $n = ($n+1) % 2;
    }
}

echo json_encode(array('responses'=>$output));

function contains($haystack, $needle)
{
    $needle = ' '.strtolower($needle).' ';
    $haystack = ' '.preg_replace('/\s+/', ' ', strtolower($haystack)).' ';
    if(strpos($haystack, $needle) !== false)
        return true;
    else
        return false;
}