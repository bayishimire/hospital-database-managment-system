<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: All logged in users can access appointments, filtered by role
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$rel_id = $_SESSION['related_id'] ?? 0;
?>
<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-calendar-check"></i> Clinical Appointments & scheduling</h2>
        <div class="badge badge-success">
            <i class="fa-solid fa-clock"></i> Local Time:
            <?= date('H:i') ?>
        </div>
    </div>

    <!-- Appointment Scheduling Form (Visible to Staff, Doctors, and Patients for self) -->
    <?php if (in_array($role, ['SuperAdmin', 'Doctor', 'Staff', 'Service'])): ?>
        <section class="card"
            style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 2.5rem; background: #f8fafc;">
            <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-calendar-plus"></i> Schedule New
                Appointment</h3>
            <form method="POST">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem;">Select
                            Patient</label>
                        <select name="patient_id" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <option value="">-- Choose Patient --</option>
                            <?php
                            $patRes = $conn->query("SELECT patient_id, first_name, last_name FROM patients");
                            while ($p = $patRes->fetch_assoc()) {
                                echo "<option value='{$p['patient_id']}'>{$p['first_name']} {$p['last_name']} (ID: {$p['patient_id']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem;">Select
                            Specialist</label>
                        <select name="doctor_id" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <option value="">-- Choose Doctor --</option>
                            <?php
                            $docRes = $conn->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors");
                            while ($d = $docRes->fetch_assoc()) {
                                echo "<option value='{$d['doctor_id']}'>Dr. {$d['first_name']} {$d['last_name']} ({$d['specialization']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem;">Date &
                            Time</label>
                        <input type="datetime-local" name="appointment_date" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <div style="grid-column: span 1;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem;">Reason /
                            Service</label>
                        <input type="text" name="reason" placeholder="e.g. Annual Checkup"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                </div>
                <button type="submit" name="schedule_appointment" class="btn btn-primary" style="margin-top: 1.5rem;">
                    <i class="fa-solid fa-calendar-check"></i> Book Appointment
                </button>
            </form>

            <?php
            if (isset($_POST['schedule_appointment'])) {
                $pid = (int) $_POST['patient_id'];
                $did = (int) $_POST['doctor_id'];
                $date = $_POST['appointment_date'];
                $reason = $_POST['reason'];
                $status = 'Scheduled';

                $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, reason, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $pid, $did, $date, $reason, $status);
                if ($stmt->execute()) {
                    echo "<div style='margin-top:1rem; padding:0.75rem; background:#ecfdf5; color:#065f46; border-radius:8px; border:1px solid #a7f3d0;'>✅ Appointment scheduled successfully.</div>";
                } else {
                    echo "<div style='margin-top:1rem; padding:0.75rem; background:#fff1f2; color:#be123c; border-radius:8px; border:1px solid #fecdd3;'>❌ Error: " . $conn->error . "</div>";
                }
            }
            ?>
        </section>
    <?php endif; ?>

    <!-- Appointments Roster -->
    <h3 class="card-title" style="margin-top: 1rem;"><i class="fa-solid fa-list-check"></i> Scheduled clinical slots
    </h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Patient Identification</th>
                    <th>Consulting Doctor</th>
                    <th>Service Reason</th>
                    <th>Registry Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Build Filtered Logic
                $sql = "SELECT a.*, p.first_name as p_fname, p.last_name as p_lname, d.first_name as d_fname, d.last_name as d_lname 
                        FROM appointments a
                        JOIN patients p ON a.patient_id = p.patient_id
                        JOIN doctors d ON a.doctor_id = d.doctor_id";

                if ($role == 'Doctor') {
                    $sql .= " WHERE a.doctor_id = $rel_id";
                } elseif ($role == 'Patient') {
                    $sql .= " WHERE a.patient_id = $rel_id";
                }

                $sql .= " ORDER BY a.appointment_date ASC";
                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $stsClass = 'badge-pending';
                        if ($row['status'] == 'Completed')
                            $stsClass = 'badge-success';
                        if ($row['status'] == 'Cancelled')
                            $stsClass = 'badge-danger';

                        echo "<tr>";
                        echo "<td><i class='fa-solid fa-clock-rotate-left' style='color:var(--primary); margin-right:5px;'></i> " . date('M d, H:i', strtotime($row['appointment_date'])) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($row['p_fname'] . " " . $row['p_lname']) . "</strong></td>";
                        echo "<td>Dr. " . htmlspecialchars($row['d_fname'] . " " . $row['d_lname']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
                        echo "<td><span class='badge $stsClass'>{$row['status']}</span></td>";
                        echo "<td>";
                        if ($role != 'Patient' && $row['status'] == 'Scheduled') {
                            echo "<a href='?complete={$row['appointment_id']}' class='btn' style='color:#059669; background:#ecfdf5; padding:4px 8px; border-radius:6px; font-size:0.75rem;' title='Mark Completed'><i class='fa-solid fa-check'></i></a>";
                            echo "&nbsp;<a href='?cancel={$row['appointment_id']}' class='btn' style='color:#ef4444; background:#fff1f2; padding:4px 8px; border-radius:6px; font-size:0.75rem;' title='Cancel'><i class='fa-solid fa-xmark'></i></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center; padding: 2rem; color: var(--text-muted);'>No appointments found for your identification.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Handle status changes
if (isset($_GET['complete'])) {
    $id = (int) $_GET['complete'];
    $conn->query("UPDATE appointments SET status = 'Completed' WHERE appointment_id = $id");
    echo "<script>window.location='appointments.php';</script>";
}
if (isset($_GET['cancel'])) {
    $id = (int) $_GET['cancel'];
    $conn->query("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = $id");
    echo "<script>window.location='appointments.php';</script>";
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>