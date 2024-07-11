<?php

require_once '../../includes/DbOperations.php';

// Your secret key can be found in your apps section by clicking on the "Secret Key" button
$secret_key = 'GdQ9VX5WkpDotoTj3KuNBqXeEg4uKmoU';

// KiwiWall server IP addresses
$allowed_ips = array(
    '188.40.3.73'
);

// if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
//     echo 0;
//     die();
// }

// Get parameters
$status = $_REQUEST['status'];
$trans_id = $_REQUEST['trans_id'];
$user_id = $_REQUEST['user_id'];
$amount_local = $_REQUEST['amount_local'];
$amount_usd = $_REQUEST['amount_usd'];
$ip_click = $_REQUEST['ip_click'];
$type = $_REQUEST['type'];
$secure_hash = $_REQUEST['secure_hash'];


// Create validation signature
$validation_signature = md5($trans_id . '-' . $secret_key);
if ($secure_hash != $validation_signature) {
    // Signatures not equal - send error code
    echo 0;
    die();
}
// Validation was successful. Credit user process.
$db = new DbOperations();
$result = $db->addPointsOfferwall($user_id, $trans_id, $amount_local);

echo 1;
die();