#!/usr/bin/env php
<?php
/**
 * command-line tiqr relying party
 * finish new user registration
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
$result = [];

if (!isset($sid)) {
	error_log("error: Cannot finish registration - need a sessionid");
	exit(1);
}

$status = $tiqr->getEnrollmentStatus($sid);
error_log("[$sid] status is $status");
if($status===Tiqr_Service::ENROLLMENT_STATUS_IDLE) {
	error_log("error: Cannot finish registration - invalid session");
	exit(1);
}
$result['uid'] = $tiqr->getAuthenticatedUser($sid);

$previous = $status;
do {
	$status = $tiqr->getEnrollmentStatus($sid);
	if( $status === $previous ) continue;
	$previous = $status;
	switch( $status ) {
	case Tiqr_Service::ENROLLMENT_STATUS_IDLE:
		error_log("[enrol-$sid] status is $status (idle)");
		break;
	case Tiqr_Service::ENROLLMENT_STATUS_INITIALIZED:
		error_log("[enrol-$sid] status is $status (initialized)");
		break;
	case Tiqr_Service::ENROLLMENT_STATUS_RETRIEVED:
		error_log("[enrol-$sid] status is $status (retrieved)");
		break;
	case Tiqr_Service::ENROLLMENT_STATUS_PROCESSED:
		error_log("[enrol-$sid] status is $status (processed)");
		break;
	case Tiqr_Service::ENROLLMENT_STATUS_FINALIZED:
		error_log("[enrol-$sid] status is $status (finalized)");
		$tiqr->resetEnrollmentSession($sid);
		error_log("[enrol-$sid] reset enrollment");
		break;
	default:
		error_log("[enrol-$sid] unknown status: $status");
	}
} while( $status !== Tiqr_Service::ENROLLMENT_STATUS_IDLE);
$result['status'] = $status;
echo json_encode($result);
