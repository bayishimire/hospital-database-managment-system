<?php
require_once 'connection.php';
echo "--- Patient Cases ---\n";
$res = $conn->query("SELECT * FROM patient_cases");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>