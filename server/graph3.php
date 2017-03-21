<?php
# PHPlot Demo
# 2009-12-01 ljb
# For more information see http://sourceforge.net/projects/phplot/

# Load the PHPlot class library:
require_once 'phplot/phplot.php';
require_once('config.php');
require_once('lib/database.php');

$qid=$_GET['qid'];
$query = "SELECT question_id, SUM(SUBSTR(value,2,10)) AS val1, SUM(value_cq) AS val2 FROM yacrs_response WHERE question_id=$qid GROUP BY question_id, value, value_cq";

$result = dataConnection::runQuery($query);

$data_points = array();
$i=1;
foreach($result as $r)
{
    $qid=$r['question_id'];
    $val1=$r['val1'];
    $val2=$r['val2'];
    $point = array($i, $val1, $val2);
    array_push($data_points, $point);
    $i++;
}
$data=$data_points;

//  array('Jan', 40, 51, 10, 3), array('Feb', 90, 8, 15, 4),


$plot = new PHPlot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

$plot->SetTitle('Answer Analysis');
$plot->SetYTitle('Count');

# No shading:
$plot->SetShading(5);

$plot->SetLegend(array('Main Questions', 'Confidence Building'));
# Make legend lines go bottom to top, like the bar segments (PHPlot > 5.4.0)
$plot->SetLegendReverse(True);

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

# Turn on Y Data Labels: Both total and segment labels:
$plot->SetYDataLabelPos('plotstack');

$plot->DrawGraph();

?>