/**
 * User: Hamza Tanveer
 * YALIS Update
 */

/*
* this function is triggered when form is submitted
* */
$('#add_questionChat').on('submit', function (e) {
    e.preventDefault();

    if ($('textarea[name="chatMessage"]').val()){
        $.ajax({
            type: 'POST',
            url: 'ajax_questionChat.php',
            data: $('form').serialize(),
            success: function (data) {
                console.log(data);
                $('textarea[name="chatMessage"]').val("");
            },
            failure: function (data) {
                alert(data);
            }
        });
    } else {
        alert("Please add message!");
    }
});

/*
 * function to select the best answer
 */
function selectBestAsnwer(questionId, messageId) {
    $.ajax({
        type: 'POST',
        url: 'ajax_setBestAnswer.php',
        data: {
            qID : questionId,
            mID : messageId
        },
        success: function (data) {
            console.log(data);
            location.reload();
        },
        failure: function (data) {
            alert(data);
        }
    });
}