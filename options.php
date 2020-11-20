<?php

require_once __DIR__.'/vendor/tiqr/tiqr-server-libphp/library/tiqr/Tiqr/AutoLoader.php';

$options = array(
    "identifier"        => "debug.tiqr.org",
    "name"              => "tiqr debug",
    "auth.protocol"     => "tiqrauth",
    "enroll.protocol"   => "tiqrenroll",
    "ocra.suite"        => "OCRA-1:HOTP-SHA1-6:QH10-S",
    "logoUrl"           => "https://demo.tiqr.org/img/tiqrRGB.png",
    "infoUrl"           => "https://www.tiqr.org",
    "tiqr.path"         => __DIR__ . "/vendor/tiqr/tiqr-server-libphp/library/tiqr",
    'phpqrcode.path'    => __DIR__ . '/vendor/kairos/phpqrcode',
    'zend.path'         => __DIR__ . '/vendor/zendframework/zendframework1/library',	// used for push notifications
    "statestorage"      => array("type" => "file"),
    "userstorage"       => array("type" => "file", "path" => "/tmp", "encryption" => array('type' => 'dummy')),
//     "userstorage"      => array("type" => "pdo", 'dsn' => 'sqlite:/tmp/tiqr.sq3', 'table' => 'user', "encryption" => array('type' => 'dummy')),
);

// override options locally. TODO merge with config
if( file_exists(dirname(__FILE__) . "/local_options.php") ) {
    include(dirname(__FILE__) . "/local_options.php");
} else {
    error_log("no local options found");
}

$autoloader = Tiqr_AutoLoader::getInstance($options); // needs {tiqr,zend,phpqrcode}.path
$autoloader->setIncludePath();

function base($proto="http", $host="localhost:8080")
{
    #if (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER)) {
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    }
    #if (array_key_exists('HTTP_X_FORWARDED_HOST', $_SERVER)) {
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } elseif (isset($_SERVER['HTTP_HOST'])) {
        $host = $_SERVER['HTTP_HOST'];
    }
    return $proto . "://" . $host;
}
