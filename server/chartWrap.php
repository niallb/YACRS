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
            $chart = 'wordwall.php';
	    echo "<img src='{$chart}?qiID={$qi->id}' style='width:100%; height:100%;'/><br/>";
     }
     else
     {
     	echo 'Question not found';
     }

?>
</body>
</html>
