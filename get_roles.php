<?php
require_once 'connection.php';
$r = $conn->query("SELECT DISTINCT role FROM users");
$roles = [];
while($row = $r->fetch_assoc()) $roles[] = $row['role'];

$credentials = [];
foreach ($roles as $role) {
    if (empty($role)) continue;
    $res = $conn->query("SELECT username, password, role FROM users WHERE role='$role' LIMIT 1");
    if ($user = $res->fetch_assoc()) {
        $credentials[] = $user;
    }
}
echo json_encode($credentials, JSON_PRETTY_PRINT);
?>
