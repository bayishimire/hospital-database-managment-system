<?php
require_once 'connection.php';
echo "--- Users Table ---\n";
$res = $conn->query("SELECT user_id, username, role, related_id FROM users");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n--- Doctors Table ---\n";
$res = $conn->query("SELECT doctor_id, first_name, last_name FROM doctors");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>