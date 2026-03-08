<?php
require_once 'connection.php';
$tables = ['patient_cases', 'medicalrecords'];
foreach ($tables as $table) {
    echo "--- Schema for $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Table $table not found.\n";
    }
}
?>