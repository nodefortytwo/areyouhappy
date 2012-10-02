<?php

class User
{

    public $uid = 0, $password, $username = '', $created = 0, $updated, $deleted = 0, $email, $status, $firstname = '', $lastname = '', $age, $gender, $location, $profile, $picture = '4f382f08c09c8fa1170ac0097e91110b', $stories = array(), $last_location, $facebook, $twitter, $gplus;
    private $include_entities = false, $cache = true;

    public function __construct($uid = false, $entities = false, $cache = true) {
        $this->include_entities = $entities;
        $this->cache = $cache;
        //if we have a numeric id then we want load that user not the current user
        if (is_numeric($uid)) {
            $this->uid = $uid;
            $this->load();
        } else {
            //current user
            //first check if we have loaded current user before
            global $user;
            if (!is_object($user) && property_exists(session(), 'user')) {
                if (!is_object(session()->user)) {
                    session()->user = unserialize(session()->user);
                }
                $this->uid = session()->user->uid;
                $this->load();
            }

            if (is_object($user)) {
                foreach ($user as $key => $value) {
                    $this->$key = $value;
                }
            }
            //regardless of where we got the info from lets rebuild from the db;
            if (!$cache) {
                $this->load();
            }
        }
        if ($this->include_entities){
            $this->load_stories();
        }
    }

    public function __get($name) {
        if (method_exists($this, ($method = 'get_' . $name))) {
            return $this->$method();
        } else
            return;
    }

    private function load() {
        $sql = 'SELECT * FROM user WHERE status = 1';
        //UID is preference
        if ($this->uid > 0) {
            $sql .= ' AND uid = :uid';
        } else {
            $sql .= ' AND (email = ":email" AND password = ":password")';
        }
        $db = db()->dquery($sql)->arg(':password', $this->password)->arg(':email', $this->email)->arg(':uid', $this->uid)->execute();
        $res = $db->fetch_single();
        if (!empty($res)) {

            foreach ($res as $key => $val) {
                $this->$key = $val;
            }

            $this->uid = (int)$this->uid;

            if (!empty($this->picture) && !is_object($this->picture)) {
                $this->picture = new File($this->picture);
            } else {
                $this->picture = new File(var_get('DEFAULT_PROFILE_PIC', ''));
            }
            if ($this->include_entities) {
                $this->load_stories();
            }
            if (is_string($this->last_location)) {
                $this->last_location = json_decode($this->last_location);
            }
            if (is_string($this->location)) {
                $this->location = new Location($this->location);
            }
        } else {
            //load failed
            $this->set_default();
        }
    }

    public function login($email, $password) {
        elog(func_get_args(), 'notice');
        
        $this->password = md5($email . $password);
        $this->email = strtolower($email);

        $this->load();
        session()->start();
        if (is_object(session()) && $this->uid) {
            session()->store('email', $this->email);
            session()->store('user', serialize($this));
        }

    }

    public function logout() {

        if (is_object(session())) {
            session()->delete();

            redirect('/', 301, true);
        }

    }
    
    public function fb_login(){
        session()->start();
        if (is_object(session()) && $this->uid) {
            session()->store('email', $this->email);
            session()->store('user', serialize($this));
        }
    }

    public function create() {
        if (empty($this->email)) {
            return false;
        }
        if (empty($this->username) && !empty($this->email)) {
            $this->username = array_shift(explode('@', $this->email));
        }

        $this->email = strtolower($this->email);

        $this->created = time();
        $this->updated = time();
        $this->deleted = 0;
        $this->password = md5($this->email . $this->password);

        if (!user_email_exists($this->email) && !user_name_exists($this->username)) {
            $this->save();
            return true;
        } else {
            return false;
        }
    }

    public function save() {

        $args = array();
        foreach ($this as $key => $val) {
            $args[':' . $key] = $val;
        }
        unset($args[':include_entities']);
        unset($args[':cache']);
        unset($args[':stories']);
        if (is_object($this->picture)) {
            $args[':picture'] = $this->picture->id;
        }
        if (is_object($this->location)) {
            $args[':location'] = $this->location->id;
        }
        //var_dump($this);

        $args[':updated'] = time();

        //for a save we need either a uid or a username and password
        if ($this->uid == 0 && (empty($this->username) || empty($this->password))) {
            return false;
        }

        db()->update('user', $args);
    }

    public function load_stories() {
        $sql = 'SELECT sid FROM story WHERE author = :uid';
        $stories = db()->dquery($sql)->arg(':uid', $this->uid)->execute()->fetch_col('sid');
        foreach ($stories as &$story) {
            $story = new Story($story);
        }
        $this->stories = $stories;
    }

    public function get_display_name() {
        if (!empty($this->firstname) && !empty($this->lastname)) {
            return $this->firstname . ' ' . $this->lastname;
        } else {
            return $this->username;
        }
    }

    public function update_location($location) {
        if (is_string($location)) {$location = json_decode($location);
        }
        $this->last_location = $location;
        $this->save();
        return true;
    }

    public function set_default() {
        foreach ($this as $key => $val) {
            $this->$key = '';
        }
        $this->username = 'anon';
        $this->uid = 0;
    }

    public function render($mode = 'teaser') {
        $vars = array();
        $vars['user_picture'] = $this->picture->render('thumbnail');
        $vars['user_name'] = $this->display_name;
        $vars['user_location'] = $this->location->render('micro');
        switch($mode) {
            case 'teaser' :
            default :
                $template = new Template(false);
                $template->load_template('templates/user.teaser.tpl.php', 'user');
                $template->add_variable($vars);
                return $template->render();
        }

    }

}
