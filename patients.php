<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: Only Staff, Service, Doctor and SuperAdmin can access the registry
if (!in_array($_SESSION['role'], ['SuperAdmin', 'Staff', 'Service', 'Doctor'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-users"></i> Patient Intake & Verification</h2>
        <a href="#intakeForm" class="btn btn-primary"><i class="fa-solid fa-plus-circle"></i> New Patient Arrival</a>
    </div>

    <!-- Register New Patient (Intake) -->
    <section id="intakeForm" class="card" style="box-shadow: none; border: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-id-card"></i> Intake & Insurance
            Verification</h3>
        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">Verify patient identity and
            insurance coverage (Service Support).</p>

        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">First Name</label>
                    <input type="text" name="first_name" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Last Name</label>
                    <input type="text" name="last_name" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Email (Optional)</label>
                    <input type="email" name="email"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Insurance / Service
                        Support</label>
                    <select name="insurance"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                        <option value="None">No Insurance (Private Pay)</option>
                        <option value="National Support">National Service Support</option>
                        <option value="Private Gold">Private Gold Insurance</option>
                        <option value="Employer Plus">Employer Plus Plan</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Date of Birth</label>
                    <input type="date" name="dob" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Gender</label>
                    <select name="gender" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Phone</label>
                    <input type="text" name="phone"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 500;">Address</label>
                    <input type="text" name="address"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
            </div>
            <button type="submit" name="add_patient" class="btn btn-primary" style="margin-top: 1.5rem;">
                <i class="fa-solid fa-user-check"></i> Verify & Register Patient
            </button>
        </form>

        <?php
        if (isset($_POST['add_patient'])) {
            $f = $_POST['first_name'];
            $l = $_POST['last_name'];
            $e = $_POST['email'];
            $i = $_POST['insurance'];
            $d = $_POST['dob'];
            $g = $_POST['gender'];
            $p = $_POST['phone'];
            $a = $_POST['address'];

            $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, email, insurance, date_of_birth, gender, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $f, $l, $e, $i, $d, $g, $p, $a);
            if ($stmt->execute()) {
                echo "<div style='margin-top: 1rem; padding: 1rem; background: #ecfdf5; color: #065f46; border-radius: 8px; font-size: 0.9rem; border: 1px solid #a7f3d0;'>
                        <i class='fa-solid fa-circle-check'></i> Patient verified and registered successfully.
                      </div>";
            }
        }
        ?>
    </section>

    <!-- Patient Directory -->
    <h3 class="card-title" style="margin-top: 2.5rem;"><i class="fa-solid fa-address-book"></i> Verified Patient
        Directory</h3>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>PID</th>
                    <th>Full Name</th>
                    <th>Insurance Coverage</th>
                    <th>Contact</th>
                    <th>Manage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM patients ORDER BY patient_id DESC");
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $insClass = ($row['insurance'] == 'None') ? 'badge-danger' : 'badge-success';
                        echo "<tr>";
                        echo "<td><span style='color: var(--text-muted); font-family: monospace;'>#" . str_pad($row['patient_id'], 4, '0', STR_PAD_LEFT) . "</span></td>";
                        echo "<td><strong>" . htmlspecialchars($row['first_name'] . " " . $row['last_name']) . "</strong></td>";
                        echo "<td><span class='badge $insClass'>" . $row['insurance'] . "</span></td>";
                        echo "<td>" . htmlspecialchars($row['phone'] ?? $row['email']) . "</td>";
                        echo "<td>
                                <a href='doctor_portal.php?patient_id=" . $row['patient_id'] . "' title='Send to Doctor' class='btn btn-primary' style='padding: 5px 10px; font-size: 0.75rem;'>
                                    <i class='fa-solid fa-stethoscope'></i> Consult
                                </a>
                                <a href='?del=" . $row['patient_id'] . "' onclick=\"return confirm('Delete record?')\" style='color: #ef4444; margin-left: 10px;'>
                                    <i class='fa-solid fa-trash'></i>
                                </a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center;'>No patient records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php
if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];

    // Start deletion of related records to satisfy foreign key constraints
    $conn->begin_transaction();
    try {
        // 1. Delete prescriptions linked to medical records of this patient
        $conn->query("DELETE FROM prescriptions WHERE record_id IN (SELECT record_id FROM medicalrecords WHERE patient_id = $id)");

        // 2. Delete medical records
        $conn->query("DELETE FROM medicalrecords WHERE patient_id = $id");

        // 3. Delete appointments
        $conn->query("DELETE FROM appointments WHERE patient_id = $id");

        // 4. Delete billing
        $conn->query("DELETE FROM billing WHERE patient_id = $id");

        // 5. Delete patient cases
        $conn->query("DELETE FROM patient_cases WHERE patient_id = $id");

        // 6. Delete patient rooms assignments
        $conn->query("DELETE FROM patient_rooms WHERE patient_id = $id");

        // 7. Finally delete the patient
        $conn->query("DELETE FROM patients WHERE patient_id = $id");

        $conn->commit();
        echo "<script>window.location='patients.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error deleting patient: " . addslashes($e->getMessage()) . "'); window.location='patients.php';</script>";
    }
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>