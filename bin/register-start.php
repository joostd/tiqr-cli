#!/usr/bin/env php
<?php
/**
 * command-line tiqr relying party
 * register new user
 */

include(__DIR__ . '/../options.php');

$usage = <<<EOD
Usage: $argv[0] [-hv] [-u userid] [-n display name] [-p proxy]
	h : help
	n : specify name (default is dummy)
	p : use a proxy (default is none, assume http://localhost:8080)
	u : specify userid to register (default is timestamp in base-36)
	v : verbose
EOD;

$opts = getopt("u:n:p:hsv");
if (isset($opts['h'])) {
	error_log($usage);
	exit(1);
}

$sid = bin2hex(random_bytes(4)); // or better 16
$uid = base_convert(time(), 10, 36); // use timestamp as default userid
if (isset($opts['u'])) $uid = $opts['u'];
$displayName = "dummy";
if (isset($opts['n'])) $displayName = $opts['n'];
$proxy = 'http://localhost:8080';
if (isset($opts['p'])) $proxy = $opts['p'];

$proto = "http";
$host = "localhost:8080";
if( strstr( $proxy, "://") === FALSE) {
	$host = $proxy;
} else {
	list($proto,$host) = explode("://",$proxy);
}

$store = Tiqr_UserStorage::getStorage($options["userstorage"]["type"], $options["userstorage"]);
if ($store->userExists($uid)) {
	error_log("error: Cannot register $uid - user exists");
	exit(1);
}

$tiqr = new Tiqr_Service($options);
error_log("[enrol-$sid] uid is $uid and displayName is $displayName");

$key = $tiqr->startEnrollmentSession($uid, $displayName, $sid);
error_log("[enrol-$sid] started enrollment session key $key");
$metadataURL = base($proto,$host) . "/tiqr?key=$key";
error_log("[enrol-$sid] generated metadata URL $metadataURL");
$url = $tiqr->generateEnrollString($metadataURL);
error_log("[enrol-$sid] URL $url");
$result = ['url' => $url, 'sid' => $sid, 'uid' => $uid];
echo json_encode($result);
