<?php
define('MAX_CLOUD_WORDS', 150);
define('MIN_CLOUD_WORDS', 30);

function wordcloud_ta($responses, $phrases=array(), $width='95%',  $height='95%', $target=false, $forAjax = false, $extraAjaxParams=false)
{
    if($target == false)
        $target = 'wordcloud';
    //# When used with YACRScontrol the font in the text should be done in vh units
    $out = "<div id='{$target}_container' style='width:{$width}; height:{$height}; border: 1px solid #000;'>
              <div id='{$target}' style='width:100%; height:100%;'></div>
           </div>";

    $wds = array();
    $respsForWrd = array();
    $stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    $totalwrds = 0;
    $totalchars = 0;
    $uniquewrds = 0;
    $sizeOrGreater = array(0, 0);
    $phrases_rep = str_replace(' ', '_', $phrases);
    if($responses)
    {
        foreach($responses as $r)
        {
            $value = preg_replace('/[^\w\s]+/', ' ', strtolower($r->value));           
            $value = trim(preg_replace('/\s+/', ' ', $value));
            $value = str_replace($phrases, $phrases_rep, $value);
            $iw = explode(' ', $value);
            foreach($iw as $w)
            {
                if((strlen($w)>2)&&(!in_array($w, $stopwords)))
                {
                    $w = str_replace('_', ' ', $w);
                    $totalwrds++;
                    $totalchars += strlen($w);
                    if(isset($wds[$w]))
                    {
                        $wds[$w]++;
                        if(isset($sizeOrGreater[$wds[$w]]))
                            $sizeOrGreater[$wds[$w]]++;
                        else
                            $sizeOrGreater[$wds[$w]] = 1;
                        if(!in_array($r->id, $respsForWrd[$w]))
                            $respsForWrd[$w][] = $r->id;
                    }
                    else
                    {
                        $wds[$w]=1;
                        $uniquewrds++; 
                        $respsForWrd[$w] = array($r->id);
                    }
                }
            }
        }
    }
    arsort($wds);
   // echo '<pre>'.print_r($wds, true).'</pre>';
    $sizeOrGreater[1] = $uniquewrds;
    $limCount = 1;
    while(($sizeOrGreater[$limCount] > MAX_CLOUD_WORDS)&&(isset($sizeOrGreater[$limCount+1])))
        $limCount++;
    if(($limCount > 0)&&($sizeOrGreater[$limCount] <= MIN_CLOUD_WORDS))
        $limCount--;

    $area = 0;
    $firstsize = false;
    foreach($wds as $wrd=>$count)
    {
        if(!$firstsize)
        {
            $firstsize = 1/sqrt($totalwrds) * (pow($count, 0.65)) * strlen($wrd);
        }
        if($count >= $limCount)
        {
            $area += pow((1/sqrt($totalwrds) * (pow($count, 0.65)) * strlen($wrd)), 2);
        }
    }
    if($area > 0)
        $sizeMult = 15000 / $area;
    else
        $sizeMult = 10; // no data, so just put in a figure to avoid error messages.
    if(($firstsize * $sizeMult) > 1000)
        $sizeMult = 1000 / $firstsize;

    $jsWords = '[';
    foreach($wds as $wrd=>$count)
    {
        if($count >= $limCount)
        {
            $size = $sizeMult/sqrt($totalwrds) * (pow($count, 0.65));
            $jsWords .= "{\"text\": \"{$wrd}\", \"url\": \"ajax/textAnalysisAjax.php?containing={$wrd}&{$extraAjaxParams}\", \"size\": {$size}},\n";
        }
    }
    $jsWords .= ']';
    $script = "var words_{$target} = $jsWords\n generateCloud(words_{$target}, 1000, 600, \"{$target}\");";
    if($forAjax)
    {
        $ajaxinfo = new stdClass();
        $ajaxinfo->html = $out;
        $ajaxinfo->eval = $script;
        return $ajaxinfo;
    }
    else
    {
        $out .= "<script>\n {$script}\n</script>";
        return $out;
    }
}

function findPhrases($responses)
{
    $stopwords = array("i", "a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    $WordPhrases = array();
    for($phrase_length = 2; $phrase_length-5; $phrase_length++)
    {
        $pl2 = $phrase_length-1;
        foreach($responses as $response)
        {
            preg_match_all('/\w[^,.?]+/', $response->value, $matches);
            foreach($matches[0] as $line)
            {
                $words = explode(' ', trim(preg_replace('/([^\w\']+)/', ' ', strtolower($line))));
                for($n=0; $n<sizeof($words)-$pl2; $n++)
                {
                    if((!in_array($words[$n], $stopwords))&&!in_array($words[$n+$pl2], $stopwords))
                    {
                        $TwoWP = implode(' ', array_slice($words, $n, $phrase_length));
                        if(isset($WordPhrases[$TwoWP]))
                            $WordPhrases[$TwoWP]++;
                        else
                            $WordPhrases[$TwoWP] = 1;
                    }
                }
            }
        }
    }
    arsort($WordPhrases);
    $ret = array();
    foreach($WordPhrases as $twp=>$count)
    {
        if($count >=2)
        {
            $ret[$twp] =$count;
        }
    }
    return $ret;
}

function containsStr($haystack, $needle)
{
    $needle = ' '.strtolower($needle).' ';
    $haystack = ' '.preg_replace('/\W+/', ' ', strtolower($haystack)).' ';
    if(strpos($haystack, $needle) !== false)
        return true;
    else
        return false;
}

?>