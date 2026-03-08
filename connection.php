<?php
//  Database connection helper for Hospital Management System
 
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("<h2 style='color:#c00;'> Database connection failed: " . $conn->connect_error . "</h2>");
}
session_start();
// Ensure UTF-8 charset
$conn->set_charset('utf8mb4');
?>