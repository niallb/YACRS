<?php

function getUBlogUpdateAJAXScript($sessionID)
{
	return "<script lang=\"JavaScript\">
function httpGet(theUrl)
{
    var xmlHttp = null;

	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlHttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlHttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
	  }
    xmlHttp.open( \"GET\", theUrl, false );
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

UpdatePosts({$sessionID});
window.setInterval(function(){UpdatePosts({$sessionID})},500);

function UpdatePosts(id)
{
    var updateURL = 'getposts.php?sessionID='+id+'&nocache='+new Date().getTime();
    var text = httpGet(updateURL);
    document.getElementById('messages').innerHTML = text;
}

</script>";
}



function getLiveResponseUpdateAJAXScript($sessionID)
{
	return "<script lang=\"JavaScript\">
function httpGet(theUrl)
{
    var xmlHttp = null;

	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlHttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlHttp=new ActiveXObject(\"Microsoft.XMLHTTP\");
	  }
    xmlHttp.open( \"GET\", theUrl, false );
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

UpdatePosts({$sessionID});
window.setInterval(function(){UpdatePosts({$sessionID})},1000);

function UpdatePosts(id)
{
    var updateURL = 'liveresponsefeed.php?sessionID='+id+'&nocache='+new Date().getTime();
    var text = httpGet(updateURL);
    document.getElementById('messages').innerHTML = text;
}

</script>";
}
?>
