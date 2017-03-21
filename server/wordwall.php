<?php
require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
require_once('lib/PHP_Word_Cloud-master/tagcloud.php');


define('IMGWIDTH', 640);
define('IMGHEIGHT', 480);

$qiID = $_REQUEST['qiID'];
$qi = questionInstance::retrieve_questionInstance($qiID);
$qu = question::retrieve_question($qi->theQuestion_id);
if($qu !== false)
{
    $full_text = array();
	$responses = response::retrieve_response_matching('question_id', $qiID);
	if($responses)
	{
   		foreach($responses as $r)
		{
            $key = trim(strtolower($r->value));
			if(strlen($key))
			{
                if(!isset($full_text[$key]))
				    $full_text[$key] = array('word'=>$r->value, 'title'=>$r->value, 'count'=>1);
                else
                    $full_text[$key]['count']++;
			}
		}
	}
	$font = 'lib/PHP_Word_Cloud-master/LiberationSerif-Italic.ttf';
	$cloud = new WordCloud(IMGWIDTH, IMGHEIGHT, $font, $full_text);
	$palette = Palette::get_palette_from_hex($cloud->get_image(), array('0000A7', '0000DF', '33004F', '4473cc'));
	$cloud->render($palette);
   	header("Content-type: image/png");
	imagepng($cloud->get_image());
	imagedestroy($cloud->get_image());
}




?>
