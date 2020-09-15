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

    function report($thisSession, $qi, $detailed = false)
    {
        return "";
    }

    function getModifiedCopyForm()
    {
        $form = $this::getEditForm();
        return $form;
    }

    static function questionTypeName() // Used for looking up classes and help files
    {
        return '';
    }

    static function getEditForm()
    {
        return false;
    }
}
