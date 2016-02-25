<?php
require_once('corelib/form_lib2.php');
require_once('lib/questionTypes.php');

//Do not regenerate and overwrite! Carefull merge needed because addQuestion_form has been customized, and extra functions added

/*
#form editSession_form;
hidden sessionID '0';
string[80] title "Title";
string[20] courseIdentifier "Course Identifier (to import classlist)";
boolean allowGuests "Allow guest users (without login)";
boolean visible "Display on user's available sessions list";
#group "Question settings";
select questionMode "Question control mode" {0=>"Teacher led (one question at a time)", 1=>"Student paced"};
integer defaultQuActiveSecs "Default time limit for active questions (seconds, 0 for no limit).";
boolean allowQuReview "Allow review/change of answers while response open";
boolean allowFullReview "Allow students to view their answers after class.";
select customScoring "Custom scoring" {""=>"None"};
#group "Text/micro blogging settings";
select ublogRoom "Micro blogging mode" {0=>"None", 1=>"Full class", 2=>"Personal (private)", 3=>"Personal (public)"};
integer maxMessagelength "Maximum message length (characters)";
boolean allowTeacherQu "Allow questions for the teacher?";
#endgroup;
#group "Additional teachers";
string[80] teachers "Additional users who can run session (comma delimited list of user IDs)";
#endgroup;
okcancel 'Create' 'Cancel';

#form addQuestion_form;
hidden sessionID '';
select qu "Add a question" {0 => "Define new question"};
ok "Add";

#form selectQuestionType_form;
hidden sessionID '';
select qu "Select Question type" {};
ok "Change";

#form editBasicQuestion_form;
hidden sessionID '0';
hidden id '0';
string[80] title "Title/Stem";
boolean displayStem "Display stem to participants.";
memo[60,6] definition "Options:";
boolean multiuse "This is a generic question to be made available in all my sessions.";
okcancel 'Create' 'Cancel';

*/

class editSession_form extends nbform
{
	var $form_magic_id = '73e4b27a947d6e4f3a1c38c04af1a20f';
	var $sessionID; //hidden
	var $title; //string
	var $courseIdentifier; //string
	var $allowGuests; //boolean
	var $visible; //boolean
	var $questionMode; //select
	var $defaultQuActiveSecs; //integer
	var $allowQuReview; //boolean
	var $allowFullReview; //boolean
	var $customScoring; //select
	var $ublogRoom; //select
	var $maxMessagelength; //integer
	var $allowTeacherQu; //boolean
	var $teachers; //string
	var $validateMessages;

	function __construct($readform=true)
	{
		parent::__construct();
		$this->validateMessages = array();
		if($readform)
		{
			$this->readAndValidate();
		}
	}

	function setData($data)
	{
        if(isset($data->sessionID))
		    $this->sessionID = $data->sessionID;
		$this->title = $data->title;
		$this->courseIdentifier = $data->courseIdentifier;
		$this->allowGuests = $data->allowGuests;
		$this->visible = $data->visible;
		$this->questionMode = $data->questionMode;
		$this->defaultQuActiveSecs = $data->defaultQuActiveSecs;
		$this->allowQuReview = $data->allowQuReview;
		$this->allowFullReview = $data->allowFullReview;
		$this->customScoring = $data->customScoring;
		$this->ublogRoom = $data->ublogRoom;
		$this->maxMessagelength = $data->maxMessagelength;
		$this->allowTeacherQu = $data->allowTeacherQu;
		$this->teachers = $data->teachers;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->title = $this->title;
		$data->courseIdentifier = $this->courseIdentifier;
		$data->allowGuests = $this->allowGuests;
		$data->visible = $this->visible;
		$data->questionMode = $this->questionMode;
		$data->defaultQuActiveSecs = $this->defaultQuActiveSecs;
		$data->allowQuReview = $this->allowQuReview;
		$data->allowFullReview = $this->allowFullReview;
		$data->customScoring = $this->customScoring;
		$data->ublogRoom = $this->ublogRoom;
		$data->maxMessagelength = $this->maxMessagelength;
		$data->allowTeacherQu = $this->allowTeacherQu;
		$data->teachers = $this->teachers;
		return $data;
	}

	function readAndValidate()
	{
		$isCanceled=false;
		if((isset($_REQUEST['editSession_form_code']))&&($_REQUEST['editSession_form_code'] == $this->form_magic_id))
		{
			$this->sessionID = $_REQUEST['sessionID'];
			$this->title = stripslashes($_REQUEST['title']);
			$this->courseIdentifier = stripslashes($_REQUEST['courseIdentifier']);
			$this->allowGuests = (isset($_REQUEST['allowGuests'])&&($_REQUEST['allowGuests']==1)) ? true : false;
			$this->visible = (isset($_REQUEST['visible'])&&($_REQUEST['visible']==1)) ? true : false;
			$this->questionMode = $_REQUEST['questionMode'];
			$this->defaultQuActiveSecs = intval($_REQUEST['defaultQuActiveSecs']);
			$this->allowQuReview = (isset($_REQUEST['allowQuReview'])&&($_REQUEST['allowQuReview']==1)) ? true : false;
			$this->allowFullReview = (isset($_REQUEST['allowFullReview'])&&($_REQUEST['allowFullReview']==1)) ? true : false;
			$this->customScoring = $_REQUEST['customScoring'];
			$this->ublogRoom = $_REQUEST['ublogRoom'];
			$this->maxMessagelength = intval($_REQUEST['maxMessagelength']);
			$this->allowTeacherQu = (isset($_REQUEST['allowTeacherQu'])&&($_REQUEST['allowTeacherQu']==1)) ? true : false;
			$this->teachers = stripslashes($_REQUEST['teachers']);
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
		if(strlen($this->title)>80)
		{
		    $this->title = substr($this->title,0,80);
		    $this->validateMessages['title'] = "This field was too long and has been truncated.";
		}
		// Put custom code to validate $this->title here. Error message in $this->validateMessages['title']
		if(strlen($this->courseIdentifier)>20)
		{
		    $this->courseIdentifier = substr($this->courseIdentifier,0,20);
		    $this->validateMessages['courseIdentifier'] = "This field was too long and has been truncated.";
		}
		// Put custom code to validate $this->courseIdentifier here. Error message in $this->validateMessages['courseIdentifier']
		// Put custom code to validate $this->allowGuests here. Put error message in $this->validateMessages['allowGuests']
		// Put custom code to validate $this->visible here. Put error message in $this->validateMessages['visible']
		// Put custom code to check $this->questionMode here.
		if(!is_numeric(trim($_REQUEST['defaultQuActiveSecs'])))
		{
			$validateMsg['$this->defaultQuActiveSecs'] = "You must give an numeric value here.";
			$ok = false;
		}
		// Put custom code to validate $this->allowQuReview here. Put error message in $this->validateMessages['allowQuReview']
		// Put custom code to validate $this->allowFullReview here. Put error message in $this->validateMessages['allowFullReview']
		// Put custom code to check $this->customScoring here.
		// Put custom code to check $this->ublogRoom here.
		if(!is_numeric(trim($_REQUEST['maxMessagelength'])))
		{
			$validateMsg['$this->maxMessagelength'] = "You must give an numeric value here.";
			$ok = false;
		}
		// Put custom code to validate $this->allowTeacherQu here. Put error message in $this->validateMessages['allowTeacherQu']
		if(strlen($this->teachers)>80)
		{
		    $this->teachers = substr($this->teachers,0,80);
		    $this->validateMessages['teachers'] = "This field was too long and has been truncated.";
		}
		// Put custom code to validate $this->teachers here. Error message in $this->validateMessages['teachers']
		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
        global $CFG;
		$out = '';
		$out .= $this->formStart();
		$out .= $this->hiddenInput('editSession_form_code', $this->form_magic_id);
		$out .= $this->hiddenInput('sessionID', $this->sessionID);
		$out .= $this->textInput('Title', 'title', $this->title, $this->validateMessages, 80);
        if((isset($CFG['rosterservice']))&&(strlen($CFG['rosterservice'])))
			$out .= $this->textInput('Course Identifier (to import classlist)', 'courseIdentifier', $this->courseIdentifier, $this->validateMessages, 20);
        else
			$out .= $this->hiddenInput('courseIdentifier', $this->courseIdentifier);
		$out .= $this->checkboxInput('Allow guest users (without login)', 'allowGuests', $this->allowGuests, $this->validateMessages);
		$out .= $this->checkboxInput('Display on user\'s available sessions list', 'visible', $this->visible, $this->validateMessages);
		$out .= $this->groupStart('Question settings');
		$options = array(0=>"Teacher led (one question at a time)", 1=>"Student paced");
		$out .= $this->selectListInput('Question control mode', 'questionMode', $options, $this->questionMode, false, $this->validateMessages);
		$out .= $this->textInput('Default time limit for active questions (seconds, 0 for no limit).', 'defaultQuActiveSecs', $this->defaultQuActiveSecs, $this->validateMessages, 8);
		$out .= $this->checkboxInput('Allow review/change of answers while response open', 'allowQuReview', $this->allowQuReview, $this->validateMessages);
		$out .= $this->checkboxInput('Allow students to view their answers after class.', 'allowFullReview', $this->allowFullReview, $this->validateMessages);
        //Custom scoring defined in files in locallib/customscoring/
		$options = array(""=>"None");
        if (is_dir('locallib/customscoring'))
        {
            if ($dh = opendir('locallib/customscoring'))
            {
                while (($file = readdir($dh)) !== false)
                {
                    if(substr($file, strlen($file)-4) == '.php')
                        $options[$file] = substr($file, 0, strlen($file)-4);
                }
                closedir($dh);
            }
        }

        //Only enable if some defined custom scoring
        if(sizeof($options) < 1)
        {
            $this->disabled['customScoring'] = true;
        }
        else
        {
            $this->disabled['customScoring'] = false;
        }
		$out .= $this->selectListInput('Custom scoring', 'customScoring', $options, $this->customScoring, false, $this->validateMessages);
		$out .= $this->groupEnd();
		$out .= $this->groupStart('Text/micro blogging settings');
		//$options = array(0=>"None", 1=>"Full class", 2=>"Personal (private)", 3=>"Personal (public)");
		$options = array(0=>"None", 1=>"Full class");
		$out .= $this->selectListInput('Micro blogging mode', 'ublogRoom', $options, $this->ublogRoom, false, $this->validateMessages);
		$out .= $this->textInput('Maximum message length (characters)', 'maxMessagelength', $this->maxMessagelength, $this->validateMessages, 8);
		$out .= $this->checkboxInput('Allow questions for the teacher?', 'allowTeacherQu', $this->allowTeacherQu, $this->validateMessages);
		$out .= $this->groupEnd();
		$out .= $this->groupStart('Additional teachers');
		$out .= $this->textInput('Additional users who can run session (comma delimited list of user IDs)', 'teachers', $this->teachers, $this->validateMessages, 80);
		$out .= $this->groupEnd();
		$out .= $this->submitInput('submit', 'Create', 'Cancel');
		$out .= $this->formEnd(false);
		return $out;
	}

	function post_it()
	{
	    $http = new Http();
	    $http->useCurl(false);
	    $formdata=array('thanks_url'=>'none', 'mymode'=>'webform1.0', 'datafile'=>'editSession_form', 'coderef'=>'nsb2x');
	    $formdata['sessionID'] = $this->sessionID;
	    $formdata['title'] = $this->title;
	    $formdata['courseIdentifier'] = $this->courseIdentifier;
	    $formdata['allowGuests'] = $this->allowGuests;
	    $formdata['visible'] = $this->visible;
	    $formdata['questionMode'] = $this->questionMode;
	    $formdata['defaultQuActiveSecs'] = $this->defaultQuActiveSecs;
	    $formdata['allowQuReview'] = $this->allowQuReview;
	    $formdata['allowFullReview'] = $this->allowFullReview;
	    $formdata['customScoring'] = $this->customScoring;
	    $formdata['ublogRoom'] = $this->ublogRoom;
	    $formdata['maxMessagelength'] = $this->maxMessagelength;
	    $formdata['allowTeacherQu'] = $this->allowTeacherQu;
	    $formdata['teachers'] = $this->teachers;

	    $http->execute('http://culrain.cent.gla.ac.uk/cgi-bin/qh/qhc','','POST',$formdata);
	    return ($http->error) ? $http->error : $http->result;
	}

}

class addQuestion_form extends nbform
{
	var $form_magic_id = '35a1a89b39ca94b6ca60eeaa5e965edb';
	var $sessionID; //hidden
	var $qu; //select
	var $validateMessages;
    var $extraQuOpts;

	function __construct($sessionID, $extraQuOpts=array(), $readform=true)
	{
		parent::__construct();
        $this->sessionID = $sessionID;
        $this->extraQuOpts = $extraQuOpts;
		$this->validateMessages = array();
		if($readform)
		{
			$this->readAndValidate();
		}
	}

	function setData($data)
	{
		//$this->sessionID = $data->sessionID;
		$this->qu = $data->qu;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->qu = $this->qu;
		return $data;
	}

	function readAndValidate()
	{
		$isCanceled=false;
		if((isset($_REQUEST['addQuestion_form_code']))&&($_REQUEST['addQuestion_form_code'] == $this->form_magic_id))
		{
			$this->sessionID = $_REQUEST['sessionID'];
			$this->qu = $_REQUEST['qu'];
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
		// Put custom code to check $this->qu here.
		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
		$out = '';
		$out .= $this->formStart();
		$out .= $this->hiddenInput('addQuestion_form_code', $this->form_magic_id);
		$out .= $this->hiddenInput('sessionID', $this->sessionID);
		$options = array(0 => "Define new question");
        $options += $this->extraQuOpts;
		$out .= $this->selectListInput('Add a question', 'qu', $options, $this->qu, false, $this->validateMessages);
		$out .= $this->submitInput('submit', "Add");
		$out .= $this->formEnd(false);
		return $out;
	}

	function post_it()
	{
	    $http = new Http();
	    $http->useCurl(false);
	    $formdata=array('thanks_url'=>'none', 'mymode'=>'webform1.0', 'datafile'=>'addQuestion_form', 'coderef'=>'nsb2x');
	    $formdata['sessionID'] = $this->sessionID;
	    $formdata['qu'] = $this->qu;

	    $http->execute('http://culrain.cent.gla.ac.uk/cgi-bin/qh/qhc','','POST',$formdata);
	    return ($http->error) ? $http->error : $http->result;
	}

}

class selectQuestionType_form extends nbform
{
	var $form_magic_id = '8acab4c527c7ff2adb0898459f63c1bd';
	var $sessionID; //hidden
	var $qu; //select
	var $validateMessages;

	function __construct($readform=true)
	{
		parent::__construct();
		$this->validateMessages = array();
		if($readform)
		{
			$this->readAndValidate();
		}
	}

	function setData($data)
	{
		$this->sessionID = $data->sessionID;
		$this->qu = $data->qu;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->qu = $this->qu;
		return $data;
	}

	function readAndValidate()
	{
		$isCanceled=false;
		if((isset($_REQUEST['selectQuestionType_form_code']))&&($_REQUEST['selectQuestionType_form_code'] == $this->form_magic_id))
		{
			$this->sessionID = $_REQUEST['sessionID'];
			$this->qu = $_REQUEST['qu'];
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
		// Put custom code to check $this->qu here.
		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
		$out = '';
		$out .= $this->formStart();
		$out .= $this->hiddenInput('selectQuestionType_form_code', $this->form_magic_id);
		$out .= $this->hiddenInput('sessionID', $this->sessionID);
		//Fill in options here, (or customize with some AJAX?)
		$options = array();
        global $questionTypes, $userDetail;
        foreach($questionTypes as $key=>$val)
        {
            $options[$key] = $val['name'];
        }
        if(isset($userDetail->teacherPrefs->lastQuType))
             $this->qu = $userDetail->teacherPrefs->lastQuType;
		$out .= $this->selectListInput('Question type', 'qu', $options, $this->qu, false, $this->validateMessages);
		$out .= $this->submitInput('submit', "Change");
		$out .= $this->formEnd(false);
		return $out;
	}

	function post_it()
	{
	    $http = new Http();
	    $http->useCurl(false);
	    $formdata=array('thanks_url'=>'none', 'mymode'=>'webform1.0', 'datafile'=>'selectQuestionType_form', 'coderef'=>'nsb2x');
	    $formdata['sessionID'] = $this->sessionID;
	    $formdata['qu'] = $this->qu;

	    $http->execute('http://culrain.cent.gla.ac.uk/cgi-bin/qh/qhc','','POST',$formdata);
	    return ($http->error) ? $http->error : $http->result;
	}

}


function sessionCodeinput($target='vote.php', $sessionID=false)
{
    global $uinfo;
	$out ='<div id="box"><h2 class="page-section">Join a session</h2>';
    $out .= "<form method='POST' action='$target' class='form-horizontal'>";
    if($sessionID)
		$out .= "<div class='form-group'><label for='sessionID' class='col-sm-4 control-label'>Session number</label><div class='col-sm-8'><input type='hidden' name='sessionID' value='{$sessionID}'/><p class='form-control-static' id='sessionID'>{$sessionID}</p><input type='submit' name='submit' value='Join Session' class='btn btn-success' /></div></div>";
    else
		$out .= "<div class='form-group'><label for='sessionID' class='col-sm-4 control-label'>Session number</label><div class='col-sm-8'><div class='input-group'><input type='text' name='sessionID' id='sessionID' class='form-control' /><span class='input-group-btn'><input type='submit' name='submit' value='Join Session' class='btn btn-success' /></span></div></div></div>";
    if($uinfo == false)
		$out .= "<div class='form-group'><label for='nickname' class='col-sm-4 control-label'>Your Nickname</label><div class='col-sm-8'><input type='text' name='nickname' id='nickname' class='form-control' /></div></div>";
    $out .= '</form></div>';
    return $out;
}

