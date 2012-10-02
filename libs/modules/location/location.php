<?php
function location_install(){
    require('location.install.php');
}

function location_init() {
    include ('libs/FoursquareAPI.class.php');
    include ('location.class.php');
    //    $test = location_4sq_search('High Holborn', array('4f2a25ac4b909258e854f55f'))->response->venues;

}

function location_routes() {
    $routes = array();
    $routes['location/search'] = array('callback' => 'location_search');
    return $routes;
}

function location_form_widget($id, $name, $default = '', $width = 'span6', $options = array(), $description = '') {
    if (!is_object($default)) {
        $default = new Location($default);
    }
    if (empty($default->id)){
        $user = new User();
        $default->lat = $user->last_location->coords->latitude;
        $default->lng = $user->last_location->coords->longitude;
    }
    $widget = '<div class="control-row">';
    //$widget .= template_form_item($id . '_lat', '', 'text', $default, $width, array('readonly' => true));
    //$widget .= template_form_item($id . '_lng', '', 'text', $default, $width, array('readonly' => true));
    $widget .= template_form_item($id . '_query', '', 'search', $default->name);
    $widget .= '<input id="' . $id . '_id" name="' . $id . '_id" type="hidden" value="' . $default->id . '"/>';
    $widget .= '</div>';
    $widget .= '<div class="row-fluid" style="position:relative;">';
    $widget .= '<div class="control-row">';
    $widget .= '<img src="' . 'https://ss0.4sqi.net/img/devsite/img_poweredby-181a0c7c0fe5f3576d97bcf29ce69d24.png' . '" height=20 class="pull-right" style="margin-right:-10px"/>';
    $widget .= '<div class="row-fluid"><div class="span12 location_results" id="location_results"></div></div>';
    $widget .= '</div>';
    $widget .= '</div>';
    $widget .= '<div class="row-fluid">';
    $widget .= '<div class="control-row map_field span12 well" id="' . $id . '_map" data-lat="' . $default->lat . '" data-lng="' . $default->lng . '">';
    $widget .= '</div>';

    $widget .= '</div>';
    return $widget;
}

function location_search($query, $limit = 50, $cats = '') {
    $cats = explode(',', $cats);
    return json_encode(location_4sq_search($query, $cats, $limit)->response->venues);
}

function location_4sq_search($query, $cats = array(), $limit = 50) {
    $foursquare = new FoursquareApi(FOUR_SQ_API_KEY, FOUR_SQ_API_SECRET);
    $params = array('query' => $query, 'intent' => 'global', 'limit' => $limit);
    $user = new User();
    if (is_object($user->last_location)) {
        $params['ll'] = $user->last_location->coords->latitude . ', ' . $user->last_location->coords->longitude;
        unset($params['intent']);
    }

    $response = json_decode($foursquare->GetPublic('venues/search', $params));
    if (count($response->response->venues) == 0){
        unset($params['ll']);
        $params['intent'] = 'global';
        $response = json_decode($foursquare->GetPublic('venues/search', $params));    
    }
    if (!empty($cats)) {
        foreach ($response->response->venues as &$v) {
            $remove = true;
            foreach ($v->categories as $c) {
                foreach ($cats as $cat) {
                    if ($cat == $c->id) {
                        $remove = false;
                    }
                }
            }
            if ($remove) {unset($v);
            }
        }
    }
    return $response;
}
