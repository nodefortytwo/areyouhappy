<?php
function db() {
    global $db;
    //if (!is_object($db)){
    $db = new Database();
    //}
    return $db;
}

class Database
{
    protected $connection, $result;

    private $sql, $vars = array();

    public $log = false;

    public function __construct($db = MYSQL_DB) {
        $this->connection = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die('Could not connect: ' . mysql_error());

        if ($db) {
            $this->select_db($db);
        }
    }

    public function query($sql) {
        $this->sql = $sql;
        if ($this->result = mysql_query($sql, $this->connection)) {

        } else {
            print($sql . "\n<br/>\n");
            elog('Query failed: ' . mysql_error() . '
            SQL: ' . $this->sql, 'error');
            die('Query failed: ' . mysql_error());
        }
        if ($this->log) {
            log_query($this->sql, $this->vars);
        }
        return $this;
    }

    public function fetch_all() {
        $result = $this->result;
        if (!$this->result) {
            return false;
        }

        if (mysql_num_rows($this->result) == 0) {
            return array();
        }

        while ($row = mysql_fetch_assoc($this->result)) {
            foreach ($row as &$field) {
                if ($field === 'null') {$field = null;
                }
            }
            $results[] = $row;
        }

        return $results;

    }

    public function fetch_single() {

        if (!$this->result) {
            return false;
        }
        if (mysql_num_rows($this->result) >= 1) {
            return mysql_fetch_assoc($this->result);
        } else {
            return array();
        }

    }

    public function fetch_col($col) {
        $res = $this->fetch_all();
        $ret = array();
        foreach ($res as $row) {
            $ret[] = $row[$col];
        }
        return $ret;
    }

    public function last_id() {

        return (mysql_insert_id($this->connection));

    }

    public function dquery($sql) {
        unset($this->result);
        $this->vars = array();
        $this->sql = $sql;
        return $this;
    }
    
    public function update($table, $args){
        foreach($args as $key=>$val){
            if (is_null($val) && empty($val)){
                unset($args[$key]);
            }
        }
        
        $fields = $vals = array_keys($args);
        $fields = str_replace(':', '', $fields);
        
        $update=array();
        foreach($fields as $key=>$f){
            $update[] = ' ' . $f . '=":' . $f . '"';
        }
        $update = implode(', ', $update);
        
        $fields = implode(',', $fields);
        $vals = implode('","', $vals);
        
        $sql = 'INSERT INTO ' . $table . ' (' . $fields . ') VALUES ("'.$vals.'") ON DUPLICATE KEY UPDATE';
        
        $sql .= $update;

        $this->dquery($sql)->arg($args)->execute();
        return $this;
    }

    public function arg($arg, $value = '') {
        if (is_array($arg)) {
            $this->vars = array_merge_recursive($this->vars, $arg);
        } else {
            $this->vars[$arg] = $value;
        }
        return $this;
    }

    public function execute() {

        //process all vars
        foreach ($this->vars as &$var) {
            if (!is_string($var)) {$var = json_encode($var);
            }
            $var = mysql_real_escape_string($var);
        }

        $this->processed = str_replace(array_keys($this->vars), array_values($this->vars), $this->sql);

        $this->query($this->processed);
        return $this;
    }

    public function select_db($db) {
        $this->db_selected = false;
        if (mysql_select_db($db, $this->connection)) {
            $this->db_selected = $db;
        }
    }

}

function log_query($sql, $vars = array()) {
    $logdb = new Database();
    $logdb->log = false;
    $logdb->dquery('INSERT INTO query_log (`sql`, vars, created) VALUES (":sql", ":vars", :created);');
    $logdb->arg(':sql', $sql);
    $logdb->arg(':vars', $vars);
    $logdb->arg(':created', time());
    //$logdb->execute();

}
?>