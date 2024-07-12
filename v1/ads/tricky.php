<?php

require_once '../../includes/DbOperations.php';
require_once '../../includes/Constants.php';

if (isset($_GET['pass'])) {
    if ($_GET['pass'] == ADMIN_PASS) {
        echo ("Password verified!\n");

        $db = new DbOperations();
        $result = $db->AddPointsS2S($_GET['uid'], $_GET['amt']);

        echo ("Points Added to user :  " . $result);

    } else {
        echo ("wrong Password\n");
    }
} else {
    echo ("INVALID OPERATION");
}