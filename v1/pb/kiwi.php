<?php

require_once '../../includes/DbOperations.php';

// Your secret key can be found in your apps section by clicking on the "Secret Key" button
$secret_key = 'UfSke4FAuuq8FuDhbBsiAyZkMfQvc6xc';

// KiwiWall server IP addresses
$allowed_ips = array(
        '34.193.235.172'
);
// Proceess only requests from KiwiWall IP addresses
// This is optional validation
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    echo 0;
    die();
}

// Get parameters
$status = $_REQUEST['status'];
$trans_id = $_REQUEST['trans_id'];
$sub_id = $_REQUEST['sub_id'];
$sub_id_2 = $_REQUEST['sub_id_2'];
$sub_id_3 = $_REQUEST['sub_id_3'];
$sub_id_4 = $_REQUEST['sub_id_4'];
$sub_id_5 = $_REQUEST['sub_id_5'];
$gross = $_REQUEST['gross'];
$amount = $_REQUEST['amount'];
$offer_id = $_REQUEST['offer_id'];
$offer_name = $_REQUEST['offer_name'];
$category = $_REQUEST['category'];
$os = $_REQUEST['os'];
$app_id = $_REQUEST['app_id'];
$ip_address = $_REQUEST['ip_address'];
$signature = $_REQUEST['signature'];

// Create validation signature
$validation_signature = md5($sub_id . ':' . $amount . ':' . $secret_key);
if ($signature != $validation_signature) {
    // Signatures not equal - send error code
    echo 0;
    die();
}
// Validation was successful. Credit user process.

$db = new DbOperations();
$result = $db->addPointsOfferwall($sub_id, $trans_id, $amount);

echo 1;
die();