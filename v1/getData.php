<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');


require_once '../includes/DbOperations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

	if (isset($_GET['uid'])) {
		$db = new DbOperations();
		$micro = round(microtime(true));
		$db->setTime($_GET['uid'], $micro);

		$result = $db->getData($_GET['uid']);
		$json = json_decode($result, true);
		$json['time'] = $micro;
		echo json_encode($json);

	} else {
		$response['error'] = true;
		$response['message'] = "Required Felids are missing";
		echo json_encode($response);
	}
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Request";
	echo json_encode($response);
}
