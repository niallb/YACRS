<?php

function displayQuestion($qi, $resp, $forceTitle=false)
{
    global $thisSession, $smemb, $CFG;
    $out = '';
    $qu = question::retrieve_question($qi->theQuestion_id);
    if($qu)
    {
        $qu->definition->checkResponse($qi->id, $resp);
        // New & replacement responses added here, partial responses for questions that support it done by checkResponse
        if($qu->definition->responseValue !== false)
            $resp == response::CreateOrUpdate($smemb->id, $qi->id, $qu->definition->responseValue);

        $out .= '<fieldset>';
        if($resp == false)
            $out .= '<legend>Input:</legend>';
        elseif(isset($_REQUEST['doupdate']))
            $out .= '<legend>Update answer:</legend>';
        else
            $out .= '<legend>You answered:</legend>';
        //$out .= '<pre>'.print_r($resp,1).'</pre>';
        $out .= "<form id='questionForm' method='POST' action='vote.php' class='form-horizontal'>";
        $out .= "<input type='hidden' name='sessionID' value='{$thisSession->id}'/>";
        $out .= "<input type='hidden' name='qiID' value='{$qi->id}'/>";
        if($forceTitle)
        {
            $qu->definition->displayTitle = true;
        }
        $out .= $qu->definition->render($qi->title);
        if(($qu->definition->responseValue === false)||(($thisSession->allowQuReview)&&(isset($_REQUEST['doupdate']))))
        {
            $out .= "<input id='submitButton' type='submit' name='submitans' value='Save Answer' class='btn btn-primary' />";
        }
        elseif(($thisSession->allowQuReview)&&($qu->definition->allowReview()))
        {
            $out .= "<a href='vote.php?sessionID={$thisSession->id}&qiID={$qi->id}&doupdate=1' id='changeButton' class='btn btn-success'>Change Answer</a>";
        }
        $out .= '</fieldset>';
        $out .= "<input type='hidden' name='loginCookie' value='{$_COOKIE[$CFG['appname'].'_login']}'/>";
        $out .= "</form>";
    }
    return $out;
}

?>
