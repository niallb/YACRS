<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/forms.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');

$uinfo = checkLoggedInUser();

$sessionID = requestInt('sessionID');

$thisSession = $sessionID? session::retrieve_session($sessionID):false;
//if(($uinfo==false)||($thisSession == false)||(!$thisSession->isStaffInSession($uinfo['uname'])))
if(!checkPermission($uinfo, $thisSession))
{
    header("Location: index.php");
    exit();
}


header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"responses_{$sessionID}.csv\";" );
header("Content-Transfer-Encoding: binary");   //*/ echo '<pre>';

$members = sessionMember::retrieve_sessionMember_matching('session_id', $thisSession->id, 0, -1, 'userID asc');
$questionInsts = array();
$questions = array();
if(strlen(trim($thisSession->questions)))
{
    $qiIDs = explode(',',$thisSession->questions);
    array_splice($qiIDs, 0, array_search(requestInt('from'), $qiIDs));
    array_splice($qiIDs, array_search(requestInt('to'), $qiIDs)+1);
    foreach($qiIDs as $qi)
        $questionInsts[] = questionInstance::retrieve_questionInstance($qi);
    for($n=0; $n < sizeof($questionInsts); $n++)
        $questions[$questionInsts[$n]->id] = question::retrieve_question($questionInsts[$n]->theQuestion_id);
}
$quRespCount = array();
$quScoreTot = array();

echo "Student ID,Name";
$catCount = array();
foreach($questionInsts as $q)
{
    echo ','.preg_replace('/([\\\\"])/','\\\\\\1', $q->title);
    $quRespCount[$q->id] = 0;
    $quScoreTot[$q->id] = 0;
    if(isset($q->extras['category']))
    {
        if(!isset($catCount[$q->extras['category']]))
            $catCount[$q->extras['category']] = 1;
        else
            $catCount[$q->extras['category']]++;
    }
}

// headings for scores
echo ',,'; // a gap
echo ',BLANKS';
foreach($questionInsts as $q)
    echo ','.preg_replace('/([\\\\"])/','\\\\\\1', $q->title);
// headings for category scores
if(isset($thisSession->extras['categories']))
{
	echo ',,'; // a gap
    $catScoreTmpl = array();
	foreach($thisSession->extras['categories'] as $cat)
    {
        if(!isset($catCount[$cat]))
            $catCount[$cat] = 0;

	    echo ','.preg_replace('/([\\\\"])/','\\\\\\1', "$cat ({$catCount[$cat]})");
        $catScoreTmpl[$cat]=0;
    }
}
else
{
    $catScoreTmpl = array();
}
echo "\r\n";

// Correct responses
echo',';
foreach($questionInsts as $q)
{
    echo ','.$questions[$q->id]->definition->getCorrectStr($q);
}
echo "\r\n";

foreach($members as $m)
{
    $respCount = 0;
    $catScore=$catScoreTmpl;
    echo preg_replace('/([\\\\"])/e', '\\\\\\1', $m->userID).','.preg_replace('/([\\\\"])/e', '\\\\\\1', $m->name);
    $resp = array();
	foreach($questionInsts as $q)
    {
        $resp[$q->id] = response::retrieve($m->id, $q->id);
        echo ',';
        if($resp[$q->id])
        {
            $respCount++;
    	    echo preg_replace('/([\\\\"])/','\\\\\\1', str_replace(',', ' ',$resp[$q->id]->value));
        }
    }
    echo ',,'; // a gap
    echo ','.(sizeof($questionInsts)-$respCount); // blanks
	foreach($questionInsts as $q)
    {
        echo ',';
        $sc = 0;
        if($resp[$q->id])
        {
            $sc = $questions[$q->id]->definition->score($q, $resp[$q->id]);
            if((isset($q->extras['category']))&&(isset($catScore[$q->extras['category']])))
                $catScore[$q->extras['category']] += $sc;
            $quRespCount[$q->id]++;
            $quScoreTot[$q->id]+=$sc;
        }
    	echo $sc;
    }
	if(isset($thisSession->extras['categories']))
	{
		echo ',,'; // a gap
		foreach($thisSession->extras['categories'] as $cat)
	    {
		    echo ','.$catScore[$cat];
	    }
	}
    echo "\r\n";
}

    echo "\r\n";
    echo ',';
	foreach($questionInsts as $q)
    {
        echo ',';
    }
    echo ',,,';
	foreach($questionInsts as $q)
    {
        echo ','.$quScoreTot[$q->id];
    }
    echo "\r\n";
    echo ',';
	foreach($questionInsts as $q)
    {
        echo ',';
    }
    echo ',,,';
	foreach($questionInsts as $q)
    {
        echo ','.$quRespCount[$q->id];
    }
    echo "\r\n\r\n";
    echo ',';
	foreach($questionInsts as $q)
    {
        echo ',';
    }
    echo ',,,';
	foreach($questionInsts as $q)
    {
        if($quRespCount[$q->id] > 0)
	        echo ','.sprintf("%.0f", 100*$quScoreTot[$q->id]/$quRespCount[$q->id]).'%';
        else
            echo ',';
    }

/*foreach($messages as $m)
{
    if($m->user_id > 0)
    {
		$user = sessionMember::retrieve_sessionMember($m->user_id);
    	echo date("c", $m->posted).','.$user->userID.',"'.preg_replace('/([\\\\"])/e', '\\\\\\1', $user->name).'","'.preg_replace('/([\\\\"])/', '\\\\\\1', $m->message).'"'."\r\n";
    }
    else
    {
    	echo date("c", $m->posted).', System,,"'.preg_replace('/([\\\\"])/','\\\\\\1', $m->message).'"'."\r\n";
    }
}    */

?>
