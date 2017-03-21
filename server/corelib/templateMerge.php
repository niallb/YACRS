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

class templateMerge {//start class
  var $fields;
  var $dataNames;
  var $pageData;

  function templateMerge($templateFile) {// start templateMerge()
    $this->pageData=array();
    $src=file_get_contents($templateFile);
    $tmpfields=explode("<%", $src);
    $this->fields=array();
    $this->fields[]="echo " . $tmpfields[0];
    for ($i=1; $i<count($tmpfields); $i++) {// start FOR loop
      $this->fields[]=substr($tmpfields[$i], 0, strpos($tmpfields[$i], "%>"));
      $this->fields[]="echo " . substr($tmpfields[$i], strpos($tmpfields[$i], "%>")+2);
    }// end FOR loop
    
    for ($i=0; $i<count($this->fields); $i++) {// start FOR loop

      if (strpos($this->fields[$i], " ")!==false) {
        list($type, $fieldinfo)=explode(" ", $this->fields[$i], 2);
      } else {
        $type=$this->fields[$i];
        $fieldinfo='';
      }

      if ($type!='echo') {
        $params=$this->decodeParams($fieldinfo);
        if(array_key_exists('name', $params)) {
          $dataname=$params['name'];
          $this->dataNames[]=$dataname;
        }
      }

    }// end FOR loop
  }// end templateMerge()

  function render() {// start render()
    $out='';
    for ($i=0; $i<count($this->fields); $i++) {// start FOR loop
      if (strpos($this->fields[$i], ' ')!==false) {
        list($type, $fieldinfo)=explode(' ', $this->fields[$i],2);
      } else {
        $type=$this->fields[$i] . '!!!!!!!!!!!!!!!!!!!';
        $fieldinfo='';
      }

      switch ($type) {
        case 'echo':
          $out.=$fieldinfo;
          break;
        case 'section':
          $out.=$this->sectionRender($fieldinfo);
          break;
      }
    }// end FOR loop
    return $out;
  }// end render()

  function sectionRender($fieldinfo) {// start sectionRender()
    $out='';
    $params=$this->decodeParams($fieldinfo);
    if (array_key_exists('name', $params)) {
      $dataname=trim($params['name']);
      if (array_key_exists($dataname, $this->pageData)) {
        $out.=$this->pageData[$dataname];
        if (array_key_exists('div_id', $params)) {
          $out='<div id="' . $params['div_id'] . '">' . $out . '</div>';
        } elseif (array_key_exists('div_class', $params)) {
          $out='<div class="' . $params['div_class'] . '">' . $out . '</div>';
        }
      }
    }
    
    return $out;
  }// end sectionRender)

  function decodeParams($data) {//start decodeParams()
    $params=array();
    // eg name="BannerAds_468x60_as" div_id="bannerAds"
    // or  name="Ads_728x90_as" <#start> <div id="horizontalAds"> <#content> </div> <#end>
    
    // first find if there's a <#start> <#content> <#end> section
    if (strpos($data, '<#start>')!==false) {
      list($data, $merge)=explode('<#start>', $data, 2);
      $params['merge']='<#start>' . $merge;
    }
    $data=trim($data);
    while (strlen($data)>0) {// start WHILE loop
      $sp=strcspn($data, "=\"'\r\n\t ");

      if ($sp>0) {
        $name=substr($data, 0, $sp);
        $data=trim(substr($data, $sp));

        if (substr($data, 0, 1)=='=') {
          $data=trim(substr($data, 1));
          // if " or ' find match
          $delim=substr($data, 0, 1);

          if ($delim=='"' || $delim=="'") {
            $ep=strpos($data, $delim, 1);

            if ($ep!==false) {
              $params[$name]=substr($data, 1, $ep-1);
			  $data=trim(substr($data, $ep+1));
            } else {
              $data='';// malformed. give up
            }// end if ($ep!==false)

          } else {// find next space
            $ep=strcspn($data, "\r\n\t ");

            if ($ep!==false) {
              $params[$name]=substr($data, 0, $ep);
              $data=trim(substr($data, $ep+1));
            } else {
              $params[$name]=$data;
              $data='';
            }// end if ($ep!==false)

          }// end if ($delim=='"' || $delim=="'")

        } else {
          $params[$name]=true;
        }// end if (substr($data, 0, 1)=='=')

      } else {
        $data='';// malformed. giveup
      }// end if ($sp>0)

    }// end WHILE loop
    
    return $params;
  }// end decodeParams()

}// end class
?>
