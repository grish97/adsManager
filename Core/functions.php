<?php

function getRoute($session) {
    return new \Core\Route($session);
}

function dd( ... $args) {
    var_dump($args);
    die();
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