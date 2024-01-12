<?php

require_once '../../includes/DbOperations.php';
require_once '../../includes/Constants.php';

function generateRandomString($length)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

if (isset($_GET['pass'])) {
    if ($_GET['pass'] == ADMIN_PASS) {
        echo ("Password verified!\n");
        echo ("<br>START\n");
        $db = new DbOperations();
        $referral = generateRandomString(8);
        $referral1 = generateRandomString(8);
        $result = $db->createUser($_GET['u'], $referral, $referral1 . $referral);
        if ($result == 1) {
            echo ("User created ! uid = " . $_GET['u']);
        } else {
            echo ("Some Error Occurred");
        }

    } else {
        echo ("wrong Password\n");
    }
} else {
    echo ("INVALID OPERATION");
}

echo "<br>EXECUTION DONE ✅";

?>