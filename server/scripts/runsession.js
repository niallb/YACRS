function httpGet(theUrl)
{
    var xmlHttp = null;

    if (window.XMLHttpRequest)
    {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlHttp=new XMLHttpRequest();
    }
    else
    {// code for IE6, IE5
        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
    xmlHttp.open( "GET", theUrl, false );
    xmlHttp.send( null );
    return xmlHttp.responseText;
}

function EditTitle(id)
{
    name = "title"+id;
    document.getElementById(name).innerHTML = "<input type='text' id='edt' size='60' maxlength='76' value='" + document.getElementById('title' + id + '_txt').innerHTML + "'/>"
            + "<a OnClick='UpdateTitle(\"" + id + "\");' aria-label='Update question title'><i aria-hidden='true' class='fa fa-check'></a>";
    document.getElementById('edt').onkeydown = function(e)
    {
        if(e.keyCode == 13)
        {
            UpdateTitle(id);
        }
    };
    return false;
}

function UpdateTitle(id)
{
    var updateURL = 'updateTitle.php?qiID='+id+'&text='+encodeURIComponent(document.getElementById('edt').value);
    var text = httpGet(updateURL);
    var name = "title"+id;
    document.getElementById(name).innerHTML = "<span id='title" + id + "_txt'>" + text + "</span>&nbsp;"
        + "<a OnClick='EditTitle(\"" + id + "\");' aria-label='Edit question title'><i aria-hidden='true' class='fa fa-pencil'></i></a>";
}

function toggle(checked)
{
    checkboxes = document.getElementsByName('qiid[]');
    for(var i=0; i<checkboxes.length; i++)
    {
        checkboxes[i].checked = checked;
    }
}
