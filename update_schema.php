<?php
require_once 'connection.php';

$sql1 = "ALTER TABLE patient_cases ADD COLUMN chief_complaint TEXT AFTER insurance_id";
$sql2 = "ALTER TABLE patient_cases ADD COLUMN vitals VARCHAR(255) AFTER chief_complaint";

if ($conn->query($sql1) === TRUE) {
    echo "Column 'chief_complaint' added successfully.\n";
} else {
    echo "Error adding column 'chief_complaint': " . $conn->error . "\n";
}

if ($conn->query($sql2) === TRUE) {
    echo "Column 'vitals' added successfully.\n";
} else {
    echo "Error adding column 'vitals': " . $conn->error . "\n";
}

$conn->close();
?>