window.fbAsyncInit = function() {

    FB.init({
        appId : '107695079386810', // App ID
        channelUrl : '//local.areyouhappy.me/facebook/channel', // Channel File
        status : true, // check login status
        cookie : false, // enable cookies to allow the server to access the session
        xfbml : true  // parse XFBML
    });
    getUser(function() {
        FB.getLoginStatus(function(response) {
            if(response.status === 'connected') {
                
                var uid = response.authResponse.userID;
                var accessToken = response.authResponse.accessToken;
                console.log(response.authResponse);
            }
        });
    });
};
function facebookLogin() {
    FB.login(function(response) {
        if(response.authResponse) {
            userRegister(response.authResponse.signedRequest, function(response){
                console.log(response);
            });
        } else {
            console.log('User cancelled login or did not fully authorize.');
        }
    }, {
        scope : 'email'
    });
}