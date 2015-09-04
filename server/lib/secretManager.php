<?php
include_once('lib/database.php');

class secretManager extends ltiSecretManager
{
    function getSecret($key)
    {
    	$hash = md5($key);
        $client = lticonsumer::retrieve_lticonsumer_matching('keyHash',$hash);
        if($client === false)
        	return false;
        else
        	return $client[0]->secret;
    }

    function getDomain($key)
    {
    	$hash = md5($key);
        $client = lticonsumer::retrieve_lticonsumer_matching('keyHash',$hash);
        if($client === false)
        	return false;
        else
        	return $client->domain;
    }

    function registerNonce($nonce, $consumerKey)
    {
    	return true;
    }

}


