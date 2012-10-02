<?php
function session_schema() {
    $schema = array();
    $schema['session'] = "CREATE TABLE `session` (
                              `sid` varchar(255) NOT NULL DEFAULT '',
                              `username` varchar(255) DEFAULT NULL,
                              `created` int(11) DEFAULT NULL,
                              `updated` int(11) DEFAULT NULL,
                              `data` longblob,
                              PRIMARY KEY (`sid`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    return $schema;
}

function session_init() {
    require ('session.class.php');
    return true;
}

function session(){
    global $session;
    if (!is_object($session)){
        $session = new Session();
    }
    return $session;
}
