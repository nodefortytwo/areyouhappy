<?php

class Template
{
    public $html = '', $close, $content, $title, $messages, $js_settings = array();
    private $template, $current_template, $vars, $fullpage = true;

    public function __construct($fullpage = true) {

        $this->fullpage = $fullpage;
        if ($this->fullpage) {
            $this->load_css();
            $this->load_js();
            $this->load_less();
        }
        $this->template = array();
    }

    public function load_default_wrappers() {
        $this->title = $this->title . ' | Local Stories from areyouhappy.me';
        $this->html = $this->get_template(PATH_TO_MODULES . '/' . 'template' . '/' . 'theme/wrapper.tpl.php');
    }

    private function get_template($path) {
        ob_start();
        include ($path);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    public function load_template($template, $module) {
        $this->template[] = array('file' => PATH_TO_MODULES . '/' . $module . '/' . $template, 'vars' => array());
        $this->current_template = count($this->template) - 1;
    }

    public function add_variable($array) {
        $this->template[$this->current_template]['vars'] = array_merge($this->template[$this->current_template]['vars'], $array);
    }

    public function render() {
        if ($this->fullpage) {

        }
        //render templates
        foreach ($this->template as $template) {
            $this->vars = $template['vars'];
            $tmp = $this->get_template($template['file']);
            foreach ($template['vars'] as $variable => $value) {
                if (is_null($value) || empty($value)) {$value = '';
                }
                if (is_string($value)) {
                    $tmp = str_replace('{{' . $variable . '}}', $value, $tmp);
                }
            }
            $this->content .= $tmp;

            unset($tmp);
        }
        //if we are rendering a full page we have to compile the js, css and less
        if ($this->fullpage) {
            //parse the css and js files to generate the mark-up

            $this->compile_less();
            $this->compile_css();
            $this->compile_js();
            $this->get_messages();
            $this->compile_messages();
            //Load in the wrappers
            $this->load_default_wrappers();
            return $this->html;
        } else {
            return $this->content;
        }

    }

    private function load_css() {
        $this->css = array();
        $files = exec_hook('global_css');
        foreach ($files as $module_name => $module) {
            foreach ($module as $file) {
                if (strpos($file, '//') !== false) {
                    $this->css[] = $file;
                } else {
                    $this->css[] = get_url('/' . PATH_TO_MODULES . '/' . $module_name . '/' . $file);
                }
            }
        }
    }

    public function add_css($file, $module_name) {

        $this->css[] = get_url('/' . PATH_TO_MODULES . '/' . $module_name . '/' . $file);

    }

    private function compile_css() {
        $this->css_compiled = '';
        $css = '';
        foreach ($this->css as $file) {
            if (beginsWith($file, 'http://') || beginsWith($file, 'https://')) {
                $css .= file_get_contents($file);
            } else {
                $file = cwd() . $file;
                
                if (file_exists($file)) {
                    $css .= file_get_contents($file);
                }
            }
            //$this->css_compiled .= "\t" . '<link rel="stylesheet" href="' . $file . '">' . "\n";
        }
        //require_once('libs/cssmin.php');
        //$css = CssMin::minify($css);
        $id = md5($css) . '_' . var_get('CACHE_KEY', md5(time()));
        $path = UPLOAD_PATH . '/css/' . $id . '.css';
        if (!file_exists($path)) {
            file_put_contents($path, $css);
        }
        $this->css_compiled = "\t" . '<link rel="stylesheet" href="' . get_url('/' . $path) . '">' . "\n";
    }

    private function load_js() {
        $this->js = array();
        $files = exec_hook('global_js');
        foreach ($files as $module_name => $module) {
            foreach ($module as $file) {
                $this->add_js($file, $module_name);
            }
        }
    }

    public function add_js($file, $module_name = null) {

        if (beginsWith($file, 'http://') || beginsWith($file, 'https://') || beginsWith($file, '//')) {
            $this->js[] = $file;
        } else {
            $this->js[] = get_url('/' . PATH_TO_MODULES . '/' . $module_name . '/' . $file);
        }

    }

    private function compile_js() {
        $this->js_complied = '';
        $js = '';
        foreach ($this->js as $file) {
            if (beginsWith($file, 'http://') || beginsWith($file, 'https://')) {
                $js .= file_get_contents($file);
            } else {
                $file = cwd() . $file;
                if (file_exists($file)) {
                    $js .= file_get_contents($file);
                }
            }

        }
        $id = md5($js) . '_' . var_get('CACHE_KEY', md5(time()));
        $path = UPLOAD_PATH . '/js/' . $id . '.js';
        if (!file_exists($path)) {
            file_put_contents($path, $js);
        }
        $this->js_complied = "\t" . '<script src="' . get_url('/' . $path) . '"></script>' . "\n";
    }

    //load less
    private function load_less() {
        $this->less = array();
        $files = exec_hook('global_less');
        foreach ($files as $module_name => $module) {
            foreach ($module as $file) {
                $this->less[] = PATH_TO_MODULES . '/' . $module_name . '/' . $file;
            }
        }
    }

    public function add_less($file, $module_name) {

        $this->less[] = PATH_TO_MODULES . '/' . $module_name . '/' . $file;

    }

    public function compile_less() {

        require_once ('libs/lessc.inc.php');
        $this->less_complied = '';
        $content = '';
        $id = '';
        foreach ($this->less as $file) {
            $id .= $file . filesize($file);
        }
        $id = md5($id) . '_' . cache_key();
        $path = UPLOAD_PATH . '/css/' . $id . '.css';

        if (!file_exists($path)) {
            $this->lessc = new lessc();
            foreach ($this->less as $file) {

                $content .= $this->lessc->compileFile($file);

                //$this->less_complied .= "\t" . '<link rel="stylesheet/less" type="text/css" href="' . $file . '">' . "\n";
            }
            //$id = md5($content);

            file_put_contents($path, $content);
        }
        $this->css[] = get_url('/' . $path);
    }

    public function c($content, $clear = false) {
        if ($clear) {
            $this->content = $content;
        } else {
            $this->content .= $content;
        }
    }

    private function render_nav($nav) {
        global $system_routes;
        $menu = array();
        $n = 0;
        if (true) {
            $menu['divider' . $n] = array('text' => '', 'class' => 'divider-vertical');
        }
        foreach ($system_routes as $module) {
            foreach ($module as $path => $item) {
                if (array_key_exists('nav', $item) && array_search($nav, $item['nav']) !== false) {
                    $display = true;
                    if (array_key_exists('menu_callback', $item)) {
                        $display = call_user_func_array($item['menu_callback'], array($path));
                    }
                    if ($display) {

                        if ($item['menu_title']) {
                            $title = $item['menu_title'];
                        } else {
                            $title = $path;
                        }

                        $icon = '';
                        if (isset($item['menu_icon'])) {
                            $icon = '<i class="' . $item['menu_icon'] . ' icon-white"></i> ';
                        }

                        if ($nav == 'phone') {
                            $title = '<i class="' . $item['menu_icon'] . ' icon-white"></i> ';
                        } else {
                            $title = $icon . $title;
                        }
                        $t = l($title, '/' . $path, '', false, $item['menu_title']);
                        $menu[$path] = array('text' => $t);

                        if ($nav == 'phone') {
                            $n++;
                            $menu['divider' . $n] = array('text' => '', 'class' => 'divider-vertical');
                        }
                    }
                }
            }
        }
        return template_list($menu, 'nav');
    }

    private function get_messages() {
        global $messages;
        $this->messages = $messages;
    }

    private function compile_messages() {
        $this->compiled_messages = '';
        if (is_array($this->messages)) {
            foreach ($this->messages as $message) {
                $this->compiled_messages .= '<div class="alert alert-' . $message['level'] . '">' . $message['text'] . '</div>';
            }
        }
        if (!empty($this->compiled_messages)) {
            $this->compiled_messages = '<div class="fluid-row"><div class="span10 offset1"><div class="alerts span11">' . $this->compiled_messages . '</div></div></div>';
        }
    }

}
