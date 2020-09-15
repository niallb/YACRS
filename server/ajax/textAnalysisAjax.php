<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');
require_once('../lib/database.php');
require_once('../lib/shared_funcs.php');
require_once('../lib/textAnalysisLib.php');

$uinfo = checkLoggedInUser();
session_start(array('name'=>'YACRSSESSION'));
if(isset($_REQUEST['sessionID']))
    $_SESSION['sessionID'] = $_REQUEST['sessionID'];
if(isset($_REQUEST['qiID']))
    $_SESSION['textAnalysis_qiID'] = $_REQUEST['qiID'];
$thisSession = isset($_SESSION['sessionID'])? session::retrieve_session($_SESSION['sessionID']):false;
if(!checkPermission($uinfo, $thisSession))
{
    exit(json_encode(array('alert'=>'You do not have permission for this.')));
}

if(isset($_SESSION['selectedPhrases']))
    $selectedPhrases = $_SESSION['selectedPhrases'];
else
    $selectedPhrases = array();

$responses = response::retrieve_response_matching('question_id', $_SESSION['textAnalysis_qiID']);

if(!isset($_REQUEST['containing']))
    exit(json_encode(array('alert'=>'textAnalysisAjax.php called without required \'containing\' parameter.')));

$output = array();
$bkcolour[false] = '#dff';
$bkcolour[true] = '#ffd';

if(!isset($_REQUEST['refine']))
{
    $responses2 = array();
    foreach($responses as $r)
    {
        $value = ' '.trim(preg_replace('/[^\w]+/', ' ', strtolower($r->value))).' ';           
       // if(strpos($value, $_REQUEST['containing']) !== false)
        if(containsStr($value, $_REQUEST['containing']))
        {
                $responses2[] = $r;
        }
    }
    $wc = wordcloud_ta($responses2, $selectedPhrases, '100%', '550px', 'c3', true, 'refine='.urlencode($_REQUEST['containing']));
    $output = array("cloud2"=>"<h2>Cloud of responses containing <span style='background-color: yellow;'>{$_REQUEST['containing']}</span></h2>".$wc->html, 'eval'=>$wc->eval, 'scrollto'=>'cloud2');

    $out = "<h3>Responses containing '{$_REQUEST['containing']}':</h3>";

    $regex = '/(?<!\w)'.str_replace(' ', '\s+', $_REQUEST['containing']).'(?!\w)/i';
    $bkchoice = false;
    foreach($responses as $r)
    {
        if(containsStr($r->value, $_REQUEST['containing']))
        {
            $out .= "<div style='background-color: {$bkcolour[$bkchoice]};'>".preg_replace($regex, '<span style="background-color: yellow;">\0</span>', htmlentities($r->value))."</div>";
            $bkchoice = !$bkchoice;
        }
    }
    $output["report1"] = $out;
}
else
{
    $responses2 = array();
    foreach($responses as $r)
    {
        $value = ' '.trim(preg_replace('/[^\w]+/', ' ', strtolower($r->value))).' ';           
        if(strpos($value, $_REQUEST['refine']) !== false)
        {
            $responses2[] = $r;
        }
    }
    $out = "<h3>Responses containing '<span style='background-color: yellow;'>{$_REQUEST['refine']}</span>' and '<span style='background-color: #afa;'>{$_REQUEST['containing']}</span>':</h3>";

    $regex1 = '/(?<!\w)'.str_replace(' ', '\s+', $_REQUEST['refine']).'(?!\w)/i';
    $regex2 = '/(?<!\w)'.str_replace(' ', '\s+', $_REQUEST['containing']).'(?!\w)/i';
    $bkchoice = false;
    foreach($responses2 as $r)
    {
        if(containsStr($r->value, $_REQUEST['containing']))
        {
            $tmp = preg_replace($regex1, '<span style="background-color: yellow;">\0</span>', htmlentities($r->value));
            $out .= "<div style='background-color: {$bkcolour[$bkchoice]};'>".preg_replace($regex2, '<span style="background-color: #afa;">\0</span>', $tmp)."</div>";
            $bkchoice = !$bkchoice;
    }
    }
    $output["report1"] = $out;
}

echo json_encode($output);



