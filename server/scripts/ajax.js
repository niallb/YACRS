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
        }
    }
}

function ajaxLinkClick(url)
{
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
                } catch (e)
                {
                    document.write(xmlHttp.responseText);
                }
            }
            else
            {
                alert("Http response code " + xmlHttp.status + " when retrieving " + url);
            }
            document.body.style.cursor = '';
        }
    }
    xmlHttp.open("GET", url, true);
    xmlHttp.send(null);
}

function ajaxAction(url, senddata)
{
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
                } catch (e)
                {
                    if (confirm("JSON parse error on response from URL:" + url + "\n" + e + "\n\nView the response?"))
                        document.write(xmlHttp.responseText);
                }
            }
            else
            {
                alert("Http response code " + xmlHttp.status + " when retrieving " + url);
            }
            document.body.style.cursor = '';
        }
    }
    if (senddata != undefined)
    {
        xmlHttp.open("POST", url, true);
        if (typeof senddata == 'string') // should be a form element's ID
        {
            data = new FormData(document.getElementById(senddata));
        }
        else
        {
        data = new FormData();
        for (key in senddata)
            data.append(key, senddata[key]);
        }
        xmlHttp.send(data);
    }
    else
    {
        xmlHttp.open("GET", url, true);
        xmlHttp.send(null);
    }
}

// This is used by NBWebsites Form wizard generated ajax forms, which include the URL in the form.
function submitForm(formid, button)
{
    document.body.style.cursor = 'wait';
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function ()
    {
        if (xmlHttp.readyState === 4)
        {
            if (xmlHttp.status == 200)
            {
                try
                {
                    var response = JSON.parse(xmlHttp.responseText);
                    processAjaxResponse(response);
                } catch (e)
                {
                    alert("JSON parse error in submitForm\n" + e + "\n\n" + xmlHttp.responseText);
                    document.write(xmlHttp.responseText);
                }
            }
            else
            {
                alert("Http response code " + xmlHttp.status);
            }
            document.body.style.cursor = '';
        }
    }
    var theform = document.getElementById(formid);
    xmlHttp.open("POST", theform.action, true);
    xmlHttp.setRequestHeader('accept', 'application/json');
    data = new FormData(theform);
    if (button != undefined)
        data.append(button.name, button.value);
    xmlHttp.send(data);
}

