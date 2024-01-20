<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');


require_once '../../includes/DbOperations.php';
require_once '../../includes/decode.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $jsonStringEncoded = $_POST['encrypted'];

    $jsonString = decrypt($jsonStringEncoded);

    $data = json_decode($jsonString, true);

    // Check if decoding was successful
    if ($data === null) {
        $response['error'] = true;
        $response['message'] = "Error Decoding Json";
        echo json_encode($response);
        die();
    }

    // Access individual fields
    $fingerprint = $data['fingerprint'];
    $uid = $data['uid'];
    $time = $data['time'];
    $id = $data['id'];
    $version = $data['version'];

    if ($fingerprint != FINGERPRINT) {
        $response['error'] = true;
        $response['message'] = "Fingerprint Mismatch!";
        echo json_encode($response);
        //die();
    }

    if ($time + 10 < time()) {
        $response['error'] = true;
        $response['message'] = "Request Packet Expired!";
        echo json_encode($response);
        //die();
    }

    if ($version != VERSION) {
        $response['error'] = true;
        $response['message'] = "Version Mismatch!";
        echo json_encode($response);
        //die();
    }

    // You can safely execute anything here
    $db = new DbOperations();

    if ($db->validate($jsonStringEncoded)) {
        $response['error'] = true;
        $response['message'] = "Token already used!";
        echo json_encode($response);
        //die();
    }

    $result = $db->addPointsSubs($uid, $id);
    if ($result == 1) {
        $response['message'] = "Reward Claimed Successfully";
        $response['Code'] = "101";
    } else if ($result == 3) {
        $response['message'] = "Already Subscribed!";
        $response['Code'] = "103";
    } else {
        $response['message'] = "Some Error Occurred Please try again";
        $response['Code'] = "102";
    }

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
}

echo json_encode($response);