<?php
/*****************************************************************************
This file is a component of the NBWebsites PHP toolkit.
http://www.nbwebsites.co.uk/

Copyright 2005-2012 Niall S F Barr

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*****************************************************************************/
require_once('corelib/safeRequestFunctions.php');
define('FORM_NOTSUBMITTED',0);
define('FORM_SUBMITTED_VALID', 1);
define('FORM_SUBMITTED_INVALID', 2);
define('FORM_CANCELED',3);

abstract class nbform
{
	private $inFieldset;
    protected $formStatus;
    protected $disabled;

    function __construct()
    {
    	$this->status = FORM_NOTSUBMITTED;
        $disabled = array();
    }

    function disable($fname, $disable=true)
    {
    	$this->disabled[$fname] = $disable;
    }

    function enable($fname)
    {
    	$this->disabled[$fname] = false;
    }

    function getStatus()
    {
    	return $this->formStatus;
    }

	function formStart($target=false, $method='POST', $enctype='')
	{
    	$this->inFieldset = false;
		if(!$target)
	    	$target = $_SERVER['PHP_SELF'];
	    $out = '<form action="'.$target.'" method="'.$method.'"';
        if($enctype != '')
        	$out .= ' enctype="'.$enctype.'"';
        $out .= ' class="form-horizontal">';
	    return $out;
	}

	function formEnd($showSubmit=true, $showCancel=false)    //# include submit/cancel
	{
    	$out = '';
    	if($showSubmit|$showCancel)
        {
            $out .= "<div class=\"formfield\">";
    		if($showSubmit)
	            $out .= '<input class="submit btn btn-lg btn-success" name="submit" type="submit" value="Submit" />';
    		if($showCancel)
	            $out .= '<input class="submit btn btn-lg btn-default" name="submit" type="submit" value="Cancel" />';
            $out .= "</div>";
        }
        if($this->inFieldset)
        	$out .= '</fieldset>';
	    $out .= '</form>';
        return $out;
	}

	function submitInput($name, $value1, $value2=null)
	{
	    $out = "<div class=form-group><div class=\"col-sm-8 col-sm-offset-4\">";
        $out .= '<input class="submit btn btn-success" name="'.$name.'" type="submit" value="'.$value1.'" />';
  		if($value2)
            $out .= '<input class="submit btn btn-link" name="'.$name.'" type="submit" value="'.$value2.'" />';
        $out .= "</div></div>";
	    return $out;
	}

	function getFormInput($name, $default=false)
	{
		if(requestSet($name))
			return trim(strip_tags(requestRaw($name)));
	    else
	    	return $default;
	}

	function textInput($caption, $name, $value="", $validateMsgs=null, $width=40, $required=false)
	{
	    $out = "<div class=\"form-group\">";
        $out .= "<label class='col-sm-4 control-label' for=\"$name\">$caption";
        if($required)
        	$out .= '<span class="required">*</span>';
	    $out .= '</label>';
	    $out .= "<div class='col-sm-8'>";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<div class='alert alert-danger'>{$validateMsgs[$name]}</div>";
	    
	    $out .= '<input type="text" class="form-control" name="'.$name.'" id="'.$name.'" value="'.$value.'" size="'.$width.'"';
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="1" ';
        $out .= "/></div></div>";
        
	    return $out;
	}

	function textInputCompact($caption, $name, $value="", $validateMsgs=null, $width=40, $required=false)
	{
	    $out = "<span class=\"compactformfield\">";
        $out .= "<label for=\"$name\">$caption ";
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= " <span class=\"forminputcompact\"><input type=\"text\" name=\"$name\" value=\"$value\" size=\"$width\" ";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="1" ';
        $out .= "/></span></span>\n";
	    return $out;
	}

    // Fake field used for 'text' fields that aren't to be edited, but should be clearer than disabled text fields.
	function fixedInput($caption, $name, $value)
	{
		$out = "<div class=\"form-group\">";
        $out .= "<label class='col-sm-4 control-label' for=\"$name\">$caption</label>";
        $out .= "<div class='col-sm-8'>";
	    $out .= "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
        $out .= '<p class="form-control-static" id="'.$name.'">'.$caption.'</p>'; 
        $out .= "/></div></div>";
	    
	    return $out;
	}

	function uploadInput($caption, $name, $validateMsgs=null, $width=40, $required=false)
	{
	    $out = "<div class=\"formfield\" id=\"$name\">";
        if($required)
	    	$out .= "<label for=\"$name\" style=\"color: Red;\">$caption: *";
        else
	    	$out .= "<label for=\"$name\">$caption:";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= "<br/><span class=\"forminput\"><input type=\"file\" name=\"$name\" size=\"$width\" ";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= "/></span></div>\n";
	    return $out;
	}

	function hiddenInput($name, $value)
	{
	    $out = "<input type=\"hidden\" name=\"$name\" value=\"$value\" />";
	    return $out;
	}

	function startFieldset($legend=null)
	{
        if($this->inFieldset)
        	$out = '</fieldset><fieldset>';
        else
	    	$out = '<fieldset>';
        if($legend)
        	$out .= '<legend>'.$legend.'</legend>';
        $this->inFieldset = true;
	    return $out;
	}

    //# Should also have an option of checkboxGroupInput that does several at once
	function checkboxInput($caption, $name, $checked=false, $validateMsgs=null, $required=false)
	{
		$out = "<div class=\"form-group\">";
	    $out .= "<div class='col-sm-8 col-sm-offset-4'>";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<div class='alert alert-danger'>{$validateMsgs[$name]}</div>";
	    
	    $out .= "<div class=\"checkbox\"><label><input type=\"checkbox\" name=\"$name\" id=\"$name\" value=\"1\"";
	    if($checked)
	    	$out .= ' checked="1" ';
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= "/>";
        $out .= $caption;
        $out .= "</label></div></div></div>";
        
	    return $out;
	}

	function checkboxInputCompact($caption, $name, $checked=false, $validateMsgs=null, $required=false)
	{
	    $out = "<span class=\"formfieldcompact\">";
	    $out .= " <span class=\"forminputcompact\"><input type=\"checkbox\" name=\"$name\" value=\"1\" ";
	    if($checked)
	    	$out .= 'checked="1" ';
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
	    $out .= "/></span>";
  	    $out .= "<label for=\"$name\">$caption ";
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
		$out .= "</span>\n";
	    return $out;
	}

	function radioInputCompact($caption, $name, $value, $checked=false, $validateMsgs=null)
	{
	    $out = "<span class=\"formfieldcompact\">";
	    $out .= "<label for=\"$name\">$caption ";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= " <span class=\"forminputcompact\"><input type=\"radio\" name=\"$name\" value=\"$value\" ";
	    if($checked)
	    	$out .= 'checked="1" ';
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
	    $out .= "/></span></span>\n";
	    return $out;
	}

    //# Should really replace this with radioGroupInput that does several at once.
	function radioInput($caption, $name, $value, $checked=false, $validateMsgs=null)
	{
	    $out = "<div class=\"formfield\">";
	    $out .= "<label for=\"$name\">$caption:";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= "<br/><span class=\"forminput\"><input type=\"radio\" name=\"$name\" value=\"$value\" ";
	    if($checked)
	    	$out .= 'checked="1" ';
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
	    $out .= "/></span></div>\n";
	    return $out;
	}

	function radioGroupInput($caption, $name, $options, $value="", $required=false, $validateMsgs=null)
	{
    	$out = '';
    	$out .= '<fieldset><legend>'.$caption.'</legend>';
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    if(!is_array($options))
	        $options = explode(",",$options);
        $out .= '<br/>';
        foreach($options as $key => $val)
        {
	        if((is_integer($key))&&(strpos($val,":")))
	            list($nm, $v) = explode(":",$val,2);
	        else
        	{
	            $nm = $val;
                $v = $key;
            }
		    $out .= "<div class=\"formfield\">";
		    $out .= "<span class=\"forminput\"><input type=\"radio\" name=\"$name\" value=\"$v\" ";
            if($v==$value)
		    	$out .= 'checked="1" ';
            //if(trim($nm)=='')
            /*// This is a hack for the LT conference. Needs replaced with a better mechanisim that's in the derived class.
            if((substr(trim($nm),0,1) != '<')&&(strlen(trim($nm))>4))
            	$out .= ' disabled="disabled" '; */
            if((isset($this->disabled[$name]))&&($this->disabled[$name]))
            	$out .= ' disabled="disabled" ';
		    $out .= "/></span>";
		    $out .= "<label for=\"$name\">$nm";
		    $out .= "</label></div>\n";
        }
        $out .= '<br/>&nbsp;';
    	$out .= '</fieldset>';
	    return $out;
	}

	function inlineRadioGroupInput($caption, $name, $options, $value="", $required=false, $validateMsgs=null)
	{
    	$out = '';
    	$out .= '<fieldset><legend>'.$caption.'</legend>';
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
        $out .= '<br/>';
	    if(!is_array($options))
	        $options = explode(",",$options);
        foreach($options as $key => $val)
        {
	        if((is_integer($key))&&(strpos($val,":")))
	            list($nm, $v) = explode(":",$val,2);
	        else
        	{
	            $nm = $val;
                $v = $key;
            }
		    $out .= "<span class=\"forminput\"><input type=\"radio\" name=\"$name\" value=\"$v\" ";
            if($v==$value)
		    	$out .= 'checked="1" ';
            //if(trim($nm)=='')
            /*// This is a hack for the LT conference. Needs replaced with a better mechanisim that's in the derived class.
            if((substr(trim($nm),0,1) != '<')&&(strlen(trim($nm))>4))
            	$out .= ' disabled="disabled" '; */
            if((isset($this->disabled[$name]))&&($this->disabled[$name]))
            	$out .= ' disabled="disabled" ';
		    $out .= "/></span>";
		    $out .= "<label for=\"$name\">$nm";
		    $out .= "</label>\n";
        }
        $out .= '<br/>&nbsp;';
    	$out .= '</fieldset>';
	    return $out;
	}

	function textareaInput($caption, $name, $value="", $validateMsgs=null, $width=70, $height=3, $required=false)
	{
	    $out = "<div class=\"form-group\">";
    	$out .= "<label for=\"$name\" class='control-label col-sm-4'>$caption";
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    $out .= '</label>';
	    $out .= '<div class="col-sm-8">';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<div class=\"alert alert-error\">{$validateMsgs[$name]}</div>";
	    $out .= "<textarea name=\"$name\" class=\"form-control\" rows=\"$height\"";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= ">";
	    $out .= $value;
	    $out .= "</textarea></div></div>";
	    return $out;
	}

	function passwordInput($caption, $name, $value="", $validateMsgs=null, $width=12, $required=false)
	{
	    $out = "<div class=\"formfield\">";
    	$out .= "<label for=\"$name\">$caption:";
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= "<br/><span class=\"forminput\"><input type=\"password\" name=\"$name\" value=\"$value\" size=\"$width\"";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= "/></span></div>\n";
	    return $out;
	}

	function selectListInput($caption, $name, $options, $value="", $required=false)
	{
		$out = "<div class=\"form-group\">";
        $out .= "<label class='col-sm-4 control-label' for=\"$name\">$caption";
        if($required)
        	$out .= '<span class="required">*</span>';
	    $out .= '</label>';
	    $out .= "<div class='col-sm-8'>";
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<div class='alert alert-danger'>{$validateMsgs[$name]}</div>";
	        
	    if(!is_array($options))
	        $options = explode(",",$options);
	    $out .= "<select class=\"form-control\" name=\"$name\" id=\"$name\"";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= ">\n";
        foreach($options as $key => $val)
        {
	        $out .= "<option";
	        if((is_integer($key))&&(strpos($val,":")))
	            list($nm, $v) = explode(":",$val,2);
	        else
            {
	            $nm = $val;
                $v = $key;
            }
	        if(trim($v)==trim($value))
	            $out .= " selected=\"1\"";
	        $out .= " value='$v'>{$nm}</option>";

	    }
	    $out .= "</select></div></div>";
        
	    return $out;
		
	}

	function selectListInputCompact($caption, $name, $options, $value="", $required=false, $validateMsgs=null)
	{
	    $out = "<span class=\"formfieldcompact\">";
     	$out .= "<label for=\"$name\">$caption </label>";
        if($required)
        	$out .= '<span style="color: Red;">*</span>';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= " <span class=\"forminputcompact\">";
	    if(!is_array($options))
	        $options = explode(",",$options);
	    $out .= "<select name=\"$name\"";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= ">\n";
        foreach($options as $key => $val)
        {
	        $out .= "<option";
	        if(is_integer($key))
            {
            	if(strpos($val,":"))
	            	list($nm, $v) = explode(":",$val,2);
                else
                	$nm = $v = $val;
            }
	        else
            {
	            $nm = $val;
                $v = $key;
            }
	        if(trim($v)==trim($value))
	            $out .= " selected=\"1\"";
	        $out .= " value='$v'>{$nm}</option>\n";

	    }
	    $out .= "</select></span></span>\n";
	    return $out;
	}

    function groupStart($legend=null)
    {
    	$out = '<fieldset>';
        if($legend !== null)
        	$out .= "<legend>$legend</legend>";
        return $out;
    }

    function groupEnd()
    {
    	return '</fieldset>';
    }

	function dateInput($caption, $name, $value=null, $validateMsgs=null)
	{
	    if($value!==null)
	        $strvalue = date2form($value);
	    else
	        $strvalue='';
	    $out = '<div class="formfield">';
	    $out .= '<label for="'.$name.'">'.$caption.': ';
	    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
	        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
	    $out .= '</label>';
	    $out .= "<span class=\"forminput\"><input type=\"text\" name=\"$name\" value=\"$strvalue\" ";
        if((isset($this->disabled[$name]))&&($this->disabled[$name]))
          	$out .= ' disabled="disabled" ';
        $out .= "/> (dd/mm/yyyy)";
	    $out .= "</span></div>";
	    return $out;
	}

	function form2date($in)
	{
	    $in2 = preg_replace('/(\d+)\/(\d+)\/(\d+)/i','${2}/${1}/${3}', $in);
	    return strtotime($in2);
	}

	function date2form($in)
	{
	    return strftime("%d/%m/%Y", $in);
	}
}

?>
