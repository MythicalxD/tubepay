<?php

require_once '../../includes/DbOperations.php';
$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {

    $db = new DbOperations();
    $result = $db->getyt($_GET['uid']);
    echo $result;

} else {
    $response['error'] = true;
    $response['message'] = "Invalid Request";
    echo json_encode($response);
}
