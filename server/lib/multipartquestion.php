<?php

$questionTypes['multiPartQuestion'] = array('name'=>'Multi-part question (e.g. pre/post test)', 'class'=>'multiPartQuestion', 'edit'=>'editMultiPartQuestion_form');

class multiPartQuestion extends questionBase
{
    var $title;
    var $subQus;
    var $source;
    var $displayTitle;
    var $partialResponse;

	function __construct($title, $displayTitle, $options)
	{
        $this->subQus = $this->parse($options);
        $this->source = $options;
        $this->title = $title;
        $this->displayTitle = $displayTitle;
        $this->partialResponse = true;
    }

    function checkResponse($qiID, $resp, $SMSResp=false)
    {
        if($resp == false)
        {
	        $this->responseValue = false;
	        $this->responses = array();
        }
        else
        {
            $this->responseValue = $resp->value;
            $this->responses = unserialize($resp->value);
        }
        if(isset($_REQUEST['subqu']))
        {
            $subqu = $_REQUEST['subqu'];
	        if((isset($_REQUEST['submitans']))&&($_REQUEST['qiID']==$qiID))
	        {
	            $this->responses[$subqu] = '';
		        if($this->subQus[$index]['correctcount'] <= 1)
		        {
	                if(isset($_REQUEST['Ans']))
	                    $this->responses[$subqu] = $_REQUEST['Ans'];
		        }
		        else
		        {
			        $onum = 0;
			        foreach($this->subQus[$index]['options'] as $o)
			        {
	                    $onum++;
	                	$k = "R$onum";
	                    if((isset($_REQUEST[$k]))&&($_REQUEST[$k]==$k))
	                         $this->responses[$subqu] .= $k.',';
	                }
	                $this->responses[$subqu] = trim($this->responses[$subqu],',');
	            }
	        }
            $this->responseValue = serialize($this->responses);
            if(sizeof($this->responses)==sizeof($this->subQus))
            {
            	$this->partialResponse = false;
            }
            else
            {
            	$this->partialResponse = true;
            }
            //echo "<h1>".sizeof($this->responses)." ".sizeof($this->subQus).' '.$this->partialResponse.'</h1>';
            if($resp !== false)
            {
                $resp->value = $this->responseValue;
                $resp->isPartial = $this->partialResponse;
                $resp->update();
            }
        }

        return false;
    }

    function allowReview()
    {
        return false;
    }

    function render($title='')
    {
        $out = '';
        //$out .= '<pre>'.print_r($this,1).'</pre>';
        if($this->responses == false)
        {
        	$CurSubQu = 0;
        }
        else
        {
            $CurSubQu = sizeof($this->responses);
        }
	    if($this->displayTitle)
	        $out .= "<p class='stem'>{$title}</p>";
        if($CurSubQu < sizeof($this->subQus))
        {
        	$out .= $this->renderSubQu($CurSubQu);
        }
        else
        {
            $out .= "You have completed this set of questions.";
        }
        //$out .= '<pre>'.print_r($this,1).'</pre>';
        return $out;
    }

    function renderSubQu($index)
    {
        $out = '<fieldset>';
        $out = '<legend>Part '.($index+1).' of '.sizeof($this->subQus).'</legend>';
        $out .= "<p class='stem'>{$this->subQus[$index]['stem']}</p>";
        $out .= "<input type='hidden' name='subqu' value='$index'/>";
        if($this->subQus[$index]['type'] == "choice")
        {
	        $out .= "<div class='wide buttonlist'>";
	        $onum = 0;
	        foreach($this->subQus[$index]['options'] as $o)
	        {
	            $onum++;
	            $out .= "<label for='R$onum'>";

	            if($this->subQus[$index]['correctcount'] <=1)
	            {
	                $out .= "<input class='radio' type='radio' name='Ans' id='R$onum' value='R$onum'";
                    if(isset($this->responses[$index]))
                    {
		                if($this->responses[$index] == "R$onum")
		                    $out .= " checked='1'";
		                if(strlen($this->responses[$index]))
		                    $out .= " disabled='1'";
                    }
	                $out .= "/>&nbsp;";
	            }
	            else
	            {
	                $out .= "<input class='radio' type='checkbox' name='R$onum' id='R$onum' value='R$onum'";
                    if(isset($this->responses[$index]))
                    {
	                    $out .= " disabled='1'";
		                if(strpos($this->responses[$index],"R$onum")!==false)
		                    $out .= " checked='1'";
	                }
	                $out .= "/>&nbsp;";
	            }
	            $out .= $o['opt'];
	            $out .= "</label>";

	        }
	        $out .= '</div>';
        }
        elseif($this->subQus[$index]['type'] == "text")
        {
	        $out .= "<input class='textbox' type='text' name='Ans' id='R$onum' ";
            if(isset($this->responses[$index]))
            {
		        $out .= " value='{$this->responses[$index]}'";
		        $out .= " disabled='disabled'";
            }
            $out .= '/>';
        }
        else
        {
        	$out = "Can not render {$this->subQus[$index]['type']} ($index) yet";
        }
        $out .= "<div class='submit'><input type='submit' name='submitans' value='Submit answer'/></div>";
	    $out .= '</fieldset>';
	    return $out;
    }

    function getGraphLabels()
    {
        return false;
    }

    function report($thisSession, $qi, $detailed = false)
    {
	    $responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
        $qu = question::retrieve_question($qi->theQuestion_id);
        //return '<pre>'.print_r($qu,1).'</pre>';
		$out .= "<p><a href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}&display=detail'><b>".sizeof($responses)." response(s).</b></a></p>";
		$out .= "<table border='1'><thead><tr><th>User</th><th>Name</th>";
        for($n=1; $n<=sizeof($qu->definition->subQus); $n++)
            $out .= "<th>Part $n</th>";
        $out .= "<th>Score</th></thead><tbody>";
	    if($responses)
	    {
		    foreach($responses as $r)
		    {
                $pr = unserialize($r->value);
                $score = 0;
		    	$member = sessionMember::retrieve_sessionMember($r->user_id);
		        $out .= "<tr><td>{$member->userID}</td><td>{$member->name}</td>";
                for($n=0; $n<sizeof($qu->definition->subQus); $n++)
                {
                    if($qu->definition->subQus[$n]['type']=='choice')
                    {
                        $sels = explode(',',$pr[$n]);
	                    foreach($sels as $s)
	                    {
	                    	$s = intval(substr($s,1))-1;
                            if($qu->definition->subQus[$n]['options'][$s]['correct'])
                                $score++;
	                    }
                    }
                    elseif($qu->definition->subQus[$n]['type']=='text')
                    {
                        $submitted = strtolower(trim(preg_replace('/\s+/',' ',$pr[$n])));
                        if(in_array($submitted, $qu->definition->subQus[$n]['correct']))
                            $score++;
                    }
                    $out .= "<td>{$pr[$n]}</td>";
                }
                $out .= "<td>$score</td></tr>";

		    }
	    }
		$out .= "</table>";
        return $out;
    }


	function parse($src)
	{
	   $qus = array();
	   $cq = false;
	   $cqtype = false;
	   $lines = explode("\n", $src);
	   foreach($lines as $l)
	   {
	       $l = trim($l);
	       if(preg_match('/#(\w+)\s+(.*)/',$l, $matches))
	       {
	           if($cq)
	           {
	               $qus[] = $cq;
	               $cq = false;
	           }
	           $cqtype = $matches[1];
	           switch($cqtype)
	           {
	               case 'choice':
	                   $cq = array('type'=>$cqtype, 'stem'=>$matches[2], 'options'=>array(), 'correctcount'=>0);
	                   break;
	               case 'text':
	                   $cq = array('type'=>$cqtype, 'stem'=>$matches[2], 'correct'=>array());
	                   break;
	               default:
	                   $cq = false;
	                   break;
	           }
	       }
	       elseif(strlen($l))
	       {
	           switch($cqtype)
	           {
	                case 'choice':
	                    if(preg_match('/(\*\s*)?(.*)/', $l, $matches))
	                    {
	                        if(strlen($matches[1]))
	                        {
	                            $cq['options'][] = array('opt'=>$matches[2], 'correct'=>true);
	                            $cq['correctcount']++;
	                        }
	                        else
	                        {
	                            $cq['options'][] = array('opt'=>$matches[2], 'correct'=>false);
	                        }
	                    }
	                    break;
	                case 'text':
	                    $parts = explode(';',$l);
	                    foreach($parts as $p)
	                    {
	                        if(strlen(trim($p)))
	                            $cq['correct'][] = strtolower(trim(preg_replace('/\s+/',' ',$p)));
	                    }
	                    break;
	                default:
	                    break;
	            }
	        }
	    }
	    if($cq)
	       $qus[] = $cq;

	    return $qus;
	}

    static function getEditForm()
    {
    	$form = new editMultiPartQuestion_form();
        return $form;
    }
}

class editMultiPartQuestion_form extends nbform
{
	var $form_magic_id = '56090c1dea408bff57a005fe239b550d';
	var $sessionID; //hidden
	var $id; //hidden
	var $title; //string
	var $displayTitle; //boolean
	var $definition; //memo
	var $multiuse; //boolean
	var $validateMessages;
    static $briefHelp = "<div style='border : 1px solid Blue;margin : 30px;padding: 10px;background-color : #FFFFAA;'><h3>Instructions</h3><p>Still to be written</p></div>";


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
           $this->displayTitle = true;
        }
	}

	function setData($data)
	{
		$this->sessionID = $data->sessionID;
		$this->id = $data->id;
		$this->title = $data->title;
		$this->displayTitle = $data->displayTitle;
		$this->definition = $data->definition;
		$this->multiuse = $data->multiuse;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->id = $this->id;
		$data->title = $this->title;
		$data->displayTitle = $this->displayTitle;
		$data->definition = $this->definition;
		$data->multiuse = $this->multiuse;
		return $data;
	}

	function readAndValidate()
	{
		$isCanceled=false;
		if((isset($_REQUEST['editBasicQuestion_form_code']))&&($_REQUEST['editBasicQuestion_form_code'] == $this->form_magic_id))
		{
			$this->sessionID = $_REQUEST['sessionID'];
			$this->id = $_REQUEST['id'];
			$this->title = stripslashes($_REQUEST['title']);
			$this->displayTitle = (isset($_REQUEST['displayTitle'])&&($_REQUEST['displayTitle']==1)) ? true : false;
			$this->definition = stripslashes($_REQUEST['definition']);
			$this->multiuse = (isset($_REQUEST['multiuse'])&&($_REQUEST['multiuse']==1)) ? true : false;
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
		// Put custom code to validate $this->displayTitle here. Put error message in $this->validateMessages['displayTitle']
		// Put custom code to validate $this->definition here. Put error message in $this->validateMessages['definition']
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
		$out .= $this->hiddenInput('editBasicQuestion_form_code', $this->form_magic_id);
		$out .= $this->hiddenInput('sessionID', $this->sessionID);
		$out .= $this->hiddenInput('id', $this->id);
		$out .= $this->textInput('Test title', 'title', $this->title, $this->validateMessages, 80);
		$out .= $this->checkboxInput('Display title to participants (shown above stem of each question).', 'displayTitle', $this->displayTitle, $this->validateMessages);
		$out .= $this->textareaInput('Definition', 'definition', $this->definition, $this->validateMessages, 60 , 30);
		$out .= $this->checkboxInput('This is a generic question/test to be made available in all my sessions.', 'multiuse', $this->multiuse, $this->validateMessages);
		$out .= $this->submitInput('submit', 'Create', 'Cancel');
		$out .= $this->formEnd(false);
        $out .= editBasicQuestion_form::$briefHelp;
		return $out;
	}

	function post_it()
	{
	    $http = new Http();
	    $http->useCurl(false);
	    $formdata=array('thanks_url'=>'none', 'mymode'=>'webform1.0', 'datafile'=>'editBasicQuestion_form', 'coderef'=>'nsb2x');
	    $formdata['sessionID'] = $this->sessionID;
	    $formdata['id'] = $this->id;
	    $formdata['title'] = $this->title;
	    $formdata['displayTitle'] = $this->displayTitle;
	    $formdata['definition'] = $this->definition;
	    $formdata['multiuse'] = $this->multiuse;

	    $http->execute('http://culrain.cent.gla.ac.uk/cgi-bin/qh/qhc','','POST',$formdata);
	    return ($http->error) ? $http->error : $http->result;
	}

    function getNewQuestion()
    {
        return new multiPartQuestion($this->title, $this->displayTitle, $this->definition);
    }
}

