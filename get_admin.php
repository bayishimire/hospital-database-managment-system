<?php
require_once 'connection.php';
$r = $conn->query("SELECT username, password, role FROM users WHERE role='SuperAdmin' LIMIT 1");
print_r($r->fetch_assoc());
?>
