<?php
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
header('Pragma: no-cache'); // HTTP 1.0.
header('Expires: 0'); // Proxies.

include_once('config.php');
include_once('lib/database.php');

$count = response::countCompleted($_REQUEST['qiID']);
if($count !== false)
    echo $count;
else
    echo 'ERROR';

?>
