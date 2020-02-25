<?php
define('MAX_CLOUD_WORDS', 150);
define('MIN_CLOUD_WORDS', 30);

function wordcloud($qiID, $responses, $width='95%',  $height='95%')
{
    //# When used with YACRScontrol the font in the text shoulw be done in vh units
    $out = "<div id='wordcloud_container' style='width:{$width}; height:{$height}; border: 1px solid #000;'>
              <div id='wordcloud' style='width:100%; height:100%;'></div>
              <div id='responses' style='width:100%; height:100%; font: 15pt arial, sans-serif; overflow: scroll;'>dgdfgds</div>
           </div>";

    $wds = array();
    $respsForWrd = array();
    $stopwords = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    $totalwrds = 0;
    $totalchars = 0;
    $uniquewrds = 0;
    $sizeOrGreater = array(0, 0);
    if($responses)
    {
        foreach($responses as $r)
        {
            $r->value = preg_replace('/[^\w\s]+/', ' ', strtolower($r->value));
            $r->value = trim(preg_replace('/\s+/', ' ', $r->value));
            $iw = explode(' ', $r->value);
            foreach($iw as $w)
            {
                if((strlen($w)>2)&&(!in_array($w, $stopwords)))
                {
                    $totalwrds++;
                    $totalchars += strlen($w);
                    if(isset($wds[$w]))
                    {
                        $uniquewrds++;
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
                        $respsForWrd[$w] = array($r->id);
                    }
                }
            }
        }
    }
    arsort($wds);
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
    $sizeMult = 15000 / $area;
    if(($firstsize * $sizeMult) > 1000)
        $sizeMult = 1000 / $firstsize;

    $jsWords = '[';
    foreach($wds as $wrd=>$count)
    {
        if($count >= $limCount)
        {
            $size = $sizeMult/sqrt($totalwrds) * (pow($count, 0.65));
            $jsWords .= "{\"text\": \"{$wrd}\", \"url\": \"ajax/responses.php?qiID={$qiID}&containing={$wrd}\", \"size\": {$size}},\n";
        }
    }
    $jsWords .= ']';
    $out .= "<script>\n var words = $jsWords\n generateCloud(words, 1000, 600);\n</script>";

    return $out;
}

?>