<?php

require_once '../../includes/DbOperations.php';
require_once '../../includes/decode.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $jsonStringEncoded = $_POST['encrypted'];
    $id = $_GET['id'];

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
    $email = $data['email'];
    $method = $data['method'];
    $amount = $data['amount'];
    $country = $data['country'];
    $time = $data['time'];

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

    $result = $db->payout($method, $amount, $email, $country, $uid);

    $response['message'] = $result['message'];
    $response['Code'] = $result['code'];

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
}

echo json_encode($response);