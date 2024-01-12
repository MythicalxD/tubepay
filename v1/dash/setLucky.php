<?php

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
    $number = $data['number'];
    $time = $data['time'];
    $version = $data['version'];

    if ($fingerprint != FINGERPRINT) {
        $response['error'] = true;
        $response['message'] = "Fingerprint Mismatch!";
        echo json_encode($response);
        die();
    }

    if ($time + 10 < time()) {
        $response['error'] = true;
        $response['message'] = "Request Packet Expired!";
        echo json_encode($response);
        die();
    }

    if ($version != VERSION) {
        $response['error'] = true;
        $response['message'] = "Version Mismatch!";
        echo json_encode($response);
        die();
    }

    // You can safely execute anything here
    $db = new DbOperations();

    if ($db->validate($jsonStringEncoded)) {
        $response['error'] = true;
        $response['message'] = "Token already used!";
        echo json_encode($response);
        die();
    }

    $result = $db->setLucky($uid, $number);

    if ($result['code'] == 101) {
        $response['message'] = $result['message'];
        $response['Code'] = "101";
    } elseif ($result['code'] == 102) {
        $response['message'] = $result['message'];
        $response['Code'] = "102";
    } else {
        $response['message'] = $result['message'];
        $response['Code'] = "403";
    }

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
}

echo json_encode($response);