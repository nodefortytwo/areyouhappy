<?php

class Session
{
    private $sid;
    public $username, $created;

    public function __construct() {
        $sid = session_id();
        if (empty($sid) && !empty($_COOKIE['PHPSESSID'])){
            $sid = $_COOKIE['PHPSESSID'];
            session_id($sid);
        }
        
        if (empty($sid)) {
            $this->sid = 'tmp_' . md5(time());
        } else {
            $this->sid = session_id();
            $this->load();
            $this->save();
        }

    }

    public function load() {
        $db = db()->dquery('SELECT * FROM session WHERE sid = ":sid"');
        $db->arg(':sid', $this->sid);
        $res = $db->execute()->fetch_all();
        if ($this->indb()) {
            $data = unserialize($res[0]['data']);
            if (is_object($data)) {
                foreach ($data as $key => $val) {
                    $this->store($key, $val);
                }
            }
        } else {
            $this->username = '';
            $this->created = time();
        }
    }

    public function save() {
        global $db;
        $args = array(':sid' => $this->sid, ':username' => $this->username, ':created' => $this->created, ':updated' => time(), ':data' => serialize($this));
        if ($this->indb()) {
            $db->dquery('UPDATE session SET username = ":username", created = ":created", updated = ":updated", data = ":data" WHERE sid = ":sid"')->arg($args)->execute();
        } else {
            $db->dquery('INSERT INTO session (sid, username, created, updated, data) VALUES (":sid", ":username", ":updated", ":updated", ":data")')->arg($args)->execute();
        }

    }

    public function indb() {
        global $db;

        $db->dquery('SELECT * FROM session WHERE sid = ":sid"');
        $db->arg(':sid', $this->sid);
        $res = $db->execute()->fetch_all();

        if (empty($res)) {
            return false;
        } else {
            return true;
        }
    }

    public function store($key, $val) {
        $this->$key = $val;
        $this->save();
    }

    public function delete() {
        db()->dquery('DELETE FROM session WHERE sid = ":sid"')->arg(':sid', $this->sid)->execute();
    }

    public function start() {
        session_start();
        $_SESSION = new Session();
    }

}
