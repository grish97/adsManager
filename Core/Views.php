<?php

namespace Core;

class Views
{
    public $layout_path;
    public $template_path;

    public $data;
    public $content = '';

    function __construct($template, $data = []) {
        $this->data = $data;

        $this->layout_path = views_path('layout.app');
        $this->template_path = views_path($template);
    }

    public function render() {
        $layout = $this->getTemplate($this->layout_path);
        $template = $this->getTemplate($this->template_path);

        $this->content = str_replace('@content', $template, $layout);

        return $this->content;
    }

    public function getTemplate($file) {
        if(file_exists($file)) {
            ob_start();
            extract($this->data);
            require $file;
            return ob_get_clean();
        }
    }

}