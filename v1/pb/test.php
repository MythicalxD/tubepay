<?php

$trans_id = "100407874547";
$secret_key = "GdQ9VX5WkpDotoTj3KuNBqXeEg4uKmoU";

$validation_signature = md5($trans_id . '-' . $secret_key);
echo $validation_signature;