<html>

<head>
  <title></title>
</head>

<body>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('config.php');
require_once('lib/database.php');
require_once('lib/forms.php');
require_once('lib/shared_funcs.php');
require_once('lib/questionTypes.php');

echo "YACRS setup<br/>";

if($DBCFG['type']=='SQLite')
{
    if(!file_exists($DBCFG['file']))
    {
        initializeDataBase_yacrs();
    }
	else
	{
	    echo "Database already exists<br/>";
	}
}
else // MySQL database
{
    initializeDataBase_yacrs();
}

createBasicGlobalQuestion("MCQ A-D", "A\nB\nC\nD\n");
createBasicGlobalQuestion("MCQ A-E", "A\nB\nC\nD\nE\n");
createBasicGlobalQuestion("MCQ A-F", "A\nB\nC\nD\nE\nF\n");
createBasicGlobalQuestion("MCQ A-H", "A\nB\nC\nD\nE\nF\nG\nH");
createBasicGlobalQuestion("MRQ A-D", "*A\n*B\n*C\n*D\n");
createBasicGlobalQuestion("MRQ A-E", "*A\n*B\n*C\n*D\n*E\n");
createBasicGlobalQuestion("MRQ A-F", "*A\n*B\n*C\n*D\n*E\n*F\n");
createBasicGlobalQuestion("MRQ A-H", "*A\n*B\n*C\n*D\n*E\n*F\n*G\n*H");
createTextinputGlobalQuestion();


echo "Done<br/>";

function createBasicGlobalQuestion($title, $definition)
{
    $qu = new basicQuestion($title, false, $definition);
    $theQu = new question();
    $qu->displayStem = false;
    $qu->displayTitle = true;
    $qu->stem = $title;
	$theQu->title = $title;
	$theQu->multiuse = true;
    $theQu->definition = $qu;
    $theQu->insert();
    $qlu = new systemQuestionLookup();
    $qlu->qu_id = $theQu->id;
    $qlu->name = $theQu->title;
    $qlu->insert();
}

function createTextinputGlobalQuestion()
{
    $qu = new ttcQuestion1("Text input", false, 0, 0);
    $theQu = new question();
    $qu->displayStem = false;
    $qu->displayTitle = true;
	$theQu->title = "Text input";
	$theQu->multiuse = true;
    $theQu->definition = $qu;
    $theQu->insert();
    $qlu = new systemQuestionLookup();
    $qlu->qu_id = $theQu->id;
    $qlu->name = $theQu->title;
    $qlu->insert();
}


?>

</body>

</html>
