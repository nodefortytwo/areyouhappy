window.fbAsyncInit = function() {
    FB.init({
        appId : '229227537206456', // App ID
        status : true, // check login status
        cookie : true, // enable cookies to allow the server to access the session
        xfbml : true  // parse XFBML
    });
};
var uid;
$(document).ready(function() {
    $('#questions').hide();
    $('#msg').hide();
    FB.getLoginStatus(function(response) {
        console.log(response);
    });
    $('#loginbtn').click(function() {
        FB.login(function(response) {
            if(response.authResponse) {
                console.log('Welcome!  Fetching your information.... ');
                FB.api('/me', function(response) {
                    console.log('Good to see you, ' + response.name + '.');
                    uid = response.id;
                    $('#login').hide();
                    $('#questions').show();

                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        });
    });

    $('#questions .btn').click(function() {
        $('#msg').hide();
        var q = $(this).parent().parent().attr('data-question');
        var val = $(this).attr('data-value');
        var data = {
            q : q,
            val : val,
            uid : uid
        }
        $.ajax({
            method: 'POST',
            url : 'vote/',
            data : data,
            dataType : 'json'
        }).done(function(data){
            $('#msg').show();
            $('#msg .alert').html(data.msg);
        })
    });
});
