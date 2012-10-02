<?php
function user_install(){
    require('user.install.php');
}
function user_init() {
    require ('user.class.php');
}
//js file to include on all pages
function user_global_js(){
    return array(
        'js/user.js'
    );
}

function user_routes() {
    $paths = array();

    return $paths;
}

function user_create_form() {
    $page = new Template();
    $page->title = "Register";
    $page->add_js('js/user.register.js', 'user');
    $page->load_template('templates/user.register.tpl.php', 'user');
    $page->title = 'Register for areyouhappy';
    $variables = array();
    $variables['page_title'] = 'Let\'s Get Started';
    $variables['form_intro'] = '<p>' . 'If you can give us some basic information we will get your account set up, you can fill in more details later on.' . '<p>';
    $variables['form_intro'] .= '<p>' . 'When you add stories we will use your Pseudonym however you can use your real name if you prefer.' . '<p>';
    $variables['form_pseudonym'] = template_form_item('pseudonym', 'Pseudonym (username)', 'text', '', 'span12', array(), '');
    $variables['form_email'] = template_form_item('email', 'E-mail', 'text', '', 'span12', array(), '');
    $variables['form_password'] = template_form_item('password', 'Password', 'password', '', 'span12', array(), '');
    $variables['form_cpassword'] = template_form_item('cpassword', 'Confirm', 'password', '', 'span12', array(), '');
    $variables['form_submit'] = template_form_item('user_register_submit', 'Submit', 'submit', '', 'span6 pull-right');
    $variables['register_facebook'] = l('Sign in with Facebook', get_url('/facebook/register'), 'span10 btn btn-primary btn-large btn-social');
    $variables['form_action'] = get_url('/user/create/');
    $page->add_variable($variables);
    return $page->render();
}

function user_create() {
    
    $user = new User();
    $user->set_default();
    $user->username = $_POST['pseudonym'];
    $user->email = $_POST['email'];
    if ($_POST['password'] == $_POST['cpassword']) {
        $user->password = $_POST['password'];
    }
    $user->status = 1;
    
    $created = $user->create();
    if ($created) {
        $user->login($user->email, $_POST['password']);
        redirect('/user/');
    } else {
        redirect('/register/#error');
    }
}

function user_update(){
    $user = new User();
    
    if ($val = get('firstname')){
        $user->firstname = $val;
    }
    if ($val = get('lastname')){
        $user->lastname = $val;
    }
    
    if ($val = get('pseudonym')){
        $user->username = $val;
    }
    
    if ($val = get('age')){
        $user->age = $val;
    }
    
    if ($val = get('gender')){
        $user->gender = $val;
    }
    
    if ($val = get('location_id')){
        $user->location = new Location($val);
    }
    $files = file_upload($_FILES);
    if (!empty($files)){
        $user->picture = $files[0];
    }
    
    $email = get('email');
    $p = get('password');
    $cp = get('cpassword');
    
    if ($email && $p && $cp && ($p == $cp)){
        $user->email = $email;
        $user->password = md5($email . $password);
    }
    
    $user->save();
    redirect('/user/');
}

function user_page($uid = false) {
    $user = new User($uid, true, true);
    //no cache, current, with entities
    $page = new Template();
    $page->title = $user->display_name;
    $page->load_template('templates/user.profile.tpl.php', 'user');
    $vars = array();
    foreach ($user as $key => $value) {
        $vars['user_' . $key] = $value;
    }
    $vars['user_location'] = $vars['user_location']->render('micro');
    $vars['user_edit'] = l('edit profile',get_url('/user/edit'));
    $vars['user_picture'] = $vars['user_picture']->render('thumbnail-large');
    $vars['story_create_url'] = get_url('/story/create');

    foreach ($user->stories as &$story) {
        $story = $story->render();
    }
    $vars['user_stories'] = implode('', $user->stories);
    $page->add_variable($vars);
    return $page->render();
}

function user_edit_page($uid = false){
    $user = new User($uid, true, true);
    //no cache, current, with entities
    $page = new Template();
    $page->title = 'Edit Your Profile';
    $page->load_template('templates/user.edit.tpl.php', 'user');
    $page->add_js('js/location.widget.js', 'location');
    $vars = array();
    $vars['page_title'] = $page->title;
    $vars['form_pseudonym'] = template_form_item('pseudonym', 'Pseudonym (username)', 'text', $user->username, 'span12', array(), '');
    $vars['form_email'] = template_form_item('email', 'E-mail', 'text', $user->email, 'span12', array(), '');
    $vars['form_firstname'] = template_form_item('firstname', 'Firstname', 'text', $user->firstname, 'span12', array(), '');
    $vars['form_lastname'] = template_form_item('lastname', 'Lastname', 'text', $user->lastname, 'span12', array(), '');
    $vars['form_age'] = template_form_item('age', 'Age', 'text', $user->age, 'span12', array(), '');
    $vars['form_profile'] = template_form_item('profile', 'Profile', 'html', '', '', array('rows' => '11'));
    $vars['form_gender'] = template_form_item('gender', 'Gender', 'select', $user->gender, 'span12', array('male'=>'Male','female'=>'Female'), '');
    $vars['form_location'] = template_form_item('location', 'Location', 'location', $user->location, '', array());
    $vars['form_password'] = template_form_item('password', 'Password', 'password', '', 'span12', array(), '');
    $vars['form_cpassword'] = template_form_item('cpassword', 'Confirm', 'password', '', 'span12', array(), '');
    $vars['form_picture'] = template_form_item('picture', 'Picture', 'image', $user->picture, 'span12', array(), '');
    $vars['form_submit'] = template_form_item('user_register_submit', 'Submit', 'submit', '', 'span6 pull-right');
    
    $vars['form_action'] = get_url('/user/update/');
    
    $page->add_variable($vars);
    return $page->render();
}

function user_page_access($path = '') {
    $user = new User();
    if ($user->uid > 0) {
        return true;
    } else {
        return false;
    }
}

function user_login_form() {
    $message = '';
    $user = new User();
    if ($user->uid > 0) {
        redirect('/user/', true);
    }
    if (isset($_POST['form_id']) == 'login') {
        $user->login($_POST['email'], $_POST['password']);
        if ($user->uid == 0) {
            message("The username or password you entered was inccorect or your account has not been activated yet", 'error');
        } else {
            redirect('/user/', 301, true);
        }
    }
    $form = '<div class="row-fluid">';
    $form .= '<div class="span5">';
    $form .= '<form method="post" name="login" id="login" class="form">';
    $form .= '<input type="hidden" name="form_id" value="login" id="form_id"/>';
    $form .= '<div class="controls controls-row">';
    $form .= template_form_item('email', 'E-Mail', 'text', '', 'span6');
    $form .= template_form_item('password', 'Password', 'password', '', 'span6');
    $form .= '</div>';
    $form .= '<div class="controls controls-row">';
    $form .= template_form_item('user_login_submit', 'Submit', 'submit', '', 'span2 pull-right');
    $form .= '</div>';
    $form .= '</form>';
    $form .= '</div>';
    $form .= '</div>';
    $page = new Template();
    $page->title = 'Login';
    $page->c('<h1>' . 'Login' . '</h1>');
    $page->c('<p>' . 'Enter your username and password below to login in, if you don\'t have an account yet click ' . l('Register', '/register') . '</p>');
    $page->c($form);
    return $page->render();
}

function user_login_block() {
    $user = new User();
    $links = array();
    $links['d0'] = array('text' => '', 'class' => 'divider-vertical');
    if ($user->uid) {
        $links['your-account'] = l('Your Account', '/user');
        $links['d1'] = array('text' => '', 'class' => 'divider-vertical');
        $links['logout'] = l('Logout', '/logout') . '</li>';
    } else {
        $links['login'] = l('Login', '/login', 'button');
        $links['register'] = l('Register', '/register', 'button');
    }

    return template_list($links, 'nav');
}

function user_logout() {
    $user = new User();
    $user->logout();
}

function user_email_exists($email) {
    if (empty($email)) {
        return false;
    }
    $res = db()->dquery('SELECT uid FROM user WHERE email = ":email"')->arg(':email', $email)->execute()->fetch_single();
    if (empty($res)) {
        return false;
    } else {
        return $res['uid'];
    }
}

function user_name_exists($username) {
    if (empty($username)) {
        return false;
    }
    $res = db()->dquery('SELECT uid FROM user WHERE username = ":username"')->arg(':username', $username)->execute()->fetch_single();
    if (empty($res)) {
        return false;
    } else {
        return $res['uid'];
    }
}

function user_menu_register_callback() {
    $user = new User();
    if ($user->uid == 0) {
        return true;
    } else {
        return false;
    }
}

function user_valid_access_token($access_token){
    
}

function user_get_json(){
    $user = new User();
    return json_encode($user);
}

function user_name_exists_json() {
    if ($param = get('username')) {
        $response = array('status' => 200, 'response' => user_name_exists($param));
    } else {
        $response = array('status' => 500, 'response' => 'missing arguments');
    }
    return json_encode($response);
}

function user_email_exists_json() {
    if ($param = get('email')) {
        $response = array('status' => 200, 'response' => user_email_exists($param));
    } else {
        $response = array('status' => 500, 'response' => 'missing arguments');
    }
    return json_encode($response);
}

function user_update_location_json(){
    $location = get('location');

    if (is_array($location)) {
        $user = new User();
        if ($user->uid > 0){
            $res = $user->update_location($location);
            if ($res){
                $response = array('status' => 200, 'response' => 'Location Updated');
            }else{
               $response = array('status' => 500, 'response' => 'Location Update Failed'); 
            }
        }else{
            $response = array('status' => 403, 'response' => 'access denied');
        }
    } else {
        $response = array('status' => 500, 'response' => 'missing arguments', 'xtra' => print_r($_POST, true));
    }
    return json_encode($response);
}

?>