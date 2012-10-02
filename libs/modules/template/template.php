<?php

function template_init() {
    require ('template.class.php');
}

function template_routes() {
    return array('theme/js/dynamic' => array('callback' => 'template_dynamic_js'));
}

function template_global_css() {
    $css = array();
    $css[] = 'http://fonts.googleapis.com/css?family=Raleway:100';
    $css[] = 'theme/css/ui-darkness/jquery-ui-1.8.21.custom.css';
    $css[] = 'theme/css/prettify.css';
    $css[] = 'theme/css/wysiwyg-color.css';
    $css[] = 'theme/css/bootstrap-wysihtml5-0.0.2.css';
    $css[] = 'theme/css/jasny-bootstrap.min.css';
    $css[] = 'theme/css/jasny-bootstrap-responsive.min.css';

    return $css;
}

function template_global_js() {
    $js = array();
    $js[] = 'theme/js/wysihtml5-0.3.0.min.js';
    $js[] = 'theme/js/jquery-1.7.2.min.js';

    $js[] = 'theme/js/jquery-ui-1.8.18.custom.min.js';
    $js[] = 'theme/js/modernizr-2.5.2.min.js';
    $js[] = 'theme/js/bootstrap.min.js';
    $js[] = 'theme/js/bootstrap-alert.js';
    $js[] = 'theme/js/bootstrap-button.js';
    $js[] = 'theme/js/bootstrap-carousel.js';
    $js[] = 'theme/js/bootstrap-collapse.js';
    $js[] = 'theme/js/bootstrap-dropdown.js';
    $js[] = 'theme/js/bootstrap-modal.js';
    $js[] = 'theme/js/bootstrap-popover.js';
    $js[] = 'theme/js/bootstrap-scrollspy.js';
    $js[] = 'theme/js/bootstrap-tab.js';
    $js[] = 'theme/js/bootstrap-tooltip.js';
    $js[] = 'theme/js/bootstrap-transition.js';
    $js[] = 'theme/js/bootstrap-typeahead.js';
    $js[] = 'theme/js/bootstrap-wysihtml5-0.0.2.min.js';
    
    //these seem to need to be above the jansy stuff.
    $js[] = 'theme/js/less-1.3.0.min.js';
    //$js[] = 'theme/js/jquery.colorbox-min.js';
    
    //jasny extentions
    $js[] = 'theme/js/jasny-bootstrap.js';
    $js[] = 'theme/js/bootstrap-fileupload.js';

    $js[] = 'theme/js/prettify.js';
    $js[] = 'theme/js/areyouhappy.js';
    $js[] = 'http://maps.googleapis.com/maps/api/js?key=' . MAPS_API . '&sensor=true';
    $js[] = 'http://' . HOST . get_url('/theme/js/dynamic');
    return $js;
}

function template_global_less() {
    $less = array();
    $less[] = 'theme/less/bootstrap.less';
    $less[] = 'theme/less/responsive.less';
    $less[] = 'theme/less/areyouhappy.less';
    //$less[] = 'theme/less/colorbox.less';
    return $less;
}

//this function is used to pass system variables to JS, makes it easier to format ajax call urls and other stuff
function template_dynamic_js() {
    $vars = array();
    $vars['HOST'] = HOST;
    $vars['SITE_ROOT'] = SITE_ROOT;
    $vars['PATH_TO_MODULES'] = PATH_TO_MODULES;
    $vars['BASE_PATH'] = '//' . HOST . '' . SITE_ROOT;

    $js_vars = json_encode($vars);

    //$user = new User();

    $return = 'var SYSTEM' . "\n";
    $return .= 'SYSTEM = eval(' . $js_vars . ')' . "\n";
    return $return;
}

//Theme functions (to be called by other modules)
function template_tabs($tabs = array(), $active = 0) {
    $content = '';
    $top = '';
    $i = 0;
    foreach ($tabs as $id => $tab) {
        if ($i == $active) {$class = 'active';
        }
        $top .= "\t" . '<li><a class="' . $class . '" href="#' . $id . '">' . $tab['title'] . '</a></li>' . "\n";
        $content .= "\t" . '<li id="' . $id . '" class="' . $class . '">' . $tab['content'] . '</li>' . "\n";
        $i++;
        $class = '';
    }
    $return = '<ul class="tabs">' . "\n" . $top . '</ul>' . "\n";
    $return .= '<ul class="tabs-content">' . "\n" . $content . '</ul>';
    return $return;

}

function l($text, $url, $class = '', $root = false, $title = '') {
    if(empty($title)){$title = trim(strip_tags($text));}
    
    if(!empty($title)){$title = 'title="'.trim($title).'"';}
    if(!empty($class)){$class = 'class="'.trim($class).'"';}
    
    $url = get_url($url);
    $return = '<a href="' . $url . '" ' . $class . ' '.$title.'>' . $text . '</a>';
    return $return;
}

function template_list($array, $class = '') {
    $return = '<ul class="' . $class . '">';
    foreach ($array as $key => $item) {
        $class = '';
        if (is_array($item)) {
            if (array_key_exists('class', $item)) {
                $class = 'class="' . $item['class'] . '"';
            }
            if (array_key_exists('text', $item)) {
                $item = $item['text'];
            }
        }
        $return .= '<li id="' . $key . '" ' . $class . '>';
        $return .= trim($item);
        $return .= '</li>';
    }
    $return .= '</ul>';

    return $return;
}

function template_table($headers, $rows, $class = '') {
    $return = '';
    $return .= '<table class="table table-striped table-bordered ' . $class . '">';
    $return .= '<thead>';
    $return .= '<tr>';
    foreach ($headers as $header) {
        $return .= '<th>' . $header . '</th>';
    }
    $return .= '</tr>';
    $return .= '</thead>';
    $return .= '<tbody>';
    foreach ($rows as $row) {
        $return .= '<tr>';
        foreach ($row as $col) {
            $return .= '<td>' . $col . '</td>';
        }
        $return .= '</tr>';
    }
    $return .= '</tbody>';
    $return .= '</table>';
    return $return;
}

function template_form_item($id, $name, $type, $default = '', $class = '', $options = array(), $description = '') {
    if ($type == 'search') {
        $class = 'span10';
    } elseif(empty($class)) {
        $class = 'span12';
    }
    $class .= ' input-large';
    $return = '';
    $ops = '';
    //$return .= '<div class="control-group ' . $type . ' ' . $width . ' columns">' . "\n";
    $return .= '<div class="control-group '  . '">';
    if ($type != 'submit') {
        $return .= "\t" . '<label class="control-label" for="' . $id . '">' . $name . '</label>' . "\n";
    }

    switch($type) {
        case 'select' :
            $return .= "\t" . '<select id="' . $id . '" name="' . $id . '">' . "\n";
            array_unshift($options, '-- select -- ');
            foreach ($options as $key => $opt) {
                $selected = '';
                if ($default == $key || $default == $opt) {
                    $selected = 'selected="selected"';
                }
                if (!is_numeric($key)) {
                    $return .= "\t\t" . '<option value="' . $key . '" ' . $selected . '>';
                } else {
                    $return .= "\t\t" . '<option ' . $selected . '>';
                }
                $return .= $opt . '</option>' . "\n";
            }
            $return .= "\t" . '</select>' . "\n";
            break;
        case 'submit' :
            $class .= ' btn btn-primary';
            $return .= "\t" . '<input type="' . $type . '" id="' . $id . '" name="' . $id . '" value="' . $name . '" class="' . $class . '"/>' . "\n";
            break;
        case 'location' :
            $return .= template_form_widget_location($id, $name, $default, $class, $options, $description);
            break;
        case 'html' :
        case 'textarea' :
            if (array_key_exists('rows', $options)) {

                $ops .= ' rows="' . $options['rows'] . '"';
            }
            $return .= "\t" . '<textarea id="' . $id . '" name="' . $id . '" placeholder="' . $default . '" class="' . $class . '" ' . $ops . '/>'.$default.'</textarea>' . "\n";
            break;
        case 'file' :
        case 'image' :
            $return .= file_upload_widget($id, $type, $class, $default);
            break;
        case 'typeahead':
            if (isset($options['function'])){
                $source = $options['function'];
            }else{
                $source = htmlentities(json_encode($options));   
            }
            $ops = 'data-source="' . $source . '" data-provide="typeahead" data-items="4"';
            $return .= "\t" . '<input style="display:inline-block;" type="text" id="' . $id . '" name="' . $id . '" placeholder="' . $default . '" class="' . $class . ' typeahead" ' . $ops . ' value="'.$default.'"/>' . "\n";
            //die($return);
            break;
        case 'search' :
            $return .= '<div class="input-append"><div class="row-fluid"><div class="span9">';
            $class = ' span12';
        case 'password' :
        case 'text' :
        default :
            $ops = '';
            if (array_key_exists('readonly', $options)) {

                $ops .= ' readonly="readonly"';
            }
            $return .= "\t" . '<input style="display:inline-block;" type="' . $type . '" id="' . $id . '" name="' . $id . '" placeholder="' . $default . '" class="' . $class . '" ' . $ops . ' value="'.$default.'"/>' . "\n";
    }

    if ($type == 'search') {
        $return .= '</div><div class="span3"><button class="btn span12" id="'.$id.'_search">Search</button></div></div></div>';
    }

    //$return .= '</div>';
    $return .= '<div class="help-block">' . $description . '</div>';
    $return .= '</div>' . "\n";

    return $return;

}

function template_form_widget_location($id, $name, $default = '', $width = 'span6', $options = array(), $description = '') {
    return location_form_widget($id, $name, $default, $width, $options, $description);
}

function template_date($date = null) {
    if (is_null($date)) {$date = time();
    }
    if (!is_numeric($date)) {
        $date = strtotime($date);
    }
    $now = time();
    if (($now - $date) > 86400) {
        return date('dS M @ ga', $date);
    } else {
        return template_time_ago($date) . ' ago';
    }
}

function template_time_ago($tm, $rcs = 0) {
    $cur_tm = time();
    $dif = $cur_tm - $tm;
    $pds = array('second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade');
    $lngh = array(1, 60, 3600, 86400, 604800, 2630880, 31570560, 315705600);
    for ($v = sizeof($lngh) - 1; ($v >= 0) && (($no = $dif / $lngh[$v]) <= 1); $v--);
    if ($v < 0)
        $v = 0;
    $_tm = $cur_tm - ($dif % $lngh[$v]);

    $no = floor($no);
    if ($no <> 1)
        $pds[$v] .= 's';
    $x = sprintf("%d %s ", $no, $pds[$v]);
    if (($rcs == 1) && ($v >= 1) && (($cur_tm - $_tm) > 0))
        $x .= time_ago($_tm);
    return $x;
}
