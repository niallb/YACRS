<?php

$questionTypes['basicquestion'] = array('name'=>'Multiple Choice question', 'class'=>'basicQuestion', 'edit'=>'editBasicQuestion_form');

class basicQuestion extends questionBase
{
    var $stem;
    var $displayStem; // New version, display stem if it exists, previously title was stored as stem
    var $displayTitle;
    var $quType;
    var $options;
    var $correct;

    var $responseValue;

	function __construct($stem, $displayTitleAsStem, $options)
	{
        $this->stem = $stem;
        $this->displayStem = (strlen($stem) > 0); // New version, display stem if it exists.
        $this->displayTitle =  $displayTitleAsStem; // Prev displayStem param is now display (instance) title as stem.
        $optCount = 0;
        $cCount = 0;
        $tmpOpts = explode("\n",$options);
        $this->options = array();
        $this->correct = array();
        foreach($tmpOpts as $t)
        {
            $t= trim($t);
        	if((substr($t,0,1)=='*')&&(strlen($t)>1))
            {
        		$optCount++;
                $cCount++;
                $this->options[] = substr($t,1);
                $this->correct[] = true;
            }
            elseif(strlen($t)>0)
            {
        		$optCount++;
                $this->options[] = $t;
                $this->correct[] = false;
            }
        }
        if($cCount == 0)
        {
        	$this->quType = 'MCS';
            $this->correct = false;
        }
        elseif($cCount == $optCount)
        {
        	$this->quType = 'MRS';
            $this->correct = false;
        }
        elseif($cCount == 1)
        {
        	$this->quType = 'MCQ';
        }
        else
        {
        	$this->quType = 'MRQ';
        }
        $this->responseValue = false;
    }

    function allowReview()
    {
        return true;
    }

    function checkResponse($qiID, $resp, $SMSResp=false)
    {
        if($resp == false)
	        $this->responseValue = false;
        else
            $this->responseValue = $resp->value;

        if($SMSResp)
        {
            $SMSResp = trim(strtoupper($SMSResp));
            $this->responseValue = '';
	        if(($this->quType == 'MCS')||($this->quType == 'MCQ'))
	        {
                if(strlen($SMSResp))
                {
                	$SMSResp = substr($SMSResp,0,1);
                    $ans = 'R'.(ord($SMSResp)-ord('A')+1);
                    $this->responseValue = $ans;
                }
	        }
	        else
	        {
		        $onum = 0;
		        foreach($this->options as $o)
		        {
                    $ch = chr(ord('A')+$onum);
                    $onum++;
                	$k = "R$onum";
                    if(strpos($SMSResp, $ch)!==false)
                         $this->responseValue .= $k.',';
                }
                $this->responseValue = trim($this->responseValue,',');
            }
        }
        elseif((isset($_REQUEST['submitans']))&&($_REQUEST['qiID']==$qiID))
        {
            $this->responseValue = '';
	        if(($this->quType == 'MCS')||($this->quType == 'MCQ'))
	        {
                if(isset($_REQUEST['Ans']))
                    $this->responseValue = $_REQUEST['Ans'];
	        }
	        else
	        {
		        $onum = 0;
		        foreach($this->options as $o)
		        {
                    $onum++;
                	$k = "R$onum";
                    if((isset($_REQUEST[$k]))&&($_REQUEST[$k]==$k))
                         $this->responseValue .= $k.',';
                }
                $this->responseValue = trim($this->responseValue,',');
            }
        }
    }

    function getGraphLabels()
    {
        $out = array();
        $onum = 0;
        foreach($this->options as $o)
        {
            $onum++;
            $out['R'.$onum] = $o;
        }
        return $out;
    }

    function render($title='')
    {
        //$out = '<fieldset>';
        //if($this->responseValue == false)
        //    $out .= '<legend>Input:</legend>';
        //else
        //    $out .= '<legend>You answered:</legend>';
        if($this->displayStem)
            $out .= "<p class='stem'>{$this->stem}</p>";
        if($this->displayTitle)
            $out .= "<p class='stem'>{$title}</p>";
        $out .= "<div class='form-group'><div class='col-sm-12'>";
        $onum = 0;
        foreach($this->options as $o)
        {
            $onum++;
            $out .= "<div class='radio'><label for='R$onum'>";

            if(($this->quType == 'MCS')||($this->quType == 'MCQ'))
            {
                $out .= "<input type='radio' name='Ans' id='R$onum' value='R$onum'";
                if($this->responseValue == "R$onum")
                    $out .= " checked='1'";
                if(($this->responseValue !== false)&&(!isset($_REQUEST['doupdate'])))
                    $out .= " disabled='1'";
                $out .= "/> ";
            }
            else
            {
                $out .= "<input type='checkbox' name='R$onum' id='R$onum' value='R$onum'";
                if($this->responseValue !== false)
                {
                    if(!isset($_REQUEST['doupdate']))
                        $out .= " disabled='1'";
	                if(strpos($this->responseValue,"R$onum")!==false)
	                    $out .= " checked='1'";
                }
                $out .= "/> ";
            }
            $out .= $o;
            $out .= "</label>";
            $out .= '</div>';

        }
        $out .= '</div></div>';
        //if($this->responseValue == false)
        //{
        //    $out .= "<div class='submit'><input type='submit' name='submitans' value='Submit answer'/></div>";
        //}
        //$out .= '</fieldset>';
        return $out;
    }

    function score($qi, $resp)
    {
        $score = 0;
        if(strlen($resp->value))
        {
            $rs = explode(',', $resp->value);
            //echo '<pre>'; print_r($rs); echo '</pre>';
            if(is_array($this->correct))
            {
                foreach($this->correct as $id=>$sc)
                {
                    //echo "$id => $sc<br/>";
                    if(in_array('R'.($id+1), $rs))
                       $score += $sc;
                }
            }
            elseif(is_array($qi->extras['correct']))
            {
                foreach($qi->extras['correct'] as $id=>$sc)
                {
                    if(in_array('R'.($id+1), $rs))
                       $score += $sc;
                }
            }
        }
        //echo '<pre>'; print_r($this); echo '</pre>';
        //echo '<pre>'; print_r($qi); echo '</pre>';
        //echo '<pre>'; print_r($resp); echo '</pre>';
        return $score;
    }

    function getCorrectStr($qi)
    {
        $ca = array();
        if(is_array($this->correct))
        {
            foreach($this->correct as $id=>$sc)
            {
	            if($sc)
    	            $ca[] = 'R'.($id+1);
            }
        }
        elseif(is_array($qi->extras['correct']))
        {
            foreach($qi->extras['correct'] as $id=>$sc)
            {
                if($sc)
	                $ca[] = 'R'.($id+1);
            }
        }
        return implode(' ',$ca);
    }

    function getCorrectForDisplay($qi)
    {
        $ca = array();
        if(is_array($this->correct))
        {
            foreach($this->correct as $id=>$sc)
            {
	            if($sc)
    	            $ca[] = $this->options[$id];
            }
        }
        elseif(is_array($qi->extras['correct']))
        {
            foreach($qi->extras['correct'] as $id=>$sc)
            {
                if($sc)
	                $ca[] = $this->options[$id];
            }
        }
        if(sizeof($ca))
	        return implode('; ',$ca);
        else
            return '(undefined)';
    }

    function getResponseForDisplay($resp)
    {
        if($resp==false)
            return '(none)';
        $rs = explode(',', $resp->value);
        $ca = array();
        foreach($rs as $r)
        {
             $id = intval(substr($r, 1))-1;
    	     $ca[] = $this->options[$id];
        }
        return implode('; ',$ca);
    }

    function report($thisSession, $qi, $detailed = false, $anonymous=false)
    {
	    if(isset($_REQUEST['updateAnotation']))
	    {
	        if(strlen(trim($_REQUEST['newcat'])))
	        {
	            if(!isset($thisSession->extras['categories']))
	                $thisSession->extras['categories'] = array();
	            if(!in_array(trim($_REQUEST['newcat']), $thisSession->extras['categories']))
		            $thisSession->extras['categories'][] = trim($_REQUEST['newcat']);
	            $thisSession->update();
	            $qi->extras['category'] = trim($_REQUEST['newcat']);
	        }
	        else
	            $qi->extras['category'] = trim($_REQUEST['cat']);
	        $qi->extras['correct'] = array();
	        for($n=0; $n<$_REQUEST['optcount']; $n++)
	            $qi->extras['correct'][$n] = isset($_REQUEST['corr_'.$n])?$_REQUEST['corr_'.$n]:0;
	        $qi->update();
	    }
        if(isset($_REQUEST['cdc']))
        {
        	//Create a duplicate question for comparason
            $qu = question::retrieve_question($qi->theQuestion_id);
		    $qi2 = $thisSession->addQuestion($qu);
            //# link question susing $qi->extras['paired'] = array( ids..)
            if(isset($qi->extras['paired']))
            {
                $qi2->extras['paired'] = $qi->extras['paired'];
            }
            else
            {
                $qi2->extras['paired'] = array();
            }
            $qi2->extras['paired'][] = $qi->id;
            //# add (take #) to $qi2 title;
            $qi2->title .= '('.(sizeof($qi2->extras['paired'])+1).')';
            $qi2->update();
        }

	    $labels = $this->getGraphLabels();
        $out = '';
	    $count = array_fill_keys(array_keys($labels), 0);
	    $responses = response::retrieve_response_matching('question_id', $_REQUEST['qiID']);
	    if($responses)
	    {
		    foreach($responses as $r)
		    {
		        if(strlen($r->value))
		        {
			        $votes = explode(',',$r->value);
			        foreach($votes as $v)
			        {
		                $count[$v]++;
			        }
		        }
		    }
	    }
	    if($detailed)
	    {
		    $out .= "<h2 class='page-section'>".$qi->title."<a class='pull-right' href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}'>Back to Summary</a></h2>";
		    $out .= "<table class='table table-striped'><thead><tr><th>User</th><th>Name</th><th>Response</th></thead><tbody>";
	        if($responses)
	        {
		        foreach($responses as $r)
		        {
		        	$member = sessionMember::retrieve_sessionMember($r->user_id);
                    if($anonymous)
			            $out .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>{$r->value}</td><td>{$r->time}</td></tr>";
                    else
			            $out .= "<tr><td>{$member->userID}</td><td>{$member->name}</td><td>{$r->value}</td><td>{$r->time}</td></tr>";
			        //$out .= "<tr><td>{$member->userID}</td><td>{$member->name}</td><td>{$r->value}</td></tr>";
		        }
	        }
		    $out .= "</table>";
	    }
	    else
	    {
	        if($responses)
	        {
		    	$out .= "<h2 class='page-section'>".$qi->title."<span class='pull-right'><a href='responses.php?sessionID={$thisSession->id}&qiID={$qi->id}&display=detail'>".sizeof($responses)." Response".s(sizeof($responses))."</a></h2>";
	            $out .= "<img src='chart.php?qiID={$qi->id}'/><br/>";
	        }
		    $out .= "<form action='#' method='POST' class='form-horizontal'>";
            $out .= "<input type='hidden' value='".sizeof($labels)."' name='optcount'/>";
		    $out .= "<table class='table table-striped'><thead><tr><th>Response</th><th>Count</th><th>Correct</th></thead><tbody>";
            $ci = 0;
		    foreach($labels as $r=>$txt)
		    {
		        $out .= "<tr><td>{$txt}</td><td>{$count[$r]}</td>";
                if(is_array($this->correct))
                {
                    $out .= "<td><input type='checkbox' value='1'";
                    if($this->correct[$ci])
	                    $out .= " checked='checked'";
                    $out .= " disabled='disabled'/></td>";
                }
                else
                {
                    $out .= "<td><input type='checkbox' value='1' name='corr_$ci'";
                    if((isset($qi->extras['correct'][$ci]))&&($qi->extras['correct'][$ci]))
	                    $out .= " checked='checked'";
                    $out .= "/></td>";
                }
                $out .= "</tr>";
                $ci++;
		    }
		    $out .= "</table>";
		    $out .= "<div class='form-group'><label class='control-label col-sm-4' for='cat'>Question Category</label><div class='col-sm-8'><select name='cat' class='form-control'>";
            $out .= "<option value=''>None</option>";
            if(isset($thisSession->extras['categories']))
            {
                foreach($thisSession->extras['categories'] as $cat)
                {
                    $out .= "<option value='$cat'";
                    if((isset($qi->extras['category']))&&($qi->extras['category']==$cat))
                        $out .= " selected='selected'";
                    $out .= ">$cat</option>";
                }
            }
            $out .= "</select></div></div><div class='form-group'><label class='control-label col-sm-4' for='newcat'>Or Create New Category</label><div class='col-sm-8'><input type='text' name='newcat' class='form-control'/></div></div><div class='form-group'><div class='col-sm-4'><a class='btn btn-link btn-block' href='{$_SERVER['PHP_SELF']}?sessionID={$thisSession->id}&qiID={$qi->id}&cdc=1'><i class='fa fa-plus-circle'></i> Duplicate This Question</a></div><div class='col-sm-4'><input type='submit' name='updateAnotation' class='btn btn-primary btn-block' value='Update'/></div><div class='col-sm-4'><input type='submit' name='updateAnotation' class='btn btn-default btn-block' value='Update and Go to Next Question'/></div></div></form>";

            //$out .= '<pre>'.print_r($this,1).'</pre>';
	    }
        return $out;
    }

    function getModifiedCopyForm()
    {
        $form = $this::getEditForm();
        $def = '';
        if(is_array($this->correct))
        {
            for($n = 0; $n < sizeof($this->options); $n++)
            {
                if($this->correct[$n])
                    $def .= "* {$this->options[$n]}\r\n";
                else
                    $def .= "* {$this->options[$n]}\r\n";
            }
        }
        else
        {
            $prefix = ($this->quType == 'MRQ') ? '* ' : '';
            foreach($this->options as $o)
                $def .= "{$prefix}{$o}\r\n";
        }
        $form->definition = $def;
        if($this->displayTitle)
            $form->displayStem = true;
        elseif($this->displayStem)
            $form->stem = $this->stem;
        return $form;
    }

    static function questionTypeName() // Used for looking up classes and help files
    {
        return 'basicquestion';
    }

    static function getEditForm()
    {
    	$form = new editBasicQuestion_form();
        return $form;
    }
}

/*
form editBasicQuestion_form
{
ajaxaction = "ajax/editQuestion.php";
hidden sessionID '0';
hidden id '0';
static qutype "basicquestion";
string[80] title "Title" {hint="The title is used to identify the question, and can optionally be displayed as the stem. The title can be edited for each question instance";}
boolean displayStem "Display title as stem to participants." {hint="Check this to display the title to students.";}
memo[70,4] stem "Stem, optional" {hint="The question stem should go here if the options are specific to this question.";}
memo[70,6] definition "Options:" {hint="See instructions below";}
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

define('editBasicQuestion_form_magic', md5('editBasicQuestion_form'));

//
function show_editBasicQuestion_form($sessionID, $id, $title, $displayStem, $stem, $definition, $anonymous, $multiuse, $validateMessages=array())
{
    $out = '<form id="editBasicQuestion_form" action="ajax/editQuestion.php" method="POST" class="form-horizontal" onsubmit="return false;">';
    $out .= '<input type="hidden" name="editBasicQuestion_form_code" value="'.editBasicQuestion_form_magic.'"/>';

    $out .= '<input type="hidden" name="sessionID" value="'.$sessionID.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="id" value="'.$id.'"';
    $out .= "/>\n";

    $out .= '<input type="hidden" name="qutype" value="basicquestion"';
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
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="Check this to display the title to students."
                     data-html="true"><span aria-hidden="true" title="Help with Display title as stem to participants." aria-label="Help with Display title as stem to participants." class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['displayStem']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['displayStem'].'</span>';
    $out .= '</label>';
    $out .= "</div></div>\n";

    $out .= '<div class="form-group row">';
    $out .= '<label class="col-sm-4 control-label" for="stem">Stem, optional';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="The question stem should go here if the options are specific to this question."
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
    $out .= '<label class="col-sm-4 control-label" for="definition">Options:';
    $out .= ' <span class="btn btn-link p-0" role="button" data-container="body" data-toggle="popover" data-placement="right" data-content="See instructions below"
                     data-html="true"><span aria-hidden="true" title="Help with Options:" aria-label="Help with Options:" class="icon fa fa-question-circle text-info fa-fw" ></span></span>';
    if(isset($validateMessages['definition']))
        $out .= '<br/><span style="color: Red;">'.$validateMessages['definition'].'</span>';
    $out .= '</label>';
    $out .= '<br/><span class="forminput"><textarea class="form-control" name="definition" id="definition" cols="70" rows="6"/>';
    $out .= htmlentities($definition);
    $out .= "</textarea></span></div>\n";

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
    $out .= '<input class="submit btn btn-success" name="editBasicQuestion_form_submit" type="submit" value="Create" onclick=\'submitForm("editBasicQuestion_form", this);\' />';
    $out .= '<input class="submit btn btn-secondary" name="editBasicQuestion_form_cancel" type="submit" value="Cancel" onclick=\'submitForm("editBasicQuestion_form", this);\' />';
    $out .= "</div></div>";

    $out .= '</form>';
    return $out;
}

function editBasicQuestion_form_submitted()
{
    if((isset($_REQUEST['editBasicQuestion_form_code']))&&($_REQUEST['editBasicQuestion_form_code']==editBasicQuestion_form_magic))
        return true;
    else
        return false;
}

function update_from_editBasicQuestion_form(&$sessionID, &$id, &$title, &$displayStem, &$stem, &$definition, &$anonymous, &$multiuse)
{
    if((isset($_REQUEST['editBasicQuestion_form_code']))&&($_REQUEST['editBasicQuestion_form_code']==editBasicQuestion_form_magic))
    {
        if(isset($_REQUEST['editBasicQuestion_form_cancel']))
            return false;
        $sessionID = strval($_REQUEST['sessionID']);
        $id = strval($_REQUEST['id']);
        $title = strval($_REQUEST['title']);
        $displayStem = (isset($_REQUEST['displayStem'])&&(intval($_REQUEST['displayStem'])>0));
        $stem = strval($_REQUEST['stem']);
        $definition = strval($_REQUEST['definition']);
        $anonymous = (isset($_REQUEST['anonymous'])&&(intval($_REQUEST['anonymous'])>0));
        $multiuse = (isset($_REQUEST['multiuse'])&&(intval($_REQUEST['multiuse'])>0));
        return true;
    }
    else
    {
        return false;
    }
}

//Wrapper class for editBasicQuestion_form QuickForm functions that emulates a form_lib2::nbform derived class.
class editBasicQuestion_form
{
	var $sessionID; //hidden
	var $id; //hidden
	var $title; //string
	var $displayStem; //boolean
    var $stem; //memo (multiline string)
    var $definition; //memo (multiline string)
    var $anonymous; //boolean
	var $multiuse; //boolean
	var $validateMessages;

	function __construct($readform=true)
	{
		$this->validateMessages = array();
		if(editBasicQuestion_form_submitted())
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
		$this->definition = $data->definition;
        $this->anonymous = $data->anonymous;
		$this->multiuse = $data->multiuse;
	}

	function getData(&$data)
	{
		$data->sessionID = $this->sessionID;
		$data->id = $this->id;
        $data->qutype = "basicquestion";
		$data->title = $this->title;
		$data->displayStem = $this->displayStem;
        $data->stem = $this->stem;
		$data->definition = $this->definition;
        $data->anonymous = $this->anonymous;
		$data->multiuse = $this->multiuse;
		return $data;
	}

	private function readAndValidate()
	{
		if(update_from_editBasicQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->definition, $this->anonymous, $this->multiuse))
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
        //USERCODE-SECTION-editBasicQuestion_form-sessionID-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-sessionID-validation

        //USERCODE-SECTION-editBasicQuestion_form-id-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-id-validation

        //USERCODE-SECTION-editBasicQuestion_form-title-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-title-validation

        //USERCODE-SECTION-editBasicQuestion_form-displayStem-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-displayStem-validation

        //USERCODE-SECTION-editBasicQuestion_form-stem-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-stem-validation

        //USERCODE-SECTION-editBasicQuestion_form-definition-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-definition-validation

		//USERCODE-SECTION-editBasicQuestion_form-anonymous-validation
		// Put code here.
		//ENDUSERCODE-SECTION-editBasicQuestion_form-anonymous-validation

        //USERCODE-SECTION-editBasicQuestion_form-multiuse-validation
        // Put code here.
        //ENDUSERCODE-SECTION-editBasicQuestion_form-multiuse-validation

		if(sizeof($this->validateMessages)==0)
			return true;
		else
			return false;
	}

	function getHtml()
	{
        $out = show_editBasicQuestion_form($this->sessionID, $this->id, $this->title, $this->displayStem, $this->stem, $this->definition, $this->anonymous, $this->multiuse, $this->validateMessages);
		return $out;
	}

    function getNewQuestion()
    {
        if($this->displayStem) // displayStem really means display (instance) title as stem now
            $this->stem = '';
        return new basicQuestion($this->stem, $this->displayStem, $this->definition);
    }
}

