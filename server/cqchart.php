<?php
require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
require_once('lib/forms.php');
require_once('lib/questionTypes.php');

define('IMGWIDTH', 300);
define('IMGHEIGHT', 220);

$qiID = requestInt('qiID');
$qi = questionInstance::retrieve_questionInstance($qiID);
$qu = question::retrieve_question($qi->theQuestion_id);
if($qu !== false)
{
	$labels = $qu->definition->getGraphLabels();
	$count = array_fill_keys(array_keys($labels), array(1=>0, 2=>0, 3=>0, 4=>0));
	$responses = response::retrieve_response_matching('question_id', $qiID);
	if($responses)
	{
   		foreach($responses as $r)
		{
			if(strlen($r->value))
			{
                list($val, $confVal) = explode('::', $r->value);
				$votes = explode(',',$val);
				foreach($votes as $v)
				{
					$count[$v][$confVal]++;
				}
			}
		}
	}
    $chartData = array();
    $chartShowAsCorrect = array();
    $correctData = explode('; ', $qu->definition->getCorrectStr($qi));
	foreach($count as $label=>$value)
	{
        $chartData[$labels[$label]] = $value;
        if(in_array($label, $correctData))
            $chartShowAsCorrect[$labels[$label]] = true;
        else
            $chartShowAsCorrect[$labels[$label]] = false;
    }
    drawChart($chartData, $chartShowAsCorrect);
}


function drawChart($data, $showAsCorrect=false)
{
    $maxval = 0;
    foreach($data as $d)
    {
        $tot = $d[1]+$d[2]+$d[3]+$d[4];
        $maxval = $maxval>$tot?$maxval:$tot;
    }
    $colCount = sizeof($data);
    $colWidth = intval(IMGWIDTH / ($colCount+2));
    $baseLine = IMGHEIGHT - (2 * getTextHeight("Ay", 5));
    $hightMultiplier = (IMGHEIGHT - (4 * getTextHeight("Ay", 5))) / $maxval;


	$graph = new MyImage(IMGWIDTH, IMGHEIGHT);

	$graph->line($colWidth, $baseLine, $colWidth * ($colCount+1), $baseLine);
	$graph->line($colWidth, $baseLine, $colWidth, $baseLine - ($hightMultiplier * $maxval));
	$graph->line($colWidth-5, $baseLine - ($hightMultiplier * $maxval), $colWidth, $baseLine - ($hightMultiplier * $maxval));
    $lxpos = $colWidth - getTextWidth($maxval, 5) - 10;
    $lypos =  $baseLine - ($hightMultiplier * $maxval) - (getTextHeight($maxval, 5)/2);
	$graph->string(5, $lxpos, $lypos, $maxval);
    $colPos = 0;
    foreach($data as $t=>$d)
    {
        $tot = $d[1]+$d[2]+$d[3]+$d[4];
        $colPos += $colWidth;
        $colCentre = intval($colPos + $colWidth/2);
	    $lxpos = $colCentre - intval(getTextWidth($t, 5)/2);
	    $lypos =  $baseLine + intval(getTextHeight("Ay", 5)/4);
		$graph->string(5, $lxpos, $lypos, $t);
        if($tot > 0)
        {
            $divs = array();
            foreach($d as $k=>$v)
                $divs[$k] = $v*$hightMultiplier;
            if(($showAsCorrect!==false)&&($showAsCorrect[$t]))
            	$graph->filledconfrectangle($colPos, $baseLine, $colPos + $colWidth, $baseLine-($tot*$hightMultiplier), $divs, true);
            else
            	$graph->filledconfrectangle($colPos, $baseLine, $colPos + $colWidth, $baseLine-($tot*$hightMultiplier), $divs);
        }
    }


	$graph->render();

}

function getTextWidth($text, $font)
{
    $width  = array(1 => 5, 6, 7, 8, 9);
    return $width[$font] * strlen($text);
}

function getTextHeight($text, $font)
{
    $height = array(1 => 6, 8, 13, 15, 15);
    return $height[$font];
}


class MyImage
{
    var $lineColour;
    var $background;
    var $fillColour;

	function MyImage($width,$height, $bgr=255, $bgg=255, $bgb=255)
    {
		$this->imgWidth=$width;
		$this->imgHeight=$height;
		$this->image=imagecreate($this->imgWidth, $this->imgHeight);
        $this->background = imagecolorallocate($this->image, $bgr, $bgg, $bgb);
		$this->white = imagecolorallocate($this->image, 255, 255, 255);
		$this->grey = imagecolorallocate($this->image, 128, 128, 128);
		$this->black = imagecolorallocate($this->image, 0, 0, 0);
		$this->red = imagecolorallocate($this->image, 255, 0, 0);
		$this->green = imagecolorallocate($this->image, 0, 255, 0);
        $this->greena = array();
		$this->greena[1] = imagecolorallocate($this->image, 0, 255, 0);
		$this->greena[2] = imagecolorallocate($this->image, 64, 255, 64);
		$this->greena[3] = imagecolorallocate($this->image, 128, 255, 128);
		$this->greena[4] = imagecolorallocate($this->image, 192, 255, 192);
		$this->yellow = imagecolorallocate($this->image, 255, 255, 0);
		$this->blue = imagecolorallocate($this->image, 0, 0, 255);
        $this->bluea = array();
		$this->bluea[1] = imagecolorallocate($this->image, 0, 0, 255);
		$this->bluea[2] = imagecolorallocate($this->image, 64, 64, 255);
		$this->bluea[3] = imagecolorallocate($this->image, 128, 128, 255);
		$this->bluea[4] = imagecolorallocate($this->image, 192, 192, 255);
		$this->lightblue = imagecolorallocate($this->image, 128, 128, 255);
        $this->lineColour = $this->black;
        $this->fillColour = $this->grey;
       	$style = array($this->black, $this->black, $this->black, $this->black, $this->black, $this->black, $this->white, $this->white, $this->white, $this->white);
		imagesetstyle($this->image, $style);
        $this->blackdash = IMG_COLOR_STYLED;
    }

    function save($fname)
    {
		imagepng($this->image, $fname, 9);
    }

	function render()
    {
    	header("Content-type: image/png");
		imagepng($this->image);
		imagedestroy($this->image);
    }

    function string ($font, $x, $y, $theString)
    {
        return imagestring($this->image, $font, $x, $y, $theString, $this->lineColour);
    }

    function filledrectangle($x1, $y1, $x2, $y2)
    {
        imagefilledrectangle($this->image, $x1 , $y1 , $x2 ,  $y2 , $this->fillColour);
    }

    function filledborderrectangle($x1, $y1, $x2, $y2, $highlight = false)
    {
        imagefilledrectangle($this->image, $x1 , $y1 , $x2 ,  $y2 , $this->black);
        if($highlight)
	        return imagefilledrectangle($this->image, $x1+1 , $y1-1 , $x2-1 ,  $y2+1 , $this->green);
        else
	        return imagefilledrectangle($this->image, $x1+1 , $y1-1 , $x2-1 ,  $y2+1 , $this->lightblue);
    }

    function filledconfrectangle($x1, $y1, $x2, $y2, $divs, $highlight = false)
    {
        imagefilledrectangle($this->image, $x1 , $y1 , $x2 ,  $y2 , $this->black);
        if($highlight)
            $colours = $this->greena;
        else
            $colours = $this->bluea;
        $ytop = $y2+1;
        foreach($divs as $k=>$v)
        {
            if($v > 0)
            {
            $ybase = $ytop+$v > $y1-1 ? $y1-1 :  $ytop+$v;
            imagefilledrectangle($this->image, $x1+1 , $ybase , $x2-1 ,  $ytop , $colours[$k]);
            $ytop += $v;
            }
        }
    }

    function line($x1, $y1, $x2, $y2)
    {
        return imageline($this->image, $x1, $y1, $x2, $y2, $this->lineColour);
    }

}



?>
