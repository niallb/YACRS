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

    function getDisplayURL($qiID)
    {
        return "";
    }

    abstract function report($thisSession, $qi, $detailed = false);
    //static function getEditForm();
}