<?php
$settings = getcwd() .'/libs/'. $_SERVER['HTTP_HOST'] . '.settings.php';
if (file_exists($settings)){
   require($settings); 
}else{
   require('settings.php'); 
}
require('misc.php');
require('db.php');
require('core.php');


// Extract the path from REQUEST_URI.
$request_path = strtok($_SERVER['REQUEST_URI'], '?');
//Force trailing slashes, this should be done in htaccess but this double checks :)
$lastchar = substr($request_path, strlen($request_path)-1);
if ($lastchar != '/'){redirect($request_path . '/', 301, false);}
$base_path_len = strlen(rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/'));
$path = substr(urldecode($request_path), $base_path_len + 1);
if ($path == basename($_SERVER['PHP_SELF'])) {
  $path = '';
}
if (empty($path)){$path = 'home';}

//Find any args
$split = explode('~', $path);
$path = rtrim($split[0], "/");
$args = array();
if (!empty($split[1])){
    $split[1] = trim($split[1], "/");
    $args = explode('/', $split[1]);
}

$_GET = array_merge($_POST, $_GET);
$_POST = array_merge($_POST, $_GET);

registerModules();

//run the init hook, nothing should be returned
exec_hook('init');
$status = 0;
ob_start();
//get the routing from each module, check them against the current path
global $system_routes;
$system_routes = exec_hook('routes');
$status = 404;
foreach($system_routes as $module){
    if (array_key_exists($path, $module)){
        $callback = $module[$path];
        $access = true;
        
        if (isset($callback['access_callback']) && function_exists($callback['access_callback'])){
            $access = call_user_func_array($callback['access_callback'], $args);       
        }
        
        if ($access){
            if (isset($callback['callback']) && function_exists($callback['callback'])){
                $status = 200;
                $returned = call_user_func_array($callback['callback'], $args);
                print $returned;        
            }else{
                $status = 500;
                elog('either callback is not set or is invalid', 'error', 'bootstrap');
            }
        }else{
            $status = 403;
        }
    }
}

$contents = ob_get_contents();

ob_end_clean();
if(empty($contents)){
    $status = 204;
}
if ($status == 200){
    print($contents); 
}else{
    print(call_user_func($system_routes['pages'][$status]['callback']));
}



