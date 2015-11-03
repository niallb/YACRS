<?php

function customScoring($questionInsts, $questions, $responses)
{
    $out = '';
        //$out .= '<pre>'.print_r($questionInsts,1).'</pre>';
        //$out .= '<pre>'.print_r($questions,1).'</pre>';
        //$out .= '<pre>'.print_r($responses,1).'</pre>';
    $results = array();
	foreach($questionInsts as $qi)
    {
        $day = strftime("%a %d %b %Y", ((floor($qi->endtime / (3600*24)) * 3600 * 24)+3600));
        if(!isset($results[$day]))
            $results[$day] = array('qtotal'=>0, 'qatt'=>0, 'qcorr'=>0, 'pitotal'=>0, 'piatt'=>0, 'picorr'=>0, );
        if($qi->extras['category'] == 'Q')
        {
            $results[$day]['qtotal']++;
            if(isset($responses[$qi->id]))
            {
                $results[$day]['qatt']++;
                if($questions[$qi->id]->definition->score($qi, $responses[$qi->id]) > 0)
                {
                    $results[$day]['qcorr']++;
                }
            }
        }
        elseif($qi->extras['category'] == 'PI')
        {
            $results[$day]['pitotal']++;
            if(isset($responses[$qi->id]))
            {
                $results[$day]['piatt']++;
                if($questions[$qi->id]->definition->score($qi, $responses[$qi->id]) > 0)
                {
                    $results[$day]['picorr']++;
                }
            }
        }
    }
    $out .= "<table border='1'><tr><th>&nbsp;</th><th colspan='4'>Quiz</th><th colspan='5'>PI</th></tr>";
    $out .= "<tr><th>Date</th><th>Total</th><th>Attempted</th><th>Correct</th><th>%age Correct</th><th>Total</th><th>Attempted</th><th>Correct</th><th>%age Correct</th><th>Point</th></tr>";
    foreach($results as $d=>$r)
    {
        $out .= "<tr><td>$d</td>";
        $out .= "<td>{$r['qtotal']}</td>";
        $out .= "<td>{$r['qatt']}</td>";
        $out .= "<td>{$r['qcorr']}</td>";
        if($r['qtotal'] > 0)
            $out .= "<td>".round($r['qcorr'] * 100/ $r['qtotal'], 0)."</td>";
        else
            $out .= "<td>-</td>";
        $out .= "<td>{$r['pitotal']}</td>";
        $out .= "<td>{$r['piatt']}</td>";
        $out .= "<td>{$r['picorr']}</td>";
        if($r['pitotal'] > 0)
        {
	        $pipc = round($r['picorr'] * 100/ $r['pitotal'], 0);
	        $out .= "<td>{$pipc}</td>";
	        $out .= "<td>". (($pipc >= 50) ? "1" : "0") ."</td>";
        }
        else
        {
            $out .= "<td>-</td><td>-</td>";
        }
    }
    $out .= "</table>";
    return $out;
}


