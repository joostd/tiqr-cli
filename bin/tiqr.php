#!/usr/bin/env php
<?php

include(__DIR__ . '/../options.php');

$usage = <<<EOD
Usage: $argv[0] [-hmrv] [-u userid] [-n display name] [-p proxy]
	h : help
	m : login via push message (requires -u)
	n : specify name (default is dummy)
	p : use a proxy (default is none, assume http://localhost:8080)
	r : register (default is login)
	u : specify userid to register or login (default is timestamp in base-36)
	v : verbose
EOD;

$opts = getopt("u:n:p:hmrv");
if (isset($opts['h'])) {
	error_log($usage);
	exit();
}

$uid = null;
if (isset($opts['u'])) $uid = $opts['u'];
$displayName = "dummy";
if (isset($opts['n'])) $displayName = $opts['n'];
$proxy = 'http://localhost:8080';
if (isset($opts['p'])) $proxy = $opts['p'];
$optRegister = array_key_exists('r', $opts);
$optPush = array_key_exists('m', $opts);

if( $optPush && $uid === null ) {
	error_log("error: Cannot push to unknown user");
	exit(1);
}

$proto = "http";
$host = "localhost:8080";
if( strstr( $proxy, "://") === FALSE) {
	$host = $proxy;
} else {
	list($proto,$host) = explode("://",$proxy);
}

$store = Tiqr_UserStorage::getStorage($options["userstorage"]["type"], $options["userstorage"]);
if ($store->userExists($uid)) {
	if( $optRegister ) {
		error_log("error: Cannot register $uid - user exists");
		exit(1);
	} 
	error_log("Existing user (uid: '$uid', displayName: '" . $store->getDisplayName($uid) . "')");
	#echo $store->getLoginAttempts($uid) . "\t" . $store->getTemporaryBlockAttempts($uid) . "\t" . $store->getTemporaryBlockTimestamp($uid) . "\n";
}

$tiqr = new Tiqr_Service($options);
$sid = bin2hex(random_bytes(4)); // or better 16
error_log("[$sid] uid is $uid and displayName is $displayName");

if( $optRegister ) { // register new user
	$uid = base_convert(time(), 10, 36); // use timestamp as userid
	$status = $tiqr->getEnrollmentStatus($sid);
	assert($status===Tiqr_Service::ENROLLMENT_STATUS_IDLE);
	error_log("[$sid] status is $status (idle)");
	$key = $tiqr->startEnrollmentSession($uid, $displayName, $sid);
	error_log("[$sid] started enrollment session key $key");
	$metadataURL = base($proto,$host) . "/tiqr?key=$key";
	error_log("[$sid] generating QR code for metadata URL $metadataURL");
	$url = $tiqr->generateEnrollString($metadataURL);
	error_log("[$sid] URL $url");
	#echo "open 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=$url'\n";
	system("qrencode -t ANSI256 $url 1>&2");
	$previous = $status;
	do {
		$status = $tiqr->getEnrollmentStatus($sid);
		if( $status === $previous ) continue;
		$previous = $status;
		switch( $status ) {
		case Tiqr_Service::ENROLLMENT_STATUS_IDLE:
			error_log("[$sid] status is $status (idle)");
			break;
		case Tiqr_Service::ENROLLMENT_STATUS_INITIALIZED:
			error_log("[$sid] status is $status (initialized)");
			break;
		case Tiqr_Service::ENROLLMENT_STATUS_RETRIEVED:
			error_log("[$sid] status is $status (retrieved)");
			break;
		case Tiqr_Service::ENROLLMENT_STATUS_PROCESSED:
			error_log("[$sid] status is $status (processed)");
			break;
		case Tiqr_Service::ENROLLMENT_STATUS_FINALIZED:
			error_log("[$sid] status is $status (finalized)");
			$tiqr->resetEnrollmentSession($sid);
			error_log("[$sid] reset enrollment");
			break;
		default:
			error_log("[$sid] unknown status: $status");
		}
	} while( $status !== Tiqr_Service::ENROLLMENT_STATUS_IDLE);
	echo "\n$uid\n";
} else { // login existing user
	error_log("*** new login session with id=$sid");
	$sessionKey = $tiqr->startAuthenticationSession($uid, $sid); // prepares the tiqr library for authentication
	error_log("[$sid] session key=$sessionKey");
	$url = $tiqr->generateAuthURL($sessionKey);
	error_log("[$sid] URL $url");

	if( $optPush ) {
		assert( isset($uid) );
		$notificationType = $store->getNotificationType($uid);
		$notificationAddress = $store->getNotificationAddress($uid);
		error_log("[$sid] type [$notificationType], address [$notificationAddress]");
		if (isset($notificationAddress)) {
			$translatedAddress = $tiqr->translateNotificationAddress($notificationType, $notificationAddress);
			error_log("[$sid] translated address [$translatedAddress]");
		} else {
			error_log("[$sid] No notificationAddress retrieved for user [$uid]");
		}
		if (isset($translatedAddress)) {
			if ($tiqr->sendAuthNotification($sessionKey, $notificationType, $translatedAddress)) {
				error_log("[$sid] sent notification of type [$notificationType] to [$translatedAddress]");
			} else {
				error_log("[$sid] failed sending notification of type [$notificationType] to [$translatedAddress]");
			}
		} else {
			error_log("[$sid] Could not translate address [$notificationAddress] for notification of type [$notificationType]");
		}
	} else {
		system("qrencode -t ANSI256 $url 1>&2");
	}

	do {
		$userdata = $tiqr->getAuthenticatedUser($sid);
		fwrite(STDERR, "."); sleep(1);
	} while( !isset($userdata) );

	echo "\n$userdata\n";
	$tiqr->logout($sid);
}
