<?php

/* Error Codes Mapping [ 102 : DeviceID Found ] [ 101 : Database Error ] [ 100 : Database Make SUccess ] */

require_once '../includes/DbOperations.php';
require_once '../includes/decode.php';
$response = array();

function generateRandomString($length)
{
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$randomString = '';

	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}

	return $randomString;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	if (isset($_POST['encrypted'])) {
		// unpack the data

		$jsonStringEncoded = $_POST['encrypted'];

		$jsonString = decrypt($jsonStringEncoded);

		$data = json_decode($jsonString, true);

		// Check if decoding was successful
		if ($data === null) {
			die('Error decoding JSON data');
		}

		// Access individual fields
		$fingerprint = $data['fingerprint'];
		$uid = $data['uid'];
		$deviceID = $data['deviceID'];
		$time = $data['time'];
		$referral = generateRandomString(8);

		include_once '../includes/Constants.php';

		// check fingerprint and
		if ($fingerprint == FINGERPRINT) {
			if ($time + 10 > time()) {
				$db = new DbOperations();
				if ($db->isUserExists($deviceID)) {
					$response['message'] = "User Already Registered";
					$response['Code'] = "102";
				} else {
					$result = $db->createUser($uid, $referral, $deviceID);
					if ($result == 1) {
						$response['message'] = "User Registered Successfully";
						$response['Code'] = "100";
					} else {
						$response['message'] = "Some Error Occurred Please try again";
						$response['Code'] = "101";
					}
				}
			} else {
				$response['error'] = true;
				$response['message'] = "Request Expired!";
			}
		} else {
			$response['error'] = true;
			$response['message'] = "Fingerprint Mismatch!";
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Required Felids are missing";
	}
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Request";
}

echo json_encode($response);
