<?php

function getRoute($session) {
    return new \Core\Route($session);
}

function dd( ... $args) {
    var_dump($args);
    die();
}

function uploadFile($file) {
    $imageContent = null;
    $uploadDir = BASE_PATH . 'public' .DIRECTORY_SEPARATOR. 'uploads';
    $fileDir = null;

    $tmp_name = $file['tmp_name'];
    $name = $file['name'];

    $fileDir = "$uploadDir\\$name";

    $is_uploaded = move_uploaded_file($tmp_name, $fileDir);

    if($is_uploaded) {
        $imageContent = glob($fileDir)[0];
    }

    $imageContent = file_get_contents($imageContent);

    unlink($fileDir);

    return $imageContent;

}

function _explode($size) {
    $delimiter = 'x';

    if(strpos($size ,':')) {
        $delimiter = ':';
    }

    list($width, $height) = explode($delimiter, $size);

    $size = [
        'width' => $width,
        'height' => $height
    ];

    return $size;
}

function managerAuth() {
    $auth =  new app\Auth();
    return $auth->googleAuth();
}

function fixObject(& $object) {
    if ($object instanceof __PHP_Incomplete_Class) {
        $object =  unserialize(serialize($object));
    }

    return $object;

}

function views_path($path) {
    $path = str_replace('.', DIRECTORY_SEPARATOR, $path);

    $basePath = BASE_PATH . 'views' . DIRECTORY_SEPARATOR . $path . '.php';

    return $basePath;
}


function view($template, $data = []) {
    $view =  new \Core\Views($template, $data);
    return $view->render();
}

