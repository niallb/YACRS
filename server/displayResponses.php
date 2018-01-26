<html>

<head>
  <title></title>
</head>

<body>

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
require_once('lib/questionTypes.php');

$uinfo = checkLoggedInUser();

if((isset($_SERVER['HTTPS']))&&($_SERVER['HTTPS']=='on'))
{
	$serverURL = 'https://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 443)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
else
{
	$serverURL = 'http://'.$_SERVER['HTTP_HOST'];
    if($_SERVER['SERVER_PORT'] != 80)
        $serverURL .= ':'.$_SERVER['SERVER_PORT'];
}
$serverURL .= $_SERVER['SCRIPT_NAME'];
$serverURL = substr($serverURL, 0, strrpos($serverURL, '/')+1);

if($uinfo==false)
{
    header("Location: index.php");
}
else
{
	$thisSession = requestSet('sessionID')? session::retrieve_session(requestInt('sessionID')):false;
	if(!checkPermission($uinfo, $thisSession))
	{
	    header("Location: index.php");
	    exit();
	}
    $qiIDs = explode(',',$thisSession->questions);
	$ViewQI = requestInt('qiID');
    if(($ViewQI==0)&&(sizeof($qiIDs)))
        $ViewQI = $qiIDs[sizeof($qiIDs)-1];
    //echo $ViewQI .'<pre>'.print_r($qiIDs,1).'</pre>';
    if($ViewQI > 0)
    {
	    $qiIDPos = array_flip($qiIDs);
	    $pos = $qiIDPos[$ViewQI];
	    $qi = questionInstance::retrieve_questionInstance($ViewQI);
	    $qu = question::retrieve_question($qi->theQuestion_id);
	    echo "<h2>{$qi->title} (". ($pos+1) . '/' .sizeof($qiIDs).")</h2>";
	    if(is_a($qu->definition, 'basicQuestion'))
            $chart = 'chart.php';
	    elseif(is_a($qu->definition, 'confidenceQuestion'))
            $chart = 'graph2.php';
	    elseif(is_a($qu->definition, 'ttcQuestion1'))
            $chart = 'wordwall.php';
	    echo "<img src='{$chart}?qiID={$qi->id}' style='width:90%; height:80%;'/><br/>";
	    $PrevNextLinks = '<div class="col-xs-4 question-prev">';
	    if($pos > 0)
	    {
	        $PrevNextLinks .= "<a href='displayResponses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos-1]}'>&lsaquo; Previous Question</span></a> ";
	    }
	    $PrevNextLinks .= " | <a href='displayResponses.php?sessionID={$thisSession->id}&qiID={$qi->id}'>Refresh display</a> | ";
	    if($pos < sizeof($qiIDs)-1)
	    {
	        $PrevNextLinks .= "<a href='displayResponses.php?sessionID={$thisSession->id}&qiID={$qiIDs[$pos+1]}'>Next Question</span> &rsaquo;</a> ";
	    }
	    $PrevNextLinks .= '</div>';
	    if(!empty($PrevNextLinks))
	    	echo '<div class="question-nav question-nav-top">'.$PrevNextLinks.'</div>';
     }
     else
     {
     	echo 'No questions found';
     }
}

?>

</body>

</html>
