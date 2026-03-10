<?php
require_once __DIR__ . '/connection.php';
?>
<?php include 'header.php'; ?>
<div class="card">
    <h2>Dashboard Overview</h2>
    <p>Quick statistics from your Hospital Management System.</p>
    <ul>
        <?php
        // Patients count
        $res = $conn->query('SELECT COUNT(*) AS cnt FROM patients');
        $row = $res->fetch_assoc();
        echo '<li><strong>Patients:</strong> ' . $row['cnt'] . '</li>';
        // Doctors count
        $res = $conn->query('SELECT COUNT(*) AS cnt FROM doctors');
        $row = $res->fetch_assoc();
        echo '<li><strong>Doctors:</strong> ' . $row['cnt'] . '</li>';
        // Appointments count
        $res = $conn->query('SELECT COUNT(*) AS cnt FROM appointments');
        $row = $res->fetch_assoc();
        echo '<li><strong>Appointments:</strong> ' . $row['cnt'] . '</li>';
        ?>
    </ul>
</div>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>