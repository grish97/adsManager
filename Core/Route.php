<?php

namespace Core;

use app\Home;

class Route
{
    public $session;
    public $homePage;

    public function __construct($session) {
        $this->homePage = new Home($session);
        $this->session = $session;
        $uri = $_SERVER['REQUEST_URI'];
        $this->getRoute($uri);
    }

    public function getRoute($uri) {
        $parts = array_values(array_filter(explode('/', $uri)));

        if(!empty($parts)) {
            $className = ucfirst($parts[0]);
            $controller = "\\app\\{$className}";
            $action = $parts[1];
            $params = $parts[2] ?? null;

            try {
                $object = new $controller($this->session);
                $object->$action();
            }catch(\Error $error) {
                $this->homePage->index();
            }

            if(isset($params)) {
                $object->$action($params);
                return true;
            }


        }else  $this->homePage->index();
    }
}