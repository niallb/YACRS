<?php

abstract class questionBase
{
    abstract function checkResponse($qiID, $resp, $SMSResp=false);
    abstract function getGraphLabels();
    abstract function render($title='');
    function score($qi, $resp)
    {
        return 0;
    }

    function getCorrectStr($qi)
    {
        return false;
    }

    function getCorrectForDisplay($qi)
    {
        return '';
    }

    function getResponseForDisplay($resp)
    {
        if($resp==false)
            return '(none)';
        else
        	return $resp->value;
    }

    abstract function report($thisSession, $qi, $detailed = false);
    abstract static function getEditForm();
}
