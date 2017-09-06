<?php
require_once('config.php');
require_once('lib/database.php');
require_once('lib/shared_funcs.php');
require_once('lib/PHP_Word_Cloud-master/tagcloud.php');


define('IMGWIDTH', 640);
define('IMGHEIGHT', 480);

// from http://xpo6.com/list-of-english-stop-words/
$stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");

$qiID = $_REQUEST['qiID'];
$qi = questionInstance::retrieve_questionInstance($qiID);
$qu = question::retrieve_question($qi->theQuestion_id);
if($qu !== false)
{
    $full_text = array();
    $word_list = array();
    $resplen = 0;
    $respcount = 0;
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
            $words = explode(' ', preg_replace('/[^\w]+/', ' ', $r->value));
            if(sizeof($words))
            {
                $resplen += sizeof($words);
                $respcount++;
            }
            foreach($words as $w)
            {
                if((strlen($w))&&(!in_array($w, $stopwords)))
                {
		            $key = trim(strtolower($w));
					if(strlen($key))
					{
		                if(!isset($word_list[$key]))
						    $word_list[$key] = array('word'=>$w, 'title'=>$w, 'count'=>1);
		                else
		                    $word_list[$key]['count']++;
					}
                }
            }
		}
 	}
    //exit('<pre>'.print_r($full_text, true).'</pre>');
    //exit('<pre>'.print_r($word_list, true).'</pre>');
	$font = 'lib/PHP_Word_Cloud-master/LiberationSerif-Italic.ttf';
    if(($respcount>0)&&($resplen/$respcount > 2.5))
    	$cloud = new WordCloud(IMGWIDTH, IMGHEIGHT, $font, $word_list);
    else
    	$cloud = new WordCloud(IMGWIDTH, IMGHEIGHT, $font, $full_text);
	$palette = Palette::get_palette_from_hex($cloud->get_image(), array('0000A7', '0000DF', '33004F', '4473cc'));
	$cloud->render($palette);
   	header("Content-type: image/png");
	imagepng($cloud->get_image());
	imagedestroy($cloud->get_image());
}




?>
