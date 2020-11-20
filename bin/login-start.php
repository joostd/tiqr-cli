#!/usr/bin/env php
<?php
/**
 * command-line tiqr relying party
 */

include(__DIR__ . '/../options.php');

$usage = <<<EOD
Usage: $argv[0] [-hmv] [-u userid]
	h : help
	m : login via push message (requires -u)
	u : specify userid to login (default is to allow arbitrary users to login)
	v : verbose
EOD;

$opts = getopt("u:hmv");
if (isset($opts['h'])) {
	error_log($usage);
	exit(1);
}

$uid = null;
if (isset($opts['u'])) $uid = $opts['u'];
$optPush = array_key_exists('m', $opts);

if( $optPush && $uid === null ) {
	error_log("error: Cannot push to unknown user");
	exit(1);
}

$store = Tiqr_UserStorage::getStorage($options["userstorage"]["type"], $options["userstorage"]);
if ($store->userExists($uid)) {
	error_log("[login-$sid] login existing user (uid: '$uid', displayName: '" . $store->getDisplayName($uid) . "')");
	#echo $store->getLoginAttempts($uid) . "\t" . $store->getTemporaryBlockAttempts($uid) . "\t" . $store->getTemporaryBlockTimestamp($uid) . "\n";
}

$tiqr = new Tiqr_Service($options);
$sid = bin2hex(random_bytes(4)); // or better 16
error_log("[login-$sid] uid is $uid and displayName is $displayName");
$sessionKey = $tiqr->startAuthenticationSession($uid, $sid); // prepares the tiqr library for authentication
error_log("[login-$sid] session key=$sessionKey");
$url = $tiqr->generateAuthURL($sessionKey);
error_log("[login-$sid] URL $url");

$result = [];
if( $optPush ) {
	assert( isset($uid) );
	$notificationType = $store->getNotificationType($uid);
	$notificationAddress = $store->getNotificationAddress($uid);
	error_log("[login-$sid] type [$notificationType], address [$notificationAddress]");
	if (isset($notificationAddress)) {
		$translatedAddress = $tiqr->translateNotificationAddress($notificationType, $notificationAddress);
		error_log("[login-$sid] translated address [$translatedAddress]");
	} else {
		error_log("[login-$sid] No notificationAddress retrieved for user [$uid]");
	}
	if (isset($translatedAddress)) {
		if ($tiqr->sendAuthNotification($sessionKey, $notificationType, $translatedAddress)) {
			error_log("[login-$sid] sent notification of type [$notificationType] to [$translatedAddress]");
		} else {
			error_log("[login-$sid] failed sending notification of type [$notificationType] to [$translatedAddress]");
		}
	} else {
		error_log("[login-$sid] Could not translate address [$notificationAddress] for notification of type [$notificationType]");
	}
}
$result = ['url' => $url, 'sid' => $sid];
echo json_encode($result);
