<?php

/*****************************************************************************
YACRS Copyright 2013, University of Glasgow.
Written by Niall S F Barr (niall.barr@glasgow.ac.uk, niall@nbsoftware.com)

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

require_once('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
require_once('lib/questionTypes.php');
//$uinfo = checkLoggedInUser();

$sqs = systemQuestionLookup::all();
foreach($sqs as $sq)
{
   $theQu = question::retrieve_question($sq->qu_id);
   $theQu->definition->displayStem = false;
   $theQu->definition->displayTitle = true;
   $theQu->definition->stem = $theQu->title;
   $theQu->update();
   echo '<pre>';
   print_r($theQu);
   echo '</pre>';
}

function createBasicGlobalQuestion($title, $definition)
{
    $qu = new basicQuestion($title, false, $definition);
    $qu->displayStem = true;
    $qu->stem = "";
    $theQu = new question();
	$theQu->title = $title;
	$theQu->multiuse = true;
    $theQu->definition = $qu;
    $theQu->insert();
    $qlu = new systemQuestionLookup();
    $qlu->qu_id = $theQu->id;
    $qlu->name = $theQu->title;
    $qlu->insert();
}

?>
