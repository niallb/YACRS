<?php

$questionTypes['text'] = array('name'=>'Text input suitable for 2 or three sentences.', 'class'=>'TextQuestion', 'edit'=>'editTextQuestion_form');

class TextQuestion extends questionBase
{
    var $stem;
    var $responseValue;
    var $wordLimit;

	function __construct($stem, $displayStem, $wordLimit)
	{
        $this->stem = $stem;
        $this->displayStem = $displayStem;
        $this->wordLimit = $wordLimit;
    }

    function checkResponse($qiID, $resp, $SMSResp=false)
    {
        if($resp === false)
	        $this->responseValue = false;
        else
            $this->responseValue = $resp->value;

        if((isset($_REQUEST['submitans']))&&($_REQUEST['qiID']==$qiID))
        {
            $this->responseValue = trim(requestHtml('Ans'));
            if($this->wordLimit>0)
            {
                $wordcount = str_word_count($this->responseValue);
                if($wordcount > $this->wordLimit)
                {
                	$this->responseValue = implode(' ',array_slice(explode(' ',preg_replace('/\s+/',' ',$this->responseValue)),0,$this->wordLimit));
                }
            }
        }
    }

    function getGraphLabels()
    {
        return $this->stem;
    }

    function allowReview()
    {
        return true;
    }

    function render($title='')
    {
        //$out = '<fieldset>';
        //$out .= '<legend>Input:</legend>';
        if($this->displayStem)
            $out .= "<p class='stem'>{$this->stem}</p>";
        $out .= "<div class='wide buttonlist'>";
        $out .= "<textarea name='Ans' id='R0' rows='6' style='width:100%;'";
        if(($this->responseValue !== false)&&(!isset($_REQUEST['doupdate'])))
            $out .= " disabled='1'";
        $out .= '>';
        if($this->responseValue!==false)
        {
            $out .= htmlspecialchars($this->responseValue);
        }
        $out .= '</textarea>';
        if($this->wordLimit>0)
        {
            $out .= "(Word limit: $this->wordLimit)";
        }
        $out .= '</div>';
        //if($this->responseValue == false)
        //{
        //    $out .= "<div class='submit'><input type='submit' name='submitans' value='Submit answer'/></div>";
        //}
        //$out .= '</fieldset>';
        return $out;
    }

    function report($thisSession, $qi, $detailed = false, $anonymous=false)
    {
	    //$label = $this->getGraphLabels();
        global $uinfo;
        $out = '';
	    $responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
        $rcounts = array();
        if($responses !== false)
        {
	        foreach($responses as $r)
	        {
	        	$cleanr = preg_replace('/\s+/',' ',trim($r->value));
	            if(isset($rcounts[$cleanr]))
	                $rcounts[$cleanr]['count']++;
	            else
	                $rcounts[$cleanr] = array('count'=>1, 'rid'=>$r->id);
	        }
        }
        uasort($rcounts, 'rcount_cmp');

		    //$out .= "<p><a href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}'>Summary view</a></p>";
	        if($responses)
	        {
		    	$out .= "<p><b>".sizeof($responses)." response(s).</b></p>";
	            $out .= "<img src='wordwall.php?qiID={$qi->id}'/><br/>";
	        }
		    $out .= "<table border='1'><thead>";
            if(!$anonymous)
                $out .= "<tr><th>User</th><th>Name</th>";
            $out .= "<th>Response</th></thead><tbody>";
	        if($responses)
	        {
		        foreach($responses as $r)
		        {
		        	$member = sessionMember::retrieve_sessionMember($r->user_id);
			        $out .= "<tr>";
                    if(!$anonymous)
                        $out .= "<td>{$member->userID}</td><td>{$member->name}</td>";
                    $out .= "<td>{$r->value}</td></tr>";
		        }
	        }
		    $out .= "</table>";
        return $out;
    }

    function getDisplayURL($qiID)
    {
        return "chartWrap.php?qiID={$qiID}";
    }

    static function getEditForm()
    {
    	$form = new editTTCQuestion_form();
        return $form;
    }
}

class editTextQuestion_form extends nbform
{
	var $form_magic_id = 'dfb90d549954fa2982929d9ab2f93cfe';
	var $sessionID; //hidden
	var $id; //hidden
	var $title; //string
	var $displayStem; //boolean
	var $characterLimit; //integer
	var $wordLimit; //integer
	var $multiuse; //boolean
    var $anonymous; //boolean
	var $validateMessages;

	function __construct($readform=true)
	{
		parent::__construct();
		$this->validateMessages = array();
		if($readform)
		{
			$this->readAndValidate();
		}
        else
        {
           $this->displayStem = true;
        }
	}

	function setData($data)
	{
		$this->sessionID = $data->sessionID;
		$this->id = $data->id;
		$this->title = $data->title;
		$this->displayStem = $data->displayStem;
		$this->characterLimit = $data->characterLimit;
		$this->wordLimit = $data->wordLimit;
		$this->multiuse = $data->multiuse;
		$this->anonymous = $data->anonymous;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->id = $this->id;
		$data->title = $this->title;
		$data->displayStem = $this->displayStem;
		$data->characterLimit = $this->characterLimit;
		$data->wordLimit = $this->wordLimit;
		$data->multiuse = $this->multiuse;
		$data->anonymous = $this->anonymous;
		return $data;
	}

	function readAndValidate()
	{
		$isCanceled=false;
		if((isset($_REQUEST['editTTCQuestion_form_code']))&&($_REQUEST['editTTCQuestion_form_code'] == $this->form_magic_id))
		{
			$this->sessionID = $_REQUEST['sessionID'];
			$this->id = $_REQUEST['id'];
			$this->title = stripslashes($_REQUEST['title']);
			$this->displayStem = (isset($_REQUEST['displayStem'])&&($_REQUEST['displayStem']==1)) ? true : false;
			$this->characterLimit = intval($_REQUEST['characterLimit']);
			$this->wordLimit = intval($_REQUEST['wordLimit']);
			$this->multiuse = (isset($_REQUEST['multiuse'])&&($_REQUEST['multiuse']==1)) ? true : false;
			$this->anonymous = (isset($_REQUEST['anonymous'])&&($_REQUEST['anonymous']==1)) ? true : false;
			if('Cancel' == $_REQUEST['submit'])
				$isCanceled = true;
			$isValid = $this->validate();
			if($isCanceled)
				$this->formStatus = FORM_CANCELED;
			elseif($isValid)
				$this->formStatus = FORM_SUBMITTED_VALID;
			else
				$this->formStatus = FORM_SUBMITTED_INVALID;
		}
		else
			$this->formStatus = FORM_NOTSUBMITTED;
	}

	function validate()
	{
		$this->validateMessages = array();
		// Put custom code to validate $this->sessionID here (to stop hackers using this as a way in.)
		// Put custom code to validate $this->id here (to stop hackers using this as a way in.)
		if(strlen($this->title)>80)
		{
		    $this->title = substr($this->title,0,80);
		    $this->validateMessages['title'] = "This field was too long and has been truncated.";
		}
		// Put custom code to validate $this->title here. Error message in $this->validateMessages['title']
		// Put custom code to validate $this->displayStem here. Put error message in $this->validateMessages['displayStem']
		if(!is_numeric(trim($_REQUEST['characterLimit'])))
		{
			$validateMsg['$this->characterLimit'] = "You must give an numeric value here.";
			$ok = false;
		}
		if(!is_numeric(trim($_REQUEST['wordLimit'])))
		{
			$validateMsg['$this->wordLimit'] = "You must give an numeric value here.";
			$ok = false;
		}
		// Put custom code to validate $this->multiuse here. Put error message in $this->validateMessages['multiuse']
		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
		$out = '';
		$out .= $this->formStart();
		$out .= $this->hiddenInput('editTTCQuestion_form_code', $this->form_magic_id);
		$out .= $this->hiddenInput('sessionID', $this->sessionID);
		$out .= $this->hiddenInput('id', $this->id);
		$out .= $this->textInput('Title/Stem', 'title', $this->title, $this->validateMessages, 80);
		$out .= $this->checkboxInput('Display stem to participants.', 'displayStem', $this->displayStem, $this->validateMessages);
		$out .= $this->textInput('Maximum response length (characters, blank or 0 for unlimited)', 'characterLimit', $this->characterLimit, $this->validateMessages, 8);
		$out .= $this->textInput('Maximum response length (words, blank or 0 for unlimited)', 'wordLimit', $this->wordLimit, $this->validateMessages, 8);
		$out .= $this->checkboxInput('This is a generic question to be made available in all my sessions.', 'multiuse', $this->multiuse, $this->validateMessages);
		$out .= $this->checkboxInput('This is a psudo-anonymous question where the teacher will not see who gave each response.', 'anonymous', $this->anonymous, $this->validateMessages);
		$out .= $this->submitInput('submit', 'Create', 'Cancel');
		$out .= $this->formEnd(false);
		return $out;
	}

    function getNewQuestion()
    {
        return new TextQuestion($this->title, $this->displayStem, $this->wordLimit);
    }
}

