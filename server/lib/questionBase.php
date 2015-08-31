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

    abstract function report($thisSession, $qi, $detailed = false);
    abstract static function getEditForm();
}
