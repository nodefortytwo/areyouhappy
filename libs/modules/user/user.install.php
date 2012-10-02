<?php
function user_schema() {
    $schema = array();
    $schema['user'] = "CREATE TABLE `user` (
                              `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `email` varchar(255) DEFAULT NULL,
                              `username` varchar(255) DEFAULT '',
                              `password` varchar(255) DEFAULT NULL,
                              `created` int(11) DEFAULT NULL,
                              `updated` int(11) DEFAULT NULL,
                              `deleted` int(11) DEFAULT NULL,
                              `status` int(11) DEFAULT '0',
                              `firstname` varchar(255) DEFAULT NULL,
                              `lastname` varchar(255) DEFAULT NULL,
                              `profile` text,
                              `location` varchar(255) DEFAULT NULL,
                              `age` int(11) DEFAULT NULL,
                              `picture` varchar(255) DEFAULT NULL,
                              `gender` varchar(10) DEFAULT NULL,
                              `last_location` text,
                              PRIMARY KEY (`uid`)
                            ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;";
    return $schema;

}

function user_update_0(){
    //this should be replaced with a user save but that requires a full bootstrap :(
    $sql = "REPLACE INTO `user` (`uid`, `email`, `username`, `password`, `created`, `updated`, `deleted`, `status`, `firstname`, `lastname`, `profile`, `location`, `age`, `picture`, `gender`)
VALUES
    (2, 'nodefortytwo@gmail.com', 'nodefortytwo', '5133b5ed586f3107c016ab3440d1f251', 1346599169, 1346599169, 0, 1, 'Rick', 'Burgess', '', '4c03d3af187ec9287322b67b', 25, '', 'Male');
    ";
    db()->query($sql);
    
    file_init();
    $file = array(
        'name' => 'default-profile.gif',
        'tmp_name' => cwd() . '/'.PATH_TO_MODULES . '/user/img/default-profile.gif',
        'type' => 'image/gif'
    );
    $f = new File();
    $f->upload($file);
    var_set('DEFAULT_PROFILE_PIC', $f->id);
    
}
