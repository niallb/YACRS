<?php

// The purpose of this otherwise pointless function is to allow searches for possibly hazardous
// users of $_REQUEST over the rest of the code th not throw up false positives.
function requestSet($name)
{
    return isset($_REQUEST[$name]);
}

function requestHtml($name, $defaultVal=false, $allowTags=array())
{
// This is a temporary implementation that does basic sanitizing.
    if(isset($_REQUEST[$name]))
        return htmlspecialchars(strip_tags($_REQUEST[$name]));
    else
        return $defaultVal;
}

function requestInt($name, $defaultVal=false)
{
    if(isset($_REQUEST[$name]))
        return intval(trim($_REQUEST[$name]));
    else
        return $defaultVal;
}

function requestStr($name, $defaultVal=false)
{
    if(isset($_REQUEST[$name]))
        return preg_replace('/[^\w\s]+/', '', $_REQUEST[$name]);
    else
        return $defaultVal;
}

function requestRaw($name, $defaultVal=false)
{
    if(isset($_REQUEST[$name]))
        return $_REQUEST[$name];
    else
        return $defaultVal;
}

function requestNum($name, $defaultVal=false)
{
    if(isset($_REQUEST[$name]))
    {
        if(preg_match('/^-?[0-9]+$/', trim($_REQUEST[$name])))
            return intval(trim($_REQUEST[$name]));
        else
            return floatval(trim($_REQUEST[$name]));
    }
    else
        return $defaultVal;
}

function requestHex($name, $defaultVal=false)
{
    if((isset($_REQUEST[$name]))&&(preg_match('/^[0-9a-fA-F]+$/', trim($_REQUEST[$name]))))
        return trim($_REQUEST[$name]);
    else
        return $defaultVal;
}

function requestIdent($name, $defaultVal=false)
{
    if((isset($_REQUEST[$name]))&&(preg_match('/^[a-zA-Z_]\w*$/', trim($_REQUEST[$name]))))
        return trim($_REQUEST[$name]);
    else
        return $defaultVal;
}


