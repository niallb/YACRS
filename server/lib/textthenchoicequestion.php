<?php
include_once('wordcloud.php');

$questionTypes['textthenchoice1'] = array('name'=>'Text input with optional MCQ follow-up', 'class'=>'ttcQuestion1', 'edit'=>'editTTCQuestion_form');

class ttcQuestion1 extends questionBase
{
    var $stem;
    var $responseValue;
    var $characterLimit;
    var $wordLimit;

	function __construct($stem, $displayTitleAsStem, $characterLimit, $wordLimit)
	{
        $this->stem = $stem;
        $this->displayStem = (strlen($stem) > 0); // New version, display stem if it exists.
        $this->displayTitle =  $displayTitleAsStem; // Prev displayStem param is now display (instance) title as stem.
        $this->characterLimit = $characterLimit;
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
        $out .= "<label for='R0'>Enter text:</label>";
        $out .= "<input class='text' type='text' name='Ans' id='R0'";
        if($this->characterLimit>0)
        {
            $out .= " maxlength='{$this->characterLimit}'";
        }
        if($this->responseValue!==false)
        {
            $out .= " value='".str_replace("'", "&#39;", htmlspecialchars($this->responseValue))."'";
        }
        if(($this->responseValue !== false)&&(!isset($_REQUEST['doupdate'])))
            $out .= " disabled='1'";
        $out .= '/>';
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
        if(isset($_REQUEST['CCA']))
        {
        	//Create a choice question from selected responses
            $qu = question::retrieve_question($qi->theQuestion_id);
            $opts = array();
            foreach($_REQUEST as $nm=>$val)
            {
                if((substr($nm, 0, 4)=='rid_')&&($val=='1'))
                {
                    $opts[] = intval(substr($nm, 4));
                }
            }
            if(sizeof($opts)>=2)
            {
		        $theQu = new question();
		        $theQu->ownerID = $uinfo['uname'];
 				$theQu->session_id = $thisSession->id;
				$theQu->title = '(Choice) '.$qi->title;
				$theQu->multiuse = false;
                for($n=0; $n<sizeof($opts); $n++)
                {
                	$resp = response::retrieve_response($opts[$n]);
                    if($resp)
                    {
                        $opts[$n] = preg_replace('/\s+/', ' ', trim($resp->value));
                    }
                }
                sort($opts); // Put in alphabetical order to stop most popular always being top.
		        $theQu->definition = new basicQuestion($qi->title, false, implode("\n",$opts));
 	            $theQu->id = $theQu->insert();
		        $thisSession->addQuestion($theQu);
            }
            else
            {
            	$out .= "<p>At least two selections are needed to create a choice question.</p>";
            }
        }
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

	    if($detailed)
	    {
		    $out .= "<p><a href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}'>Summary view</a></p>";
		    $out .= "<table border='1'><thead><tr><th>User</th><th>Name</th><th>Response</th></thead><tbody>";
	        if($responses)
	        {
		        foreach($responses as $r)
		        {
		        	$member = sessionMember::retrieve_sessionMember($r->user_id);
                    if($anonymous)
    			        $out .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>{$r->value}</td></tr>";
                    else
    			        $out .= "<tr><td>{$member->userID}</td><td>{$member->name}</td><td>{$r->value}</td></tr>";
		        }
	        }
		    $out .= "</table>";
        }
	    else
	    {
	        if($responses)
	        {
		    	$out .= "<p><a href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}&display=detail'><b>".sizeof($responses)." response(s).</b></a></p>";
	            //$out .= "<img src='wordwall.php?qiID={$qi->id}'/><br/>";
                $out .= "<div style='clear: both;'></div>";
                $out .=  wordcloud($_REQUEST['qiID'], $responses, '100%', '550px');
	        }
            $out .= "<form id='questionForm' method='POST' action='{$_SERVER['PHP_SELF']}'>";
            $out .= "<input type='hidden' name='sessionID' value='{$_REQUEST['sessionID']}'/>";
            $out .= "<input type='hidden' name='qiID' value='{$_REQUEST['qiID']}'/>";
		    $out .= "<table border='1'><thead><tr><th>Response</th><th>Count</th><th>Select</th></thead><tbody>";
		    foreach($rcounts as $txt=>$count)
		    {
		        $out .= "<tr><td>{$txt}</td><td>{$count['count']}</td><td><input type='checkbox' name='rid_{$count['rid']}' value='1'/></td></tr>";
		    }
		    $out .= "</table>";
            $out .= "<br/><input type='Submit' name='CCA' value='Create a choice question from selected answers.'/>";
            $out .= "</form>";
	    }
        return $out;
    }

    function getDisplayURL($qiID)
    {
        return "chartWrap.php?qiID={$qiID}";
    }


    function getModifiedCopyForm()
    {
        $form = $this::getEditForm();
        $form->characterLimit = $this->characterLimit;
        $form->wordLimit = $this->wordLimit;
        if($this->displayTitle)
            $form->displayStem = true;
        elseif($this->displayStem)
            $form->stem = $this->stem;
        return $form;
    }

    static function questionTypeName() // Used for looking up classes and help files
    {
        return 'textthenchoice1';
    }

    static function getEditForm()
    {
    	$form = new editTTCQuestion_form();
        return $form;
    }
}

function rcount_cmp($a, $b)
{
    if ($a['count'] == $b['count'])
    {
        return 0;
    }
    return ($a['count'] > $b['count']) ? -1 : 1;
}

/*
form editTTCQuestion_form
{
    ajaxaction = "ajax/editQuestion.php";
    hidden sessionID '0';
    hidden id '0';
	static qutype "textthenchoice1";
    string[80] title "Title" {hint="The title is used to identify the question, and can optionally be displayed as the stem. The title can be edited for each question instance";}
    boolean displayStem "Display title as stem to participants." {hint="Check this to display the title to students, the stem is not used if this is checked.";}
    memo[70,4] stem "Stem, optional" {hint="An optional stem for the question.";}
    integer characterLimit "Maximum response length (characters, blank or 0 for unlimited)";
    integer wordLimit "Maximum response length (words, blank or 0 for unlimited)";
    boolean anonymous "This is a pseudo-anonymous question where the teacher will not see who gave each response.";
    boolean multiuse "This is a generic question to be made available for reuse in all my sessions.";
    okcancel "Create" "Cancel";
}
*/

if(!defined('FORM_NOTSUBMITTED'))
{
    define('FORM_NOTSUBMITTED',0);
    define('FORM_SUBMITTED_VALID', 1);
    define('FORM_SUBMITTED_INVALID', 2);
    define('FORM_CANCELED',3);
}

define('editTTCQuestion_form_magic', md5('editTTCQuestion_form'));

//
function show_editTTCQuestion_form($sessionID, $id, $title, $displayStem, $stem, $characterLimit, $wordLimit, $anonymous, $multiuse, $validateMessages=array())
{
    $out = '<form id="editTTCQuestion_form" action="ajax/editQuestion.php" method="POST" class="form-horizontal" onsubmit="return false;">';
    $out .= '<input type="hidden" name="editTTCQuestion_form_code" value="'.editTTCQuestion_form_magic.'"/>';

    $out .= '<input type="hidden" name="sessionID" value="'.$sessionID.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="id" value="'.$id.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="qutype" value="textthenchoice1"';
    $out .= "/>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="title">Title';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="The title is used to identify the question, and can optionally be displayed as the stem. The title can be edited for each question instance"
                     data-html="true"><span aria-hidden="true" title="Help with Title" aria-label="Help with Title" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['title']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['title'].'</span>';
    $out .= '</label>';
    $out .= '<div class="col-sm-8"><input class="form-control" type="text" name="title" id="title" value="'.$title.'" size="80"';
    $out .= "/></div></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="displayStem" id="displayStem" value="1" onclick="disableInput(this.checked, \'stem\');"';
    if($displayStem)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="displayStem">Display title as stem to participants.';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="Check this to display the title to students, the stem is not used if this is checked."
                     data-html="true"><span aria-hidden="true" title="Help with Display title as stem to participants." aria-label="Help with Display title as stem to participants." class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['displayStem']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['displayStem'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="stem">Stem, optional';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="An optional stem for the question."
                     data-html="true"><span aria-hidden="true" title="Help with Stem, optional" aria-label="Help with Stem, optional" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['stem']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['stem'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><textarea class="form-control" name="stem" id="stem" cols="70" rows="4"';
    if($displayStem)
        $out .= ' disabled="1"';
    $out .= '>';
    $out .= htmlentities($stem);
    $out .= "</textarea></span></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="characterLimit">Maximum response length (characters, blank or 0 for unlimited)';
    if(isset($validateMessages['characterLimit']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['characterLimit'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="characterLimit" id="characterLimit" value="'.$characterLimit.'" pattern="\d*" title="An integer value" size="8"';
    $out .= "/></span></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="wordLimit">Maximum response length (words, blank or 0 for unlimited)';
    if(isset($validateMessages['wordLimit']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['wordLimit'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><input type="text" name="wordLimit" id="wordLimit" value="'.$wordLimit.'" pattern="\d*" title="An integer value" size="8"';
    $out .= "/></span></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="anonymous" id="anonymous" value="1"';
    if($anonymous)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="anonymous">This is a pseudo-anonymous question where the teacher will not see who gave each response.';
    if(isset($validateMessages['anonymous']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['anonymous'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-check row">';
    $out .= '<div class="col-sm-8 offset-sm-4">';
    $out .= '<input class="form-check-input" type="checkbox" name="multiuse" id="multiuse" value="1"';
    if($multiuse)
        $out .= ' checked="1"';
    $out .= '/>';
    $out .= '<label for="multiuse">This is a generic question to be made available for reuse in all my sessions.';
    if(isset($validateMessages['multiuse']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['multiuse'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<span class="col-sm-4 control-label">&nbsp;</span>';
    $out .= '<div class="col-sm-8">';
   $out .= '<input class="submit btn btn-success" name="editTTCQuestion_form_submit" type="submit" value="Create" onclick=\'submitForm("editTTCQuestion_form", this);\' />';
    $out .= '<input class="submit btn btn-secondary" name="editTTCQuestion_form_cancel" type="submit" value="Cancel" onclick=\'submitForm("editTTCQuestion_form", this);\' />';
    $out .= "</div></div>";

    $out .= '</form>';
    return $out;
}

function editTTCQuestion_form_submitted()
{
    if((isset($_REQUEST['editTTCQuestion_form_code']))&&($_REQUEST['editTTCQuestion_form_code']==editTTCQuestion_form_magic))
        return true;
    else
        return false;
}

function update_from_editTTCQuestion_form(&$sessionID, &$id, &$title, &$displayStem, &$stem, &$characterLimit, &$wordLimit, &$anonymous, &$multiuse)
{
    if((isset($_REQUEST['editTTCQuestion_form_code']))&&($_REQUEST['editTTCQuestion_form_code']==editTTCQuestion_form_magic))
    {
        if(isset($_REQUEST['editTTCQuestion_form_cancel']))
            return false;
        $sessionID = strval($_REQUEST['sessionID']);
        $id = strval($_REQUEST['id']);
        $title = strval($_REQUEST['title']);
        $displayStem = (isset($_REQUEST['displayStem'])&&(intval($_REQUEST['displayStem'])>0));
        $stem = strval($_REQUEST['stem']);
        $characterLimit = intval($_REQUEST['characterLimit']);
        $wordLimit = intval($_REQUEST['wordLimit']);
        $anonymous = (isset($_REQUEST['anonymous'])&&(intval($_REQUEST['anonymous'])>0));
        $multiuse = (isset($_REQUEST['multiuse'])&&(intval($_REQUEST['multiuse'])>0));
        return true;
    }
    else
    {
        return false;
    }
}

//Wrapper class for editTTCQuestion_form QuickForm functions that emulates a form_lib2::nbform derived class.
class editTTCQuestion_form
{
	var $sessionID; //hidden
	var $id; //hidden
	var $title; //string
	var $displayStem; //boolean
    var $stem; //memo (multiline string)
	var $characterLimit; //integer
	var $wordLimit; //integer
    var $anonymous; //boolean
	var $multiuse; //boolean
	var $validateMessages;

	function __construct($readform=true)
	{
		$this->validateMessages = array();
		if(editTTCQuestion_form_submitted())
		{
			$this->readAndValidate();
		}
        else
        {
   			$this->formStatus = FORM_NOTSUBMITTED;
        }
        }

    function getStatus()
    {
        return $this->formStatus;
	}

	function setData($data)
	{
		$this->sessionID = $data->sessionID;
		$this->id = $data->id;
		$this->title = $data->title;
		$this->displayStem = $data->displayStem;
        $this->stem = $data->stem;
		$this->characterLimit = $data->characterLimit;
		$this->wordLimit = $data->wordLimit;
        $this->anonymous = $data->anonymous;
		$this->multiuse = $data->multiuse;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->id = $this->id;
        $data->qutype = "textthenchoice1";
		$data->title = $this->title;
		$data->displayStem = $this->displayStem;
        $data->stem = $this->stem;
		$data->characterLimit = $this->characterLimit;
		$data->wordLimit = $this->wordLimit;
        $data->anonymous = $this->anonymous;
		$data->multiuse = $this->multiuse;
		return $data;
	}

	private function readAndValidate()
	{
		if(update_from_editTTCQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->characterLimit, $this->wordLimit, $this->anonymous, $this->multiuse))
		{
			$isValid = $this->validate();
			if($isValid)
				$this->formStatus = FORM_SUBMITTED_VALID;
			else
				$this->formStatus = FORM_SUBMITTED_INVALID;
		}
		else
        {
				$this->formStatus = FORM_CANCELED;
        }
	}

	private function validate()
	{
		$this->validateMessages = array();
//USERCODE-SECTION-editTTCQuestion_form-sessionID-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-sessionID-validation

//USERCODE-SECTION-editTTCQuestion_form-id-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-id-validation

//USERCODE-SECTION-editTTCQuestion_form-title-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-title-validation

//USERCODE-SECTION-editTTCQuestion_form-displayStem-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-displayStem-validation

//USERCODE-SECTION-editTTCQuestion_form-stem-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-stem-validation

//USERCODE-SECTION-editTTCQuestion_form-characterLimit-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-characterLimit-validation

//USERCODE-SECTION-editTTCQuestion_form-wordLimit-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-wordLimit-validation

//USERCODE-SECTION-editTTCQuestion_form-anonymous-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-anonymous-validation

//USERCODE-SECTION-editTTCQuestion_form-multiuse-validation
// Put code here.
//ENDUSERCODE-SECTION-editTTCQuestion_form-multiuse-validation

		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
        $out = show_editTTCQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->characterLimit, $this->wordLimit, $this->anonymous, $this->multiuse, $this->validateMessages);
		return $out;
	}

    function getNewQuestion()
    {
        if($this->displayStem) // displayStem really means display (instance) title as stem now
            $this->stem = '';
        return new  ttcQuestion1($this->stem, $this->displayStem, $this->characterLimit, $this->wordLimit);
    }
}

