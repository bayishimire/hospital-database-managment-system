<?php
/**
 * Hospital Management System – Database connection helper
 * Adjust credentials if your environment differs.
 */
$host = '127.0.0.1';
$user = 'root';
$pass = 'sandrine';
$db = 'hospital_database_management_system_hdms'; // target database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<h2 style='color:#c00;'>❌ Database connection failed: " . $conn->connect_error . "</h2>");
}

// Ensure UTF‑8 for all queries
$conn->set_charset('utf8mb4');
?>