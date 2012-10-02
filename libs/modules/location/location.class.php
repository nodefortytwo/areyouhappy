<?php

class Location
{
    public $id, $name = "", $data, $updated, $lat = 0, $lng = 0;

    private $foursquare;

    public function __construct($id = '', $json = '') {
        $this->foursquare = new FoursquareApi(FOUR_SQ_API_KEY, FOUR_SQ_API_SECRET);

        $this->id = $id;

        if ($this->indb()) {
            $this->load();
        } else {
            $this->load4sq();
        }

    }

    private function indb() {
        $sql = 'SELECT id FROM location WHERE id = ":id"';
        $res = db()->dquery($sql)->arg(':id', $this->id)->execute()->fetch_single();
        if (!empty($res)) {
            return true;
        } else {
            return false;
        }
    }

    private function load4sq() {
        $json = $this->foursquare->GetPublic('venues/' . $this->id);
        $response = json_decode($json);
        if (!is_object($response)){
            elog($json);
            return false;
        }
        if ($response->meta->code != 200) {
            return false;
        }
        $response = $response->response->venue;
        $this->name = $response->name;
        $this->data = $response;
        $this->updated = time();
        $this->lat = $response->location->lat;
        $this->lng = $response->location->lng;

        $this->save();
    }

    private function load() {
        $sql = 'SELECT name, data, updated, AsText(location) as location FROM location WHERE id = ":id"';
        $row = db()->dquery($sql)->arg(':id', $this->id)->execute()->fetch_single();
        if (!empty($row)) {
            $this->name = $row['name'];
            $this->data = $row['data'];
            $this->updated = $row['updated'];
            $loc = explode(' ', str_replace(')', '', str_replace('POINT(', '', $row['location'])));
            $this->lat = $loc[0];
            $this->lng = $loc[1];
        }
    }

    public function save() {
        $sql = 'REPLACE INTO location (id, name, data, updated, location) VALUES (":id", ":name", ":data", :updated, GeomFromText("POINT(:lat :lng)"))';
        $args = array();
        $args[':id'] = $this->id;
        $args[':name'] = $this->name;
        $args[':data'] = $this->data;
        $args[':updated'] = $this->updated;
        $args[':lat'] = $this->lat;
        $args[':lng'] = $this->lng;
        db()->dquery($sql)->arg($args)->execute();
    }

    public function render($mode = 'teaser') {
        $vars = array();
        $vars['location_name'] = $this->name;
        switch($mode) {
            case 'micro':
                return '<span class="location_name">'.$this->name . '</span>';
                break;
            case 'teaser' :
            default :
                $template = new Template(false);
                $template->load_template('templates/location.teaser.tpl.php', 'location');
                $template->add_variable($vars);
        }
        return $template->render();
    }

}
