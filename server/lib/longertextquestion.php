<?php
include_once('wordcloud.php');

$questionTypes['text'] = array('name'=>'Text input suitable for 2 or three sentences.', 'class'=>'TextQuestion', 'edit'=>'editTextQuestion_form');

class TextQuestion extends questionBase
{
    var $stem;
    var $responseValue;
    var $wordLimit;

	function __construct($stem, $displayTitleAsStem, $wordLimit)
	{
        $this->stem = $stem;
        $this->displayStem = (strlen($stem) > 0); // New version, display stem if it exists.
        $this->displayTitle =  $displayTitleAsStem; // Prev displayStem param is now display (instance) title as stem.
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
        $out .= "<h2 class='page-section'>".$qi->title."<span class='pull-right'><a href='textAnalysis.php?sessionID={$thisSession->id}&qiID={$qi->id}' target='_new'>Analyse (opens in new window)</a></h2>";
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
	            //$out .= "<img src='wordwall.php?qiID={$qi->id}'/><br/>";
                $out .= "<div style='clear: both;'></div>";
                $out .=  wordcloud($_REQUEST['qiID'], $responses, '100%', '550px');
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

    function getModifiedCopyForm()
    {
        $form = $this::getEditForm();
        $form->wordLimit = $this->wordLimit;
        if($this->displayTitle)
            $form->displayStem = true;
        elseif($this->displayStem)
            $form->stem = $this->stem;
        return $form;
    }

    static function questionTypeName() // Used for looking up classes and help files
    {
        return 'text';
    }

    static function getEditForm()
    {
    	$form = new editTextQuestion_form();
        return $form;
    }
}


/*
form editTextQuestion_form
{
    ajaxaction = "ajax/editQuestion.php";
    hidden sessionID '0';
    hidden id '0';
	static qutype "text";
    string[80] title "Title" {hint="The title is used to identify the question, and can optionally be displayed as the stem. The title can be edited for each question instance";}
    boolean displayStem "Display title as stem to participants." {hint="Check this to display the title to students, the stem is not used if this is checked.";}
    memo[70,4] stem "Stem, optional" {hint="An optional stem for the question.";}
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

define('editTextQuestion_form_magic', md5('editTextQuestion_form'));

//
function show_editTextQuestion_form($sessionID, $id, $title, $displayStem, $stem, $wordLimit, $anonymous, $multiuse, $validateMessages=array())
{
    $out = '<form id="editTextQuestion_form" action="ajax/editQuestion.php" method="POST" class="form-horizontal" onsubmit="return false;">';
    $out .= '<input type="hidden" name="editTextQuestion_form_code" value="'.editTextQuestion_form_magic.'"/>';

    $out .= '<input type="hidden" name="sessionID" value="'.$sessionID.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="id" value="'.$id.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="qutype" value="text"';
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
   $out .= '<input class="submit btn btn-success" name="editTextQuestion_form_submit" type="submit" value="Create" onclick=\'submitForm("editTextQuestion_form", this);\' />';
    $out .= '<input class="submit btn btn-secondary" name="editTextQuestion_form_cancel" type="submit" value="Cancel" onclick=\'submitForm("editTextQuestion_form", this);\' />';
    $out .= "</div></div>";

    $out .= '</form>';
    return $out;
}

function editTextQuestion_form_submitted()
{
    if((isset($_REQUEST['editTextQuestion_form_code']))&&($_REQUEST['editTextQuestion_form_code']==editTextQuestion_form_magic))
        return true;
    else
        return false;
}

function update_from_editTextQuestion_form(&$sessionID, &$id, &$title, &$displayStem, &$stem, &$wordLimit, &$anonymous, &$multiuse)
{
    if((isset($_REQUEST['editTextQuestion_form_code']))&&($_REQUEST['editTextQuestion_form_code']==editTextQuestion_form_magic))
    {
        if(isset($_REQUEST['editTextQuestion_form_cancel']))
            return false;
        $sessionID = strval($_REQUEST['sessionID']);
        $id = strval($_REQUEST['id']);
        $title = strval($_REQUEST['title']);
        $displayStem = (isset($_REQUEST['displayStem'])&&(intval($_REQUEST['displayStem'])>0));
        $stem = strval($_REQUEST['stem']);
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

//Wrapper class for editTextQuestion_form QuickForm functions that emulates a form_lib2::nbform derived class.
class editTextQuestion_form
{
	var $sessionID; //hidden
	var $id; //hidden
	var $title; //string
	var $displayStem; //boolean
    var $stem; //memo (multiline string)
	var $wordLimit; //integer
    var $anonymous; //boolean
	var $multiuse; //boolean
	var $validateMessages;

	function __construct($readform=true)
	{
		$this->validateMessages = array();
		if(editTextQuestion_form_submitted())
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
		$this->wordLimit = $data->wordLimit;
        $this->anonymous = $data->anonymous;
		$this->multiuse = $data->multiuse;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->id = $this->id;
        $data->qutype = "text";
		$data->title = $this->title;
		$data->displayStem = $this->displayStem;
        $data->stem = $this->stem;
		$data->wordLimit = $this->wordLimit;
        $data->anonymous = $this->anonymous;
		$data->multiuse = $this->multiuse;
		return $data;
	}

	private function readAndValidate()
	{
		if(update_from_editTextQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->wordLimit, $this->anonymous, $this->multiuse))
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
//USERCODE-SECTION-editTextQuestion_form-sessionID-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-sessionID-validation

//USERCODE-SECTION-editTextQuestion_form-id-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-id-validation

//USERCODE-SECTION-editTextQuestion_form-title-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-title-validation

//USERCODE-SECTION-editTextQuestion_form-displayStem-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-displayStem-validation

//USERCODE-SECTION-editTextQuestion_form-stem-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-stem-validation

//USERCODE-SECTION-editTextQuestion_form-wordLimit-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-wordLimit-validation

//USERCODE-SECTION-editTextQuestion_form-anonymous-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-anonymous-validation

//USERCODE-SECTION-editTextQuestion_form-multiuse-validation
// Put code here.
//ENDUSERCODE-SECTION-editTextQuestion_form-multiuse-validation

		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
        $out = show_editTextQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->wordLimit, $this->anonymous, $this->multiuse, $this->validateMessages);
		return $out;
	}

    function getNewQuestion()
    {
        if($this->displayStem) // displayStem really means display (instance) title as stem now
            $this->stem = '';
        return new  TextQuestion($this->stem, $this->displayStem, $this->wordLimit);
    }
}

