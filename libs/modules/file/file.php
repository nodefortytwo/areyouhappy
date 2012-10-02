<?php
//all of the file handling goes here!

global $file_types;
$file_types = array('gif', 'png', 'jpeg', 'jpg', 'pdf', 'txt');
function file_schema(){
    $schema = array();
    $schema['file'] = "CREATE TABLE `file` (
                          `fid` varchar(255) NOT NULL DEFAULT '',
                          `type` varchar(255) DEFAULT NULL,
                          `width` int(11) DEFAULT NULL,
                          `height` int(11) DEFAULT NULL,
                          `data` text,
                          `created` int(11) DEFAULT NULL,
                          `deleted` int(11) DEFAULT NULL,
                          `path` varchar(255) DEFAULT NULL,
                          PRIMARY KEY (`fid`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
    return $schema;
}

function file_init(){
    global $upload_path;
    $upload_path = cwd() . '/' . UPLOAD_PATH;
    require ('file.class.php');
}

function file_routes(){
    $routes = array();
    $routes['file/thumbnail'] = array(
        'callback' => 'file_thumbnail'
    );
    return $routes;
}

function file_thumbnail($id, $width = 250, $height = false){   
    $image = new File($id);
    $url = $image->get_thumbnail($width, $height);
    header('Content-Type: ' . $image->type);
    die(file_get_contents($url));
}

//Takes a $_FILES array and saves them.
function file_upload($files) {
    global $upload_path;
    $objects = array();
    foreach ($files as $file){
        if (!empty($file['name'])){
            $obj = new file();
            $obj->upload($file);
            $objects[] = $obj;
        }
    }
    return $objects;
}

function file_upload_widget($id, $type, $width = 'span12', $default) {    
    if(is_object($default) && !empty($default->url)){
        $url = $default->url;
    }else{
        $url = 'http://www.placehold.it/200x150/EFEFEF/AAAAAA&text=no+image';
    }
    
    $form = '';
    if ($type == 'image'){
    $form .= '<div class="fileupload fileupload-new" data-provides="fileupload">';
        $form .= '<div class="fileupload-new thumbnail"><img src="'.$url.'" class="'.$width.'"/></div>';
        $form .= '<div class="fileupload-preview fileupload-exists thumbnail"></div>';
        $form .= '<div>';
            $form .= '<span class="btn btn-file"><span class="fileupload-new">Select image</span><span class="fileupload-exists">Change</span><input type="file" name="'.$id.'" id="'.$id.'"/></span>';
            $form .= '<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>';
        $form .= '</div>';
    $form .= '</div>';
    }else{
        $form = 'NOT BUILT THIS YET';
    }
    return $form;
}
