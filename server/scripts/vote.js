window.onload = function()
{
    //document.getElementById('questionBlock').style.border = "thick solid #00F0FF";
    prepareAjax();
}

function prepareAjax()
{
    if(document.getElementById('submitButton'))
    {
        document.getElementById('submitButton').addEventListener("click", onSubmit);
    }
}

function onSubmit(e)
{
    //alert("Submitting");
    e.preventDefault();
    document.getElementById('submitButton').disabled = true;

    if (document.forms['questionForm'] != undefined)
    {
        frm = document.forms['questionForm'];
        data = new FormData(frm);
        data.append('submitans', 'submit');

        //document.getElementById('questionForm').style.visibility = "hidden";
        disableform('questionForm');


        document.body.style.cursor = 'wait';
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function ()
        {
            if (xmlHttp.readyState == 4)
            {
                if (xmlHttp.status == 200)
                {
                    try
                    {
                        var response = JSON.parse(xmlHttp.responseText);
                        processAjaxResponse(response);
                        clearTimeout(timeoutID);
                    } catch (e)
                    {
                        alert("JSON parse error in ajaxLinkClick\n\n" + e + "\n\n" + xmlHttp.responseText);
                    }
                }
                else
                {
                    alert("Http response code " + xmlHttp.status + " when retrieving ajax/vote.php");
                }
                document.body.style.cursor = '';
            }
        }
        timeoutID = setTimeout(timeOutWarning, 15000);
        xmlHttp.open("POST", "ajax/vote.php", true);
        xmlHttp.send(data);
        if (document.getElementById('quStatusMsg') == undefined)
            document.getElementById('questionBlock').innerHTML += "<div id='quStatusMsg'></div>";
        document.getElementById('quStatusMsg').innerHTML = "<div id='submitedMsg' class='alert alert-warning'>Response submitted. Please wait.</div>";
    }
    return false;
}

function timeOutWarning()
{
    document.getElementById('quStatusMsg').innerHTML = "<div id='submitedMsg' class='alert alert-danger'>Error: No response confirmation, your answer may not have been saved.</div>";
    enableform('questionForm');
}

// Found at http://www.rgagnon.com/jsdetails/js-0139.html
function disableform(formId)
{
    var f = document.forms[formId].getElementsByTagName('input');
    for (var i = 0; i < f.length; i++)
        f[i].disabled = true
    var f = document.forms[0].getElementsByTagName('textarea');
    for (var i = 0; i < f.length; i++)
        f[i].disabled = true
}

function enableform(formId)
{
    var f = document.forms[formId].getElementsByTagName('input');
    for (var i = 0; i < f.length; i++)
        f[i].disabled = false
    var f = document.forms[0].getElementsByTagName('textarea');
    for (var i = 0; i < f.length; i++)
        f[i].disabled = false
}

function processAjaxResponse(response)
{
    for (name in response)
    {
        if (name == 'alert')
        {
            alert(response[name]);
        }
        else if (name == 'location')
        {
            window.location = response[name];
        }
        else if (document.getElementById(name) != null)
        {
            if (document.getElementById(name).tagName.toLowerCase() == "input")
                document.getElementById(name).value = response[name];
            else
                document.getElementById(name).innerHTML = response[name];
            document.getElementById(name).style.visibility = "initial";
        }
    }
}
