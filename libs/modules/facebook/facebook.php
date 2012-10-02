<?php

function facebook_init() {
    //don't constuct the Facebook Class as it opens a session the cheeky devil
    include ('facebook.class.php');
}

function facebook_routes() {
    $routes = array();
    $routes['facebook/callback'] = array('callback' => 'facebook_callback');
    $routes['facebook/channel'] = array('callback' => 'facebook_channel_file');
    $routes['facebook/register'] = array('callback' => 'facebook_user_register');
    return $routes;
}

function facebook_global_js() {
    return array('http://connect.facebook.net/en_US/all.js', 'js/facebook.js');
}

function facebook_callback() {
    $facebook = new Facebook( array('appId' => FACEBOOK_API_KEY, 'secret' => FACEBOOK_API_SECRET));
    try {
        $fbuser = $facebook->api('/me');
        if ($id = user_email_exists($fbuser['email'])) {
            $user = new User($id);
            $user->facebook = $fbuser['id'];
            $user->save();
            $user->fb_login();
            redirect('/user');
        } else {
            
            $user = new User();
            $user->set_default();
            $user->email = $fbuser['email'];
            $user->password = md5(time());
            $user->status = 1;
            if (isset($fbuser['username'])){
                $user->username = $fbuser['username'];
            }else{
                $user->username = $fbuser['name'];
            }
            $user->firstname = $fbuser['first_name'];
            $user->lastname = $fbuser['last_name'];
            $user->facebook = $fbuser['id'];
            $user->gender = $fbuser['gender'];
            $user->picture = new File();
            $user->picture->load_from_url('https://graph.facebook.com/'.$fbuser['id'].'/picture?type=large');
            $created = $user->create();
            if ($created){
                $user->fb_login();
                redirect('/user');
            }else{
                redirect('/register');
            }
        }
    } catch(Exception $e) {
        redirect('/');
    }
}

function facebook_channel_file() {
    $cache_expire = 60 * 60 * 24 * 365;
    header("Pragma: public");
    header("Cache-Control: max-age=" . $cache_expire);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_expire) . ' GMT');
    die('<script src="//connect.facebook.net/en_US/all.js"></script>');
}

function facebook_user_register() {
    $facebook = new Facebook( array('appId' => FACEBOOK_API_KEY, 'secret' => FACEBOOK_API_SECRET));
    $params = array('scope' => 'email', 'redirect_uri' => get_url('/facebook/callback', true));
    redirect($facebook->getLoginUrl($params), 301, false);
}

function parse_signed_request($signed_request, $secret) {
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);

    // decode the data
    $sig = base64_url_decode($encoded_sig);
    $data = json_decode(base64_url_decode($payload), true);

    if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
        error_log('Unknown algorithm. Expected HMAC-SHA256');
        return null;
    }

    // check sig
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) {
        error_log('Bad Signed JSON signature!');
        return null;
    }

    return $data;
}

function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}
