<?php
$rebuild = 0;
if (defined('STDIN')) {
    $_SERVER['HTTP_HOST'] = $argv[1];
    if (isset($argv[2])) {
        $rebuild = $argv[2];
    }
}
$rebuild = 1;
$libs = getcwd() . '/libs/';
$settings = $libs . $_SERVER['HTTP_HOST'] . '.settings.php';
if (file_exists($settings)) {
    require ($settings);
} else {
    require ($libs . 'settings.php');
}
require ($libs . 'misc.php');
require ($libs . 'db.php');
require ($libs . 'core.php');

registerModules();
//allow modules to include install files containing update and schema hooks

//Get all Tables;
$col = 'Tables_in_' . MYSQL_DB;
$tables = db()->query('SHOW tables;')->fetch_col($col);
$query = '';
//this is total db rebuild, should not be used in production;
if ($rebuild) {
    echo 'Running Reuild' . " \n";
    foreach ($tables as $t) {
        $sql = 'DROP TABLE ' . $t . ';';
        db()->query($sql);
    }
    $tables = array();
    
    $query = file_get_contents('db.sql');
    $query = explode(';', $query);
    foreach ($query as $q) {
        echo($q . "\n");
        db()->query(trim($q) . ';');
    }
}

system_clear_cache();
