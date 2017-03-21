<?php
# PHPlot Demo
# 2009-12-01 ljb
# For more information see http://sourceforge.net/projects/phplot/

# Load the PHPlot class library:
require_once 'phplot/phplot.php';
require_once('config.php');
require_once('lib/database.php');

$qid=intval($_GET['qid']);
$data_points = array();

//Count for Main Answers 1
$query = "SELECT COUNT(SUBSTR(value,2,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=1";
foreach($result as $r)
    $a1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=1 AND SUBSTR(value,4,1)=1";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=1 AND SUBSTR(value,4,1)=2";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b2=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=1 AND SUBSTR(value,4,1)=3";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b3=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=1 AND SUBSTR(value,4,1)=4";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b4=$r['ans'];
$point = array(1,$b1,$b2,$b3,$b4);
array_push($data_points, $point);
exit('<pre>'.print_r($data_points,true).'</pre>');

//Count for Main Answers 2
$query = "SELECT COUNT(SUBSTR(value,2,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=2";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $a1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=2 AND SUBSTR(value,4,1)=1";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=2 AND SUBSTR(value,4,1)=2";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b2=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=2 AND SUBSTR(value,4,1)=3";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b3=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=2 AND SUBSTR(value,4,1)=4";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b4=$r['ans'];
$point = array(1,$b1,$b2,$b3,$b4);
array_push($data_points, $point);

//Count for Main Answers 3
$query = "SELECT COUNT(SUBSTR(value,2,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=3";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $a1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=3 AND SUBSTR(value,4,1)=1";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=3 AND SUBSTR(value,4,1)=2";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b2=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=3 AND SUBSTR(value,4,1)=3";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b3=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=3 AND SUBSTR(value,4,1)=4";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b4=$r['ans'];
$point = array(1,$b1,$b2,$b3,$b4);
array_push($data_points, $point);

//Count for Main Answers 4
$query = "SELECT COUNT(SUBSTR(value,2,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=4";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $a1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=4 AND SUBSTR(value,4,1)=1";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b1=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=4 AND SUBSTR(value,4,1)=2";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b2=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=4 AND SUBSTR(value,4,1)=3";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b3=$r['ans'];
$query = "SELECT COUNT(SUBSTR(value,4,1)) AS ans FROM yacrs_response WHERE question_id=$qid AND SUBSTR(value,2,1)=4 AND SUBSTR(value,4,1)=4";
$result = dataConnection::runQuery($query);
foreach($result as $r)
    $b4=$r['ans'];
$point = array(1,$b1,$b2,$b3,$b4);
array_push($data_points, $point);


//foreach($data_points as $v)
//    echo $v[6];

//exit;

/*$data = array(
  array('Jan', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4),
  array('Mar', 50, 6, 10, 4), array('Apr', 40, 3, 20, 4),
  array('May', 75, 2, 10, 5), array('Jun', 45, 6, 15, 5),
  array('Jul', 40, 5, 20, 6), array('Aug', 35, 6, 12, 6),
  array('Sep', 50, 5, 10, 7), array('Oct', 45, 6, 15, 8),
  array('Nov', 35, 6, 20, 9), array('Dec', 40, 7, 12, 9),
);*/

$data=$data_points;



$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

$plot->SetTitle('Answer Analysis');
$plot->SetYTitle('Count');

# No shading:
$plot->SetShading(5);

$plot->SetLegend(array('Very Confident', 'Confident', 'Partially Confident', 'All Guess'));
# Make legend lines go bottom to top, like the bar segments (PHPlot > 5.4.0)
$plot->SetLegendReverse(True);

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

# Turn on Y Data Labels: Both total and segment labels:
$plot->SetYDataLabelPos('plotstack');

$plot->DrawGraph();

function checkB()
{
    if($b1>0)
        return $b1;
}
?>
