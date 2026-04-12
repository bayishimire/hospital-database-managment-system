<?php
require_once 'connection.php';

$sql = "CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    require_strong_passwords TINYINT(1) DEFAULT 0,
    enforce_2fa TINYINT(1) DEFAULT 0,
    session_timeout INT DEFAULT 30,
    email_alerts TINYINT(1) DEFAULT 0,
    critical_sms TINYINT(1) DEFAULT 0,
    maintenance_warnings TINYINT(1) DEFAULT 0
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'system_settings' created successfully.\n";
    
    // Initialize with default values if empty
    $check = $conn->query("SELECT COUNT(*) FROM system_settings");
    if ($check && $check->fetch_row()[0] == 0) {
        $conn->query("INSERT INTO system_settings (id) VALUES (1)");
        echo "Initialized default settings.\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
