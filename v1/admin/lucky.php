<?php

require_once '../../includes/DbOperations.php';
require_once '../../includes/Constants.php';

if (isset($_GET['pass'])) {
    if ($_GET['pass'] == ADMIN_PASS) {
        echo ("Password verified!\n");
        echo ("<br>START\n");
        $db = new DbOperations();
        $result = $db->luckyNumber();
        echo ("<br>Lucky Draw picked :  " . $result);
    } else {
        echo ("wrong Password\n");
    }
} else {
    echo ("INVALID OPERATION");
}

echo "<br>EXECUTION DONE ✅";

?>