<?php

require_once '../../includes/DbOperations.php';
require_once '../../includes/Constants.php';

$userID = $_GET['user_id'];
$eventID = $_GET['event'];
$eventToken = $_GET['token'];

$fin = sha1($eventID . APPLOVIN_TOKEN);
$fin1 = sha1($eventID . APPLOVIN_TOKEN_2);

if ($eventToken == $fin || $eventToken == $fin1) {
    $db = new DbOperations();
    // Request is correct !
    $db->addPointsAdd20($userID);
} else {
    echo 0;
    // Heck :)
}
