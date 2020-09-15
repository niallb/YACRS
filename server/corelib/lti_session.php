<?php

/* Still needs to ensure everything is UTF8 encoded */
class ltiSession
{
	var $params;

	function ltiSession($params)
    {
        $this->params = $params;
    }

    static function Create($secretManager, $params)
    {
    	if(!is_object($secretManager))
        	return false;
        if(!is_array($params))
        	return false;
        if(!isset($params["oauth_signature"]))
            return null;
        if((!isset($params["oauth_signature_method"]))||($params["oauth_signature_method"]!="HMAC-SHA1"))
            return false;
        if(!isset($params["oauth_nonce"]))
            return false;
        if(!isset($params["oauth_consumer_key"]))
            return false;
        if(!$secretManager->registerNonce($params["oauth_nonce"], $params["oauth_consumer_key"]))
            return false;
        if((!isset($params["oauth_timestamp"]))||($params["oauth_timestamp"]<time()-3600))
            return false;
        $oldsig = $params["oauth_signature"];
        unset($params["oauth_signature"]);
        $sig = ltiSession::getOAuthSignature($params, getRequestURL(), $_SERVER['REQUEST_METHOD'], $secretManager->getSecret($params["oauth_consumer_key"]));
        if($sig == $oldsig)
        	return new ltiSession($params);
        else
        {
        	//echo "$sig<br/>$oldsig</br>";
        	return false;
        }
    }

    function getResourceKey()
    {
    	if(isset($this->params['oauth_consumer_key']) && isset($this->params['resource_link_id']))
        	return $this->params['oauth_consumer_key'].':'.$this->params['resource_link_id'];
        else
        	return false;
    }

    function isInstructor()
    {
    	if(isset($this->params['roles']) && (strpos($this->params['roles'], 'Instructor')!==false))
        	return true;
        else
        	return false;
    }

    static function getOAuthSignature($params, $endpoint, $method, $oauth_consumer_secret)
    {
        $basestring = $method.'&';
        //IMS code uses str_replace('+',' ',str_replace('%7E', '~', rawurlencode($input))); for RFC 3986
        if(strpos($endpoint,'?'))
        {
        	// get params have to be put into the OAuth parameters rather than the URL for sdigning.
            list($endpoint, $getparams) = explode('?', $endpoint,2);
            $getparams = explode('&',$getparams);
            foreach($getparams as $p)
            {
            	list($k,$v) = explode('=',$p,2);
                $params[$k] = rawurldecode($v);
            }
        }
        $basestring .= rfc3986encode($endpoint).'&'; // PHP manual says rawurlencode is RFC 3986, need to check.
        ksort($params);
		foreach($params as $k=>$v)
        {
        	$basestring .= rfc3986encode($k.'='.rfc3986encode($v).'&');
        }
        // Strip away last encoded '&';
        $basestring = substr($basestring, 0, strlen($basestring)-3);
        	//echo '<br/><b>My Userinfo structure contains:</b><pre>'.print_r($userinfo,1).'</pre>';
        //echo "\n<p>\n$basestring\n</p>\n";
		$signingkey = rfc3986encode($oauth_consumer_secret).'&';
        $computed_signature = base64_encode(hash_hmac('sha1', $basestring, $signingkey, true));
        return $computed_signature;
    }

    function gradeMethodsAvailable()
    {
    	if((isset($this->params['lis_outcome_service_url']))&&(isset($this->params['lis_result_sourcedid'])))
        	return true;
        else
        	return false;
    }

    function setGrade($secretManager, $grade)
    {
       	return $this->set_lis_Grade($secretManager, $grade);
    }

    private function set_lis_Grade($secretManager, $grade)
    {
		$message = 'basic-lis-updateresult';
		$url = $this->params['lis_outcome_service_url'];
		$id = $this->params['lis_result_sourcedid'];

    	$oauth_nonce = md5(time().$this->params['user_id']);


    	$msgbody = '<?xml version = "1.0" encoding = "UTF-8"?>
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
	<imsx_POXHeader>
		<imsx_POXRequestHeaderInfo>
			<imsx_version>V1.0</imsx_version>
			<imsx_messageIdentifier>MESSAGE</imsx_messageIdentifier>
		</imsx_POXRequestHeaderInfo>
	</imsx_POXHeader>
	<imsx_POXBody>
		<OPERATION>
			<resultRecord>
				<sourcedGUID>
					<sourcedId>SOURCEDID</sourcedId>
				</sourcedGUID>
				<result>
					<resultScore>
						<language>en-us</language>
						<textString>GRADE</textString>
					</resultScore>
				</result>
			</resultRecord>
		</OPERATION>
	</imsx_POXBody>
</imsx_POXEnvelopeRequest>';
		$msgbody = trim(str_replace(array('MESSAGE','OPERATION','SOURCEDID','GRADE'), array($oauth_nonce, 'replaceResultRequest', $id, $grade) ,$msgbody));
	    $hash = base64_encode(sha1($msgbody, TRUE));

		$data = array(
          'oauth_version' => '1.0',
          'oauth_nonce' => $oauth_nonce,
          'oauth_timestamp' => time(),
          'oauth_signature_method' => 'HMAC-SHA1',
          'oauth_consumer_key' => $this->params["oauth_consumer_key"],
          'oauth_body_hash' => $hash,
        );

        //echo "<h5>Grade URL is $url</H5>";
    	$data['oauth_signature'] = ltiSession::getOAuthSignature($data, $url, "POST", $secretManager->getSecret($this->params["oauth_consumer_key"]));
        $header = "Authorization: OAuth ";
        foreach($data as $k => $v)
        	$header .= "$k=\"".rfc3986encode($v)."\",";
        $header = substr($header, 0, strlen($header)-1);
        $header .= "\r\nContent-type: application/xml\r\n";

		$retval = do_post_request($url, $msgbody, $header);
        return $retval;
        $POXResponse = new cls_imsx_POXEnvelopeResponse($retval);
        if(isset($POXResponse->m_imsx_POXHeader->m_imsx_POXResponseHeaderInfo->m_imsx_statusInfo->m_imsx_description))
        	return $POXResponse->m_imsx_POXHeader->m_imsx_POXResponseHeaderInfo->m_imsx_statusInfo->m_imsx_description;
        else
        	return "Error (IMS POX response message not found)";
    }
};

function do_post_request($url, $data, $optional_headers = null)
{
	$header = '';
	$params = array('http' => array(
	            'method' => 'POST',
	            'content' => $data
	          ));
	 // To use a proxy, somthing like this, or maybe use curl...
	//$params['http']['proxy'] = 'tcp://127.0.0.1:8080'; $params['http']['request_fulluri'] = true;


	if ($optional_headers !== null)
    {
		$header = $optional_headers . "\r\n";
	}
	//$header = $header . "Content-type: application/x-www-form-urlencoded\r\n";
	$params['http']['header'] = $header;
	$ctx = stream_context_create($params);
	$fp = @fopen($url, 'rb', false, $ctx);
	if (!$fp)
    {
		throw new Exception("Problem with $url, $php_errormsg");
	}
	$response = @stream_get_contents($fp);
	if ($response === false)
    {
		throw new Exception("Problem reading data from $url, $php_errormsg");
	}
	return $response;
}


abstract class ltiSecretManager
{
    abstract function getSecret($key); // String
    abstract function getDomain($key); // String
    abstract function registerNonce($nonce, $consumerKey); // boolean
};

function rfc3986encode($input)
{
	return str_replace('+',' ',str_replace('%7E', '~', rawurlencode($input)));
}

function getRequestURL()
{
	$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	if (($_SERVER["SERVER_PORT"] != "80")&&($_SERVER["SERVER_PORT"] != "443"))
	{
	    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	}
	else
	{
	    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}



?>
