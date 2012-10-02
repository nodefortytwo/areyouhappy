<?php

function pages_routes() {
    $paths = array();

    $paths['home'] = array('callback' => 'pages_homepage');
    $paths['404'] = array('callback' => 'pages_404');
    $paths['403'] = array('callback' => 'pages_403');
    $paths['500'] = array('callback' => 'pages_500');
    $paths['204'] = array('callback' => 'pages_204');
    $paths['vote'] = array('callback' => 'vote');
    return $paths;
}

function pages_homepage() {
    $page = new Template();
    $page->title = "Are you Happy?";
    $page->add_js('js/pages.index.js', 'pages');

    $page->load_template('templates/pages.home.tpl.php', 'pages');

    return $page->render();
}

function vote() {

    $white_list = array('100000032730162', '61400408', '199709411', '611385121', '648351335', '199709411', '500155329');

    if (empty($_POST)) {
        $_POST['q'] = '1';
        $_POST['uid'] = '61400408';
        $_POST['val'] = '0';
    }
    
    if (!in_array($_POST['uid'], $white_list)){
        $response = array();
        $response['code'] = '0';
        $response['msg'] = 'Your FB ID has not been whitelisted! Tell Rick';
        return json_encode($response);
    }

    $vals = array();
    $vals[':ts'] = mktime(0, 0, 0);
    $vals[':q'] = $_POST['q'];
    $vals[':val'] = $_POST['val'];
    $vals[':uid'] = $_POST['uid'];
    $response = array();
    $answered = db()->dquery('SELECT id FROM votes WHERE question = :q AND uid = ":uid" AND created = :ts')->arg($vals)->execute()->fetch_single();

    if (empty($answered)) {
        db()->dquery('INSERT INTO votes (uid, created, question) VALUES (":uid", :ts, :q)')->arg($vals)->execute();
        db()->dquery('INSERT INTO question_votes (q, val, created) VALUES (:q, :val, :ts)')->arg($vals)->execute();
        $response['code'] = '200';
        $response['msg'] = 'Vote Registered';
    } else {
        $response['code'] = '0';
        $response['msg'] = 'You have already voted today!';
    }
    return json_encode($response);
}

function pages_404() {
    header("HTTP/1.0 404 Not Found");
    $page = new Template();
    $page->title = "Sorry, page not found";
    $img = '/' . SITE_ROOT . '/' . PATH_TO_MODULES . '/pages/img/404.jpg';
    $page->c('<div class="span12">' . '<h1>404 - Page Not Found</h1>');
    $page->c('</div>');
    return $page->render();
}

function pages_204() {
    header("HTTP/1.0 204 No Content");
    return '';
}

function pages_403() {
    header("HTTP/1.0 403 Access Denied");
    $page = new Template();
    $page->title = "Access Denied";
    $img = '/' . SITE_ROOT . '/' . PATH_TO_MODULES . '/pages/img/404.jpg';
    $page->c('<div class="span12">' . '<h1>403 - Access Denied</h1>');
    $page->c('<h2>Move along, Nothing to see here</h2>');
    $page->c('</div>');
    return $page->render();
}

function pages_500() {
    header("HTTP/1.0 500 Server Error");
    $page = new Template();
    $page->title = "Code Error";
    $page->c('<div class="span12">' . '<h1>500 - I appear to have broken the interwebs</h1>');
    $page->c('</div>');
    return $page->render();
}
