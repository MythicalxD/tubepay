<?php

require_once '../../includes/DbOperations.php';

/*Get query parameters*/
$pub_id = $_REQUEST['pub_id'];
$app_id = $_REQUEST['app_id'];
$user_id = $_REQUEST['user_id'];
$amount = $_REQUEST['amount'];
$payout = $_REQUEST['payout'];
$offer_id = $_REQUEST['offer_id'];
$offer_name = $_REQUEST['offer_name'];
$event_id = $_REQUEST['event_id'];
$event_name = $_REQUEST['event_name'];
$txn_id = $_REQUEST['txn_id'];
$currency_name = $_REQUEST['currency_name'];
$timestamp = $_REQUEST['timestamp'];
$hash = $_REQUEST['hash'];

/*Check if duplicate transaction*/
$transactionExist = false; // Search in database if current txn_id exist. True if exist
if ($transactionExist) {
    /*Duplicate transaction detected. Do not reward user but send us postback received positive response*/
    return 1;
}

/*Create validation hash and validate hashes*/
$secretKey = "BWOCRJfDxPa2cvrs5IkmzQy6n2r5DKwr"; // This has to be your App's secret key that you can find in you App detail page
/*Get the currently active http protocol*/
$protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") ? "https" : "http";
/*Build the full callback URL*/
/*Example: https://url.com?param1=foo&param2=bar&hash=3171f6b78e06cadcec4c9c3b15f8588400e8738*/
$url = "$protocol://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
/*Get the callback URL without the "hash" query parameter*/
/*Example: https://url.com?param1=foo&param2=bar*/
$urlWithoutHash = substr($url, 0, -strlen("&hash=$hash"));
/*Generate a hash from the complete callback URL without the "hash" query parameter*/
$generatedHash = hash_hmac("sha1", $urlWithoutHash, $secretKey);

/*Check if the generated hash is the same as the "hash" query parameter*/
if ($generatedHash == $hash) {
    /*Validation successful. Queue your user credit functions and send us postback received positive response*/

    // Add txn_id
    // Add points after conversion amount * 50k
    // uid = user_id

    $result = $db->addPointsOfferwall($uid, $txn_id, $amount);

    return 1;
} else {
    /*Hash not equal. Send error response.
    Try to fix any errors found for hash validation.
    Contact us in case the postback is from our ip and need some assistance.*/
    return 0;
}