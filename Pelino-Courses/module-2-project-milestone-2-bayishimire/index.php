<?php
require_once __DIR__ . '/connection.php';
?>
<?php include 'header.php'; ?>
<div class="card">
    <h2>Welcome to the Hospital Management System</h2>
    <p>This is a professional, premium‑styled interface for managing all aspects of the hospital.</p>
    <ul>
        <li><a href="dashboard.php">Dashboard – quick overview</a></li>
        <li><a href="patients.php">Patients management</a></li>
        <li><a href="doctors.php">Doctors management</a></li>
        <li><a href="appointments.php">Appointments schedule</a></li>
        <li><a href="billing.php">Billing & payments</a></li>
    </ul>
</div>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>