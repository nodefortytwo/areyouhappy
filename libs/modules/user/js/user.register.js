var validation = false;
$(document).ready(function() {
    $('#user_register_submit').click(userRegisterSubmit);

    $('#email').focusout(function() {
        userCheckEmail($(this).val(), function(exists){
            if (exists){
                $('#email').parent('.control-group').addClass('error');
                $('#email').parent('.control-group').children('.help-block').html('Email address in use');
                validation = false;
            }else{
                $('#email').parent('.control-group').removeClass('error');
                $('#email').parent('.control-group').children('.help-block').html('');
                validation = true;
            }
        });
    });
    $('#pseudonym').focusout(function() {
        userCheckName($(this).val(), function(exists){
            if (exists){
                $('#pseudonym').parent('.control-group').addClass('error');
                $('#pseudonym').parent('.control-group').children('.help-block').html('Pseudonym Taken ;(');
                validation = false;
            }else{
                $('#pseudonym').parent('.control-group').removeClass('error');
                $('#pseudonym').parent('.control-group').children('.help-block').html('');
                validation = true;
            }
        });
    })
    $('#cpassword').keyup(function() {
        if($('#password').val() == $('#cpassword').val()) {
            $('#password').parent('.control-group').removeClass('error');
            $('#cpassword').parent('.control-group').removeClass('error');
            $('#password').parent('.control-group').children('.help-block').html('');
            validation = true;
        } else {
            console.log($('#password').parent('.control-group'));
            $('#password').parent('.control-group').addClass('error');
            $('#cpassword').parent('.control-group').addClass('error');
            $('#password').parent('.control-group').children('.help-block').html('Passwords Do Not Match');
            validation = false;
        }
    })
});
function userRegisterSubmit() {
    return pmatch;
}

function userCheckEmail(mail, callback) {
    var path = SYSTEM.BASE_PATH + '/api/user/email_exists/' + '?email=' + mail;
    $.ajax(path, {dataType:'json'}).done(function(data) {
        if (data.status == 200){
            callback(data.response);
        }else{
            console.log('ERROR: userCheckEmail');
            console.log(data);
        }
    });
}

function userCheckName(mail, callback) {
    var path = SYSTEM.BASE_PATH + '/api/user/name_exists/' + '?username=' + mail;
    $.ajax(path, {dataType:'json'}).done(function(data) {
        if (data.status == 200){
            callback(data.response);
        }else{
            console.log('ERROR: userCheckName');
            console.log(data);
        }
    });
}