#!/usr/bin/env php
<?php
/**
 * command-line tiqr relying party
 * finish tiqr login
 */

include(__DIR__ . '/../options.php');

$usage = <<<EOD
Usage: $argv[0] [-hv] [-s sessionid]
	h : help
	s : specify sessionid to finish registration
	v : verbose
EOD;

$opts = getopt("s:hv");
if (isset($opts['h'])) {
	error_log($usage);
	exit(0);
}		

$sid = $opts['s'];
if (!isset($sid)) {
	error_log("error: Cannot finish registration - need a sessionid");
	exit(1);
}

$tiqr = new Tiqr_Service($options);

error_log("[login-$sid] " . $tiqr->getAuthenticatedUser($sid));

do {
	$uid = $tiqr->getAuthenticatedUser($sid);
	fwrite(STDERR, "."); sleep(1);
} while( !isset($uid) );
fwrite(STDERR, "\n");

error_log("[login-$sid] " . $tiqr->getAuthenticatedUser($sid));
$tiqr->logout($sid);
error_log("[login-$sid] " . $tiqr->getAuthenticatedUser($sid));
$result = ['uid' => $uid];
echo json_encode($result);
