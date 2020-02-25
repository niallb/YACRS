<!DOCTYPE html>
<html><head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title></title>
        <script src="scripts/d3.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="scripts/d3.layout.cloud.js" type="text/javascript" charset="utf-8"></script>
        <script src="scripts/responses.js" type="text/javascript" charset="utf-8"></script>
</head>

<body>

<?php
include_once('lib/wordcloud.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');

	$ViewQI = requestInt('qiID');

    if($ViewQI > 0)
    {
	   // $qiIDPos = array_flip($qiIDs);
	    //$pos = $qiIDPos[$ViewQI];
	    $qi = questionInstance::retrieve_questionInstance($ViewQI);
	    $qu = question::retrieve_question($qi->theQuestion_id);
	    if(is_a($qu->definition, 'basicQuestion'))
            $chart = 'chart.php';
	    elseif(is_a($qu->definition, 'confidenceQuestion'))
            $chart = 'chart.php';
	    else
            $chart = false;
        if($chart)
	        echo "<img src='{$chart}?qiID={$qi->id}' style='width:100%; height:100%;'/><br/>";
        else
        {
            //echo "<h1 style='border: 4px solid #ffdc36;background-color: #ffb948; border-radius: 7px;'>CSS Test</h1>";
            $responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
            echo wordcloud($_REQUEST['qiID'], $responses, '100%', '95vh');
        }
     }
     else
     {
     	echo 'Question not found';
     }

?>
</body>
</html>
