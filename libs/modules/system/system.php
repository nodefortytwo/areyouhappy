<?php
//this module exists just to set-up the system and ensure tables are as they should be

function system_schema($tables) {
    $schema = array();
    $schema['variable'] = "CREATE TABLE `variable` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) DEFAULT NULL,
                              `value` varchar(255) DEFAULT NULL,
                              `expires` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    $schema['log'] = "CREATE TABLE `log` (
                          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                          `source` varchar(255) DEFAULT NULL,
                          `text` longtext,
                          `level` varchar(255) DEFAULT NULL,
                          `created` int(11) DEFAULT NULL,
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    $schema['modules'] = "CREATE TABLE `modules` (
                              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                              `name` varchar(255) DEFAULT NULL,
                              `weight` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    return $schema;
}

function system_update_0() {
    $paths = array(UPLOAD_PATH . '/js/', UPLOAD_PATH . '/css/', UPLOAD_PATH . '/thumbnails/');
    foreach ($paths as $p) {
        if (!file_exists($p)) {
            mkdir($p);
        }
    }
}

function system_routes() {
    return array('cache/clear' => array('callback' => 'system_clear_cache_page'));
}

function system_clear_cache_page() {
    system_clear_cache();
    $page = new Template();
    $page->title = 'Caches Cleared and Rebuilt';
    $page->c('<h1>' . $page->title . '</h1>');
    return $page->render();
}

function system_clear_cache() {
    $paths = array(UPLOAD_PATH . '/js/', UPLOAD_PATH . '/css/');
    foreach ($paths as $path) {
        foreach (glob($path.'*.*') as $v) {
            unlink($v);
        }
    }

    var_set('CACHE_KEY', md5(time()));

}

function cwd(){
    $r = SITE_ROOT;
    if (!empty($r)){
        return str_replace('/'.$r, '', getcwd());
    }else{
        return getcwd();
    }
}

function cache_key() {
    if ($key = var_get('CACHE_KEY', false)) {
        return $key;
    } else {
        $key = md5(time());
        var_set('CACHE_KEY', $key);
        return $key;
    }
}
