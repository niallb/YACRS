var fill = d3.scaleOrdinal(d3.schemeCategory10);

function generateCloud(words, width, height)
{
    document.getElementById('responses').hidden = true;
    layout = d3.layout.cloud()
     .size([width, height])
     .words(words)
     .padding(5)
     .rotate(function () { return ~~(Math.random() * 2) * 90; })
     .fontSize(function (d) { return d.size; })
     .on("end", draw);

    layout.start();
}

function draw(words)
{
    d3.select("#wordcloud").append("svg")
       //.attr("width", layout.size()[0])
       //.attr("height", layout.size()[1])
       .attr("viewBox", "0 0 1000 600")
      .append("g")
      .attr("transform", "translate(" + layout.size()[0] / 2 + "," + layout.size()[1] / 2 + ")")
      .selectAll("text")
      .data(words)
      .enter()
      .append("text")
      .style("font-size", function (d) { return d.size + "px"; })
      .style("font-family", "Roboto")
      .style("fill", function (d, i) { return fill(i); })
      .attr("text-anchor", "middle")
      .attr("transform", function (d)
      {
          return "translate(" + [d.x, d.y] + ")rotate(" + d.rotate + ")";
      })
      .text(function (d) { return d.text; })
      .on("click", function (d, i)
      {
          document.getElementById('wordcloud').hidden = true;
          cloudLinkClick(d.url);
      });
}

function cloudLinkClick(url)
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
                    processCloudLinkResponse(response);
                } catch (e)
                {
                    alert("JSON parse error in ajaxLinkClick\nURL: " + url + "\n" + e + "\n\n" + xmlHttp.responseText);
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

function hideResponses()
{
    document.getElementById('responses').hidden = true;
    document.getElementById('wordcloud').hidden = false;
}

function processCloudLinkResponse(response)
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
            {
                document.getElementById(name).innerHTML = response[name];
                document.getElementById(name).hidden = false;
            }
        }
    }
}