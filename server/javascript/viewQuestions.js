/**
 * User: Hamza Tanveer
 * YALIS Update
 */

$( document ).ready(function() {

    //loaders & listeners
    loadPage();
    setListener();

    //word count for question textarea
    var maxLength = 240;
    $('.ask-question-textarea').keyup(function() {
        var strLength = $(this).val().length;
        var remainder = maxLength-strLength;
        $('#chars').text(remainder);
    });

});

function loadPage() {
    setTimeout(function() {
        $(".loading-screen").hide();
    }, 1000);
    setTimeout(function() {
        $(".message-container").css('visibility', 'visible');
        $(".form-container").css('visibility', 'visible');
        //if overflows then scroll to the bottom
        $(".message-container").scrollTop($(".message-container")[0].scrollHeight);
    }, 1000);
}

/*
 * toggle question button
 * opens/closes unimportant questions(small bubbles) on timeline
 */
$(".badge-toggle").click(function (e) {
    var iconClass = $(".badge-toggle").find("i")[0];
    var className = $(iconClass).attr("class");
    if(className.indexOf("slash") < 0){
        $(iconClass).attr("class","fa fa-eye-slash fa-2x");
        $(".ask-question").click();
    } else {
        $(iconClass).attr("class","fa fa-eye fa-2x ");
        $(".button-close").click();
    }
});

/*
 * toggles the sorting on the questions
 * questions can be sorted by importance
 * or by time when they are added
 */
$(".badge-sort").click(function (e) {
    var iconClass = $(".badge-sort");
    var className = $(".badge-sort").attr("class");
    if(className.indexOf("unsort") < 0){
        sort("attention");
        $(iconClass).attr("class","bubble-for-badge badge-sort unsort");
    } else {
        sort("id");
        $(iconClass).attr("class","bubble-for-badge badge-sort");

    }
});

function sort(sortBy) {
    var divList = $(".ask-question");
    if(sortBy.indexOf("attention") == 0) {
        divList.sort(function (a, b) {
            return $(a).data(sortBy) < $(b).data(sortBy) ? 1 : -1;
        });
    } else {
        divList.sort(function (a, b) {
            return $(a).data(sortBy) > $(b).data(sortBy) ? 1 : -1;
        });
    }
    $(".message-container").html(divList);
    setListener();
}

/*
 * to close an unimportant quesiton on timeline
 */
function closeQuestionCard(qId) {
    var qDiv = $(".badge-close-"+qId).parentsUntil($("ask-question"),".col-sm-12");
    var contentDiv = $(qDiv).find('div')[0];
    $(contentDiv).hide();
    $(qDiv).addClass("hide-unImpQuestion");
}

//setting listeners
function setListener() {

    //expand on click
    $(".hide-unImpQuestion").click(function (e) {

        //if close button is pressed!
        var target = e.target.className;
        if(target == "card-badge" || (target.indexOf("bubble-for-badge") == 0)) return false;

        //to create smooth animation  of opening and closing
        var WAIT = 40;

        var cardContent     = $(this).find('.question-content');
        // $(cardContent).show();

        setTimeout(function(){
            $(cardContent).show();
        }, WAIT);

        //toggle to open and close card
        $(this).removeClass("hide-unImpQuestion");

    });

}

/*
* AJAX call on when a question is submitted
*/
$('#add_studentsQuestion').on('submit', function (e) {
    e.preventDefault();
    if ($('textarea[name="question"]').val()) {
        $.ajax({
            type: 'POST',
            url: 'ajax_studentsQuestion.php',
            data: $('form').serialize(),
            success: function (data) {
                console.log(data);
                $(".message-container").animate({ scrollTop: $(".message-container").prop("scrollHeight")}, "slow");
                $('textarea[name="question"]').val("");
                $('#chars').text("240");
            },
            failure: function (data) {
                alert(data);
            }
        });
    } else {
        alert("please add a question!");
    }
});

/*
 * mark/unmark a question important
 */
function plusplusLike(sId,qId,liked) {
    $.ajax({
        type: 'POST',
        url: 'ajax_likeQuestion.php',
        data: {
            sessionID: sId,
            questionID: qId,
            liked: liked
        },
        success: function (data) {
            console.log(data);
            if(liked == 1) {
                $(".badge-question-" + qId).css("color", "#197fcd");
                $(".badge-question-" + qId).css("font-weight", "800");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",0)");
            }
            else {
                $(".badge-question-" + qId).css("color", "#888");
                $(".badge-question-" + qId).css("font-weight", "300");
                $(".badge-question-" + qId).attr("onclick","plusplusLike("+sId+","+qId+",1)");
            }
        },
        failure: function (data) {
            alert(data);
        }
    });
}

