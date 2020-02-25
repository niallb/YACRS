<?php
/*****************************************************************************
This file is a component of the NBWebsites PHP toolkit.
http://www.nbwebsites.co.uk/

Copyright 2005-2016 Niall S F Barr

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

// New helplinks, put here as this will always be included. I may just make it part of the templateMerge class eventually
function helpLink($name)
{
    $link = '';
    global $YACRSHelpLinks;
    $helpLinksFile = dirname(__DIR__).'/help/helplinks.txt';
    if(!is_array($YACRSHelpLinks))
    {
        $YACRSHelpLinks = array();
        if(file_exists($helpLinksFile))
        {
            $raw = file($helpLinksFile);
            foreach($raw as $line)
            {
                if((substr(trim($line),0,1)!==';')&&(strpos($line, ':')))
                {
                    list($id, $title, $url) = explode(':', $line, 3);
                    $id = trim(strval($id));
                   // $name = $id;
                    $title = trim($title);
                    $url = trim($url);
                    if((strlen($title)>0)&&(strlen($id)>0)&&(strlen($url)>0))
                    {
                        $YACRSHelpLinks[$id] = new stdClass();
                        $YACRSHelpLinks[$id]->title = $title;
                        $YACRSHelpLinks[$id]->url = $url;
                    }
                }
            }
        }
    }
   // exit('<pre>'.print_r($YACRSHelpLinks, true).'</pre>');
    if(array_key_exists($name, $YACRSHelpLinks))
        $link = "<a href='{$YACRSHelpLinks[$name]->url}' target='_blank' class='btn btn-info'><span class='fa fa-question-circle' style='color:yellow;'></span> {$YACRSHelpLinks[$name]->title}</a>";
     //exit('<pre>'.print_r($YACRSHelpLinks, true).'</pre>');
    return $link;
}

class templateMerge
{
    var $fields;
    var $dataNames;
    var $pageData; // pageData is an array that gets filled with the merge data.
	               // In the C++ version a dataProvider interface is passed to render, 
				   // a rather better design...
    var $eachIdxStack;
    private $scripts;

    function __construct($templateFile)
    {
        $this->pageData = array();
        $this->scripts = array();
        $src = file_get_contents($templateFile);
        $tmpfields = explode("<%", $src);
        $this->fields = array();
        if(substr($tmpfields[0], 0, 2)==='<%')
        {
           $sf = 0;
        }
        else
        {
            $this->fields[] = "echo " . $tmpfields[0];
            $sf = 1;
        }
        for ($i = $sf; $i < count($tmpfields); $i++)
        {
            $this->fields[] = substr($tmpfields[$i], 0, strpos($tmpfields[$i], "%>"));
            if(strlen(substr($tmpfields[$i], strpos($tmpfields[$i], "%>") + 2)))
                $this->fields[] = "echo " . substr($tmpfields[$i], strpos($tmpfields[$i], "%>") + 2);
        }
        for ($i = 0; $i < count($this->fields); $i++)
        {
            if (strpos(trim($this->fields[$i]), "\$") === 0)
            {
                $type = 'global';
                $fieldinfo = substr(trim($this->fields[$i]), 1);
            }
            elseif (strpos($this->fields[$i], " ") !== false)
            {
                list($type, $fieldinfo) = explode(" ", $this->fields[$i], 2);
            }
            else
            {
                $type = $this->fields[$i];
                $fieldinfo = '';
            }
            $this->fields[$i] = array('type'=>trim($type), 'value'=>$fieldinfo);
            if ($type != 'echo')
            {
                $this->fields[$i]['params'] = $this->decodeParams($fieldinfo);
                if (array_key_exists('name', $this->fields[$i]['params']))
                {
                    $dataname = $this->fields[$i]['params']['name'];
                    $this->dataNames[] = $dataname;
                    $this->pageData[$dataname] = false;
                }
            }

        }
    }

    function addScript($path)
    {
        if(!in_array($path, $this->scripts))
        {
            $this->scripts[] = $path;
        }
    }

    function render()
    {
        $out = '';
        $this->eachIdxStack = array();
        $i = 0;
        while($i < count($this->fields))
            $out .= $this->renderPart($i);
        return $out;
    }

    function renderPart(&$i)
    {
        $out = '';
        $ist = $i;
        switch ($this->fields[$i]['type'])
        {
            case 'scripts':
                $out = '';
                foreach($this->scripts as $s)
                {
                    $out .= "<script src=\"{$s}\" type=\"text/javascript\" charset=\"utf-8\"></script>\n";
                }
                break;
            case 'autoscript':
                $fullpath = dirname($_SERVER['SCRIPT_FILENAME']).'/';
                //$webpath = dirname($_SERVER['REQUEST_URI']).'/';
                $scriptname = basename($_SERVER['SCRIPT_FILENAME'], '.php').'.js';
                if(file_exists($fullpath.'scripts/'.$scriptname))
                    $out .= "<script src=\"scripts/{$scriptname}\" type=\"text/javascript\" charset=\"utf-8\"></script>\n";
                elseif(file_exists($fullpath.$scriptname))
                    $out .= "<script src=\"{$scriptname}\" type=\"text/javascript\" charset=\"utf-8\"></script>\n";
                break;
	        case 'echo':
	            $out = $this->fields[$i]['value'];
	            break;
	        case 'global':
	            $out = isset($GLOBALS[$this->fields[$i]['value']]) ? $GLOBALS[$this->fields[$i]['value']] : '';
	            break;
	        case 'section':
	            $out = $this->sectionRender($i);
	            break;
	        case 'if':
	            $out = $this->ifRender($i);
	            break;
            case 'else':
            case 'end':
                break;
            case 'each':
                $out = $this->eachRender($i);
                break;
            case '#':
                if(sizeof($this->eachIdxStack))
                    $out = $this->eachIdxStack[sizeof($this->eachIdxStack)-1];
                else
                    $out = '';
                break;
	        default:
	            exit("Unable to render {$this->fields[$i]['type']} (".htmlentities($this->fields[$i]['value']).")");
	            break;
        }
        if($ist==$i)
            $i++;
        return $out;
    }

    function ifRender(&$i)
    {
        $fieldinfo = $this->fields[$i]['value'];
        $params = $this->fields[$i]['params'];
        $fulfil = false;
        if(isset($params['defined']))
        {
            $req = explode(' ',preg_replace('/\s+/', ' ', $params['defined']));
            foreach($req as $r)
            {
                if ((array_key_exists($r, $this->pageData)) && ($this->pageData[$r] !== false))
                {
                    $fulfil = true;
                }
            }
        }
        $i++;
        $ifout = '';
        $elseout = '';
        $inelse = false;
        while(($i<sizeof($this->fields))&&($this->fields[$i]['type'] != 'end'))
        {
            if($this->fields[$i]['type'] == 'else')
            {
               $inelse = true;
               $i++;
            }
            else
            {
                if($inelse)
                    $elseout .= $this->renderPart($i);
                else
                    $ifout .= $this->renderPart($i);
            }
        }
        $i++;
        if($fulfil)
            return $ifout;
        else
            return $elseout;
    }

    function eachRender(&$i)
    {
        $fieldinfo = $this->fields[$i]['value'];
        $params = $this->fields[$i]['params'];
        $fulfil = false;
        $tmpdata = array();
        if(isset($params['name']))
        {
            if ((array_key_exists($params['name'], $this->pageData)) && (is_array($this->pageData[$params['name']])))
            {
                $tmpdata = $this->pageData[$params['name']];
                $fulfil = true;
            }
        }
        $i++;
        if($fulfil)
        {
	        $start_i = $i;
	        $eachout = '';
            $keysToClear = array();
	        for($idx=0; $idx < sizeof($tmpdata); $idx++)
	        {
                array_push($this->eachIdxStack, $idx);
	            $i = $start_i;
	            // This is a bit of a hack, but should work....
                if(is_array($tmpdata[$idx]))
                {
                    foreach($tmpdata[$idx] as $k=>$v)
                    {
                        $this->pageData[$params['name'].'.'.$k] = $v;
                        $keysToClear[] = $params['name'].'.'.$k;
                    }
                }
                else
                {
                    $this->pageData[$params['name']] = $tmpdata[$idx];
                    $keysToClear = $params['name'];
                }

		        while(($i<sizeof($this->fields))&&($this->fields[$i]['type'] != 'end'))
		        {
	                $eachout .= $this->renderPart($i);
 		        }
		        $i++;
                array_pop($this->eachIdxStack);
                // clear out any data from created for this itteration
                foreach($keysToClear as $k)
                {
                    unset($this->pageData[$k]);
                }
                $keysToClear = array();
	        }
            $this->pageData[$params['name']] = $tmpdata;
            return $eachout;
        }
        else
        {
	        while(($i<sizeof($this->fields))&&($this->fields[$i]['type'] != 'end'))
	        {
	            $this->renderPart($i);
	        }
	        $i++;
            return '';
        }
    }

    function sectionRender(&$i)
    {
        $fieldinfo = $this->fields[$i]['value'];
        $out = '';
        $params = $this->fields[$i]['params'];
        $i++;
        if (array_key_exists('name', $params))
        {
            $dataname = trim($params['name']);
            if ((array_key_exists($dataname, $this->pageData)) && ($this->pageData[$dataname] !== false))
            {
                $out.= $this->pageData[$dataname];
                if (array_key_exists('div_id', $params))
                {
                    $out = '<div id="' . $params['div_id'] . '">' . $out . '</div>';
                }
                elseif (array_key_exists('div_class', $params))
                {
                    $out = '<div class="' . $params['div_class'] . '">' . $out . '</div>';
                }
            }
        }

        return $out;
    }

    function decodeParams($data)
    {
        $params = array();

        // eg name="BannerAds_468x60_as" div_id="bannerAds"
        // or  name="Ads_728x90_as" <#start> <div id="horizontalAds"> <#content> </div> <#end>
        // first find if there's a <#start> <#content> <#end> section

        if (strpos($data, '<#start>') !== false)
        {
            list($data, $merge) = explode('<#start>', $data, 2);
            $params['merge'] = '<#start>' . $merge;
        }

        $data = trim($data);
        while (strlen($data) > 0)
        {
            $sp = strcspn($data, "=\"'\r\n\t ");
            if ($sp > 0)
            {
                $name = substr($data, 0, $sp);
                $data = trim(substr($data, $sp));
                if (substr($data, 0, 1) == '=')
                {
                    $data = trim(substr($data, 1));

                    // if " or ' find match

                    $delim = substr($data, 0, 1);
                    if ($delim == '"' || $delim == "'")
                    {
                        $ep = strpos($data, $delim, 1);
                        if ($ep !== false)
                        {
                            $params[$name] = substr($data, 1, $ep - 1);
                            $data = trim(substr($data, $ep + 1));
                        }
                        else
                        {
                            $data = ''; // malformed. give up
                        }
                    }
                    else
                    { // find next space
                        $ep = strcspn($data, "\r\n\t ");
                        if ($ep !== false)
                        {
                            $params[$name] = substr($data, 0, $ep);
                            $data = trim(substr($data, $ep + 1));
                        }
                        else
                        {
                            $params[$name] = $data;
                            $data = '';
                        }
                    }
                }
                else
                {
                    $params[$name] = true;
                }
            }
            else
            {
                $data = ''; // malformed. giveup
            }
        }
        return $params;
    }
}

