<?php

require_once __DIR__ . '/../../includes/DbOperations.php';
require_once __DIR__ . '/../../includes/Constants.php';

function runLuckyScript($password) {
    if ($password == ADMIN_PASS) {
        echo "Password verified!\n";
        echo "START\n";

        $db = new DbOperations();
        $result = $db->luckyNumber();
        echo "Lucky Draw picked: $result\n";
    } else {
        echo "Wrong Password\n";
    }

    echo "EXECUTION DONE ✅\n";
}

// // Check if the script is run from the command line
// if (php_sapi_name() === 'cli') {
//     if (isset($argv[1])) {
//         runLuckyScript($argv[1]);
//     } else {
//         echo "Please provide a password as a command-line argument.\n";
//     }
// } else {
//     echo "This script should be run from the command line.\n";
// }
