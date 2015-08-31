<?php

header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

include_once('config.php');
include_once('lib/database.php');

$qi = questionInstance::retrieve_questionInstance($_REQUEST['qiID']);
$qi->title = $_REQUEST['text'];
$qi->update();
echo $qi->title;

?>
