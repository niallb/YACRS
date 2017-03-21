<?php
# PHPlot Demo
# 2009-12-01 ljb
# For more information see http://sourceforge.net/projects/phplot/

# Load the PHPlot class library:
require_once 'phplot/phplot.php';
require_once('config.php');
require_once('lib/database.php');
//$qid=$_GET['qiID'];

require_once('lib/shared_funcs.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');
$qiID = requestInt('qiID');
$qi = questionInstance::retrieve_questionInstance($qiID);
$qu = question::retrieve_question($qi->theQuestion_id);

  
	$labels = $qu->definition->getGraphLabels();
	$count = array_fill_keys(array_keys($labels), array(1=>0, 2=>0, 3=>0, 4=>0));
	$responses = response::retrieve_response_matching('question_id', $qiID);

	if($responses)
	{
    $r1_1 = $r1_2 = $r1_3 = $r1_4 = 0;
    $r2_1 = $r2_2 = $r2_3 = $r2_4 = 0;
    $r3_1 = $r3_2 = $r3_3 = $r3_4 = 0;
    $r4_1 = $r4_2 = $r4_3 = $r4_4 = 0;
    $r5_1 = $r5_2 = $r5_3 = $r5_4 = 0;
   	foreach($responses as $r)
		{
			if(strlen($r->value))
			{
          list($val, $confVal) = explode('::', $r->value);
				  
          if($val == 'R1')
          {
            switch ($confVal) {
              case '1':
                $r1_1++;
                break;
              case '2':
                $r1_2++;
                break;
              case '3':
                $r1_3++;
                break;
              case '4':
                $r1_4++;
                break;
              default:
                break;
            }
          }
          if($val == 'R2')
          {
            switch ($confVal) {
              case '1':
                $r2_1++;
                break;
              case '2':
                $r2_2++;
                break;
              case '3':
                $r2_3++;
                break;
              case '4':
                $r2_4++;
                break;
              default:
                break;
            }
          }
          if($val == 'R3')
          {
            switch ($confVal) {
              case '1':
                $r3_1++;
                break;
              case '2':
                $r3_2++;
                break;
              case '3':
                $r3_3++;
                break;
              case '4':
                $r3_4++;
                break;
              default:
                break;
            }
          }
          if($val == 'R4')
          {
            switch ($confVal) {
              case '1':
                $r4_1++;
                break;
              case '2':
                $r4_2++;
                break;
              case '3':
                $r4_3++;
                break;
              case '4':
                $r4_4++;
                break;
              default:
                break;
            }
          }
          if($val == 'R5')
          {
            switch ($confVal) {
              case '1':
                $r5_1++;
                break;
              case '2':
                $r5_2++;
                break;
              case '3':
                $r5_3++;
                break;
              case '4':
                $r5_4++;
                break;
              default:
                break;
            }
          }
			}
		}
	}

  $chartData = array(
    array('A', $r1_1, $r1_2, $r1_3, $r1_4),
    array('B', $r2_1, $r2_2, $r2_3, $r2_4),
    array('C', $r3_1, $r3_2, $r3_3, $r3_4),
    array('D', $r4_1, $r4_2, $r4_3, $r4_4),
    );
 
  if($r5_1 != 0 || $r5_2 != 0 || $r5_3 != 0 || $r5_4 != 0)
  {
    $r5_array_data = array('E', $r5_1, $r5_2, $r5_3, $r5_4);
    array_push($chartData, $r5_array_data);
  }
/*$data = array(
  array('Jan', 40, 5, 10, 3), array('Feb', 90, 8, 15, 4),
  array('Mar', 50, 6, 10, 4), array('Apr', 40, 3, 20, 4),
  array('May', 75, 2, 10, 5), array('Jun', 45, 6, 15, 5),
  array('Jul', 40, 5, 20, 6), array('Aug', 35, 6, 12, 6),
  array('Sep', 50, 5, 10, 7), array('Oct', 45, 6, 15, 8),
  array('Nov', 35, 6, 20, 9), array('Dec', 40, 7, 12, 9),
);*/

$data=$chartData;

$plot = new PHPlot(500, 300);
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
$plot->SetLegendPosition(0, 0, 'image', 1, 0, -140, 5);
$plot->SetMarginsPixels(null, 140);

$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');


# Turn on Y Data Labels: Both total and segment labels:
$plot->SetYDataLabelPos('plotstack');

//FORMATING GRAPH

$plot->SetDataColors(array('#142952', '#2952a3', '#85a3e0','#d6e0f5'));
//$plot->SetFontTTF('title', '/libchart/fonts/DejaVuSansCondensed.TTF',14);
//$plot->SetFontTTF('legend', '/libchart/fonts/DejaVuSansCondensed.TTF',10);
//$plot->SetFontTTF('x_label', '/libchart/fonts/DejaVuSansCondensed.TTF',14);
//$plot->SetFontTTF('y_label', '/libchart/fonts/DejaVuSansCondensed.TTF',10);
$plot->TuneYAutoRange(0, 'R', 0);
$plot->SetYTickIncrement(1);
//$plot->SetYLabelType('data', 0, '', '%');
$plot->DrawGraph();

function checkB()
{
    if($b1>0)
        return $b1;
}
?>
