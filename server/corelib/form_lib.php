<?php

function formStart($target=false, $method='POST')
{
	if(!$target)
    	$target = $_SERVER['PHP_SELF'];
    $out = '<form action="'.$target.'" method="'.$method.'">';
    return $out;
}

function formEnd()
{
    return '</form>';
}

function getFormInput($name, $default=false)
{
	if(isset($_REQUEST[$name]))
		return trim(strip_tags($_REQUEST[$name]));
    else
    	return $default;
}

function textInput($caption, $name, $value="", $validateMsgs=null, $width=40)
{
    $out = "<div class=\"formfield\">";
    $out .= "<label for=\"$name\">$caption:";
    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
    $out .= '</label>';
    $out .= "<br/><span class=\"forminput\"><input type=\"text\" name=\"$name\" value=\"$value\" size=\"$width\" /></span></div>\n";
    return $out;
}

function checkboxInput($caption, $name, $checked=false, $validateMsgs=null)
{
    $out = "<div class=\"formfield\">";
    $out .= "<label for=\"$name\">$caption:";
    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
    $out .= '</label>';
    $out .= "<br/><span class=\"forminput\"><input type=\"checkbox\" name=\"$name\" ";
    if($checked)
    	$out .= 'checked="1" ';
    $out .= "/></span></div>\n";
    return $out;
}

function textareaInput($caption, $name, $value="", $validateMsgs=null, $width=30, $height=3)
{
    $out = "<div class=\"formfield\">";
    $out .= "<label for=\"$name\">$caption:";
    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
    $out .= '</label>';
    $out .= "<br/><span class=\"forminput\"><textarea name=\"$name\" cols=\"$width\" rows=\"$height\"/>";
    $out .= $value;
    $out .= "</textarea></span></div>\n";
    return $out;
}

function passwordInput($caption, $name, $value="", $validateMsgs=null, $width=12)
{
    $out = "<div class=\"formfield\">";
    $out .= "<label for=\"$name\">$caption:";
    if((is_array($validateMsgs))&&(array_key_exists($name, $validateMsgs)))
        $out .= "<span class=\"errormsg\">{$validateMsgs[$name]}</span>";
    $out .= '</label>';
    $out .= "<br/><span class=\"forminput\"><input type=\"password\" name=\"$name\" value=\"$value\" size=\"$width\" /></span></div>\n";
    return $out;
}

function selectListInput($caption, $name, $options, $value="")
{
    $out = "<div class=\"formfield\">";
    $out .= "<label for=\"$name\">$caption:</label>";
    $out .= "<br/><span class=\"forminput\">";
    if(!is_array($options))
        $options = explode(",",$options);
    $out .= "<select name=\"$name\">\n";
    for($n=0; $n<sizeof($options); $n++)
    {
        $out .= "<option";
        if(strpos($options[$n],":"))
            list($nm, $v) = explode(":",$options[$n],2);
        else
            $nm = $v = $options[$n];
        if(trim($v)==trim($value))
            $out .= " selected=\"1\"";
        $out .= " value='$v'>{$nm}</option>\n";

    }
    $out .= "</select></span></div>\n";
    return $out;
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
    $out .= "<span class=\"forminput\"><input type=\"text\" name=\"$name\" value=\"$strvalue\" /> (dd/mm/yyyy)";
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


?>
