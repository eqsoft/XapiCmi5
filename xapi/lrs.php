<?php
header("X-PHP-PROXY: TRUE");
$body = file_get_contents("php://input");
echo "hello world: " . $body;
exit;
?>
