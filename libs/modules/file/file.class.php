<?php

class File
{
    public $id, $path, $type, $width = 0, $height = 0, $data, $created = 0, $deleted = 0, $url = '', $sizes = array(), $is_image = false;

    private $fullpath, $upload_path;

    public function __construct($fid = 0) {
        global $upload_path;
        $this->upload_path = $upload_path;

        if (is_string($fid)) {
            $this->id = $fid;
            $this->load();
        }
    }

    public function upload($file) {
        if (empty($file['name'])) {
            return false;
        }
        $this->id = md5($file['name'] . time());
        $this->fullpath = $file['tmp_name'];
        $this->type = mime_content_type($this->fullpath);
        $this->path = $this->id . '.' . array_pop(explode('/', $this->type));
        $this->created = time();
        //check the content type that was sent with the file (may be different to detected content type)
        if (strpos($file['type'], 'image') !== false && getimagesize($this->fullpath) !== false) {
            $size = getimagesize($this->fullpath);
            list($this->width, $this->height) = $size;

        } else {
            //pretending to be an image.
            return false;
        }
        if (strpos('tmp', $this->fullpath) === false) {
            copy($this->fullpath, $this->upload_path . '/' . $this->path);
        } else {
            rename($this->fullpath, $this->upload_path . '/' . $this->path);
        }
        $this->save();
        return $this;
    }

    public function save() {
        $sql = 'REPLACE INTO file (fid, path, type, width, height, data, created, deleted) VALUES (":id", ":path", ":type", :width, :height, ":data", :created, "deleted")';
        $args = array(':id' => $this->id, ':path' => $this->path, ':type' => $this->type, ':width' => $this->width, ':height' => $this->height, ':data' => json_encode($this->data), ':created' => $this->created, ':deleted' => $this->deleted, );

        db()->dquery($sql)->arg($args)->execute();
        return $this;
    }

    public function load() {
        $sql = 'SELECT * FROM file WHERE fid = ":id" AND deleted = 0';
        $file = db()->dquery($sql)->arg(':id', $this->id)->execute()->fetch_single();
        if (!empty($file)) {
            $this->url = get_url(UPLOAD_PATH . '/' . $file['path']);
            $this->type = $file['type'];
            $this->width = $file['width'];
            $this->height = $file['height'];
            $this->path = $file['path'];

            if (beginsWith($this->type, 'image')) {
                $this->is_image = true;
            }

        }
    }

    public function get_thumbnail($width, $height = false) {
        if ($width === false && $height !== false) {
            $width = $height;
        } elseif ($width !== false && $height === false) {
            $height = $width;
        }
        $path = UPLOAD_PATH . '/thumbnails/' . $this->id . '_' . $width . '_' . $height . '.jpg';
        if (file_exists($path)) {
            return ($path);
        }
        switch ($this->type) {
            case 'image/gif' :
                $source_gd_image = imagecreatefromgif(UPLOAD_PATH . '/' . $this->path);
                break;
            case 'image/jpeg' :
                $source_gd_image = imagecreatefromjpeg(UPLOAD_PATH . '/' . $this->path);
                break;
            case 'image/png' :
                $source_gd_image = imagecreatefrompng(UPLOAD_PATH . '/' . $this->path);
                break;
        }
        if ($source_gd_image === false) {
            return false;
        }
        $source_aspect_ratio = $this->width / $this->height;
        $thumbnail_aspect_ratio = $width / $height;
        if ($this->width <= $width && $this->height <= $height) {
            $thumbnail_image_width = $this->width;
            $thumbnail_image_height = $this->height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int)($height * $source_aspect_ratio);
            $thumbnail_image_height = $height;
        } else {
            $thumbnail_image_width = $width;
            $thumbnail_image_height = (int)($width / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $this->width, $this->height);
        imagejpeg($thumbnail_gd_image, $path, 90);
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);
        return ($path);
    }

    public function load_from_url($url) {
        $data = file_get_contents($url);
        $this->id = md5($url . time());
        $this->path = $this->id . '.jpg';
        $this->fullpath = $this->upload_path . '/' . $this->path;
        file_put_contents($this->fullpath, $data);
        $this->type = mime_content_type($this->fullpath);
        if (beginsWith($this->type, 'image')) {
            $this->is_image = true;
            list($this->width, $this->height) = getimagesize($this->fullpath);
        }
        $this->save();
    }

    public function render($mode = 'teaser') {

        switch($mode) {
            case 'thumbnail' :
                if ($this->is_image) {
                    return '<img src="' . get_url($this->get_thumbnail(50)) . '" class="span12" style="max-width:50px"/>';
                }
            case 'thumbnail-large' :
                if ($this->is_image) {
                    return '<img src="' . get_url($this->get_thumbnail(200)) . '" class="span12" style="max-width:200px"/>';
                }
            case 'teaser' :
            default :
                return $this->id;
        }

    }

}

function file_image_type_id_to_type($id) {
    $types = array('gif', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'tiff', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm');
    return 'image/' . $types[$id - 1];
}
