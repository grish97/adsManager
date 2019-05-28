<?php

session_start();

//PROJECT BASE PATH
const BASE_PATH = __DIR__ . DIRECTORY_SEPARATOR;

//AUTOLOAD FILE
require BASE_PATH .  'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

//HELPER FUNCTIONS
require BASE_PATH . 'Core' . DIRECTORY_SEPARATOR  . 'functions.php';

//AUTOLOAD
spl_autoload_register(function ($class) {
    $file =  $class . '.php';

    if(file_exists($file)) {
        require_once $file;
    }
});

//GOOGLE OAUTH2
$session = managerAuth();

//ROUTING FOR REQUEST
getRoute($session);
