<html>

<head>
  <title></title>
</head>

<body>

<?php
include('config.php');
include('lib/database.php');

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

echo "Done<br/>";


?>

</body>

</html>
