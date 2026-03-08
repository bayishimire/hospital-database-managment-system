<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: Only doctor and SuperAdmin
if (!in_array($_SESSION['role'], ['SuperAdmin', 'Doctor'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'header.php'; ?>

<head>
    <meta charset="UTF-8">
    <title>Specialist Doctor Portal</title>
    <meta http-equiv="refresh" content="30">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-stethoscope"></i> Specialist Doctor Portal</h2>
        <div class="badge badge-success">
            <i class="fa-solid fa-user-doctor"></i> Active Session: <?= htmlspecialchars($_SESSION['username']) ?>
        </div>
    </div>

    <!-- Incoming 'Fish' (Referrals) -->
    <section class="card" style="box-shadow: none; border: 1px solid var(--border); background: #f8fafc;">
        <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-inbox"></i> Incoming Clinical Cards
            ("The Fish")</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">Patients automatically referred
            from Reception after insurance verification.</p>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><i class="fa-solid fa-calendar-day"></i> Intake Date/Time</th>
                        <th><i class="fa-solid fa-user"></i> Patient Identity</th>
                        <th><i class="fa-solid fa-notes-medical"></i> Clinical Summary</th>
                        <th><i class="fa-solid fa-shield-heart"></i> Insurance</th>
                        <th><i class="fa-solid fa-users-viewfinder"></i> Family Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get cases specifically assigned to this logged-in doctor
                    $doc_ref_id = $_SESSION['related_id'] ?? 0;
                    $role = $_SESSION['role'] ?? 'Doctor';

                    $condition = "pc.status = 'Pending'";
                    if ($role !== 'SuperAdmin') {
                        $condition .= " AND (pc.doctor_id = $doc_ref_id OR pc.doctor_id = 0)";
                    }

                    $sql = "SELECT pc.*, p.first_name, p.last_name, p.national_id, p.parent_name, p.district, p.cell, p.insurance, p.is_head_of_family 
                            FROM patient_cases pc
                            JOIN patients p ON pc.patient_id = p.patient_id
                            WHERE $condition
                            ORDER BY pc.created_at DESC";
                    $res = $conn->query($sql);
                    if ($res && $res->num_rows > 0) {
                        while ($row = $res->fetch_assoc()) {
                            $headIcon = $row['is_head_of_family'] ? '<span class="badge badge-success"><i class="fa-solid fa-house-chimney-user"></i> Head</span>' : '<span class="badge badge-pending"><i class="fa-solid fa-user"></i> Member</span>';
                            $vitals = $row['vitals'] ? "<br><small><i class='fa-solid fa-heart-pulse' style='color:#ef4444;'></i> <strong>Vitals:</strong> {$row['vitals']}</small>" : "";
                            $complaint = $row['chief_complaint'] ? "<strong>Reason:</strong> " . htmlspecialchars($row['chief_complaint']) : "No complaint recorded.";

                            echo "<tr style='background: #fff; transition: transform 0.2s;'>";
                            echo "<td>
                                    <div style='font-size:0.85rem; font-weight:600; color:var(--primary);'>
                                        <i class='fa-solid fa-clock'></i> " . date('Y-m-d', strtotime($row['created_at'])) . "
                                    </div>
                                    <div style='font-size:0.75rem; color:var(--text-muted);'>
                                        " . date('H:i', strtotime($row['created_at'])) . "
                                    </div>
                                  </td>";
                            echo "<td>
                                    <div style='font-weight:700;'>{$row['first_name']} {$row['last_name']}</div>
                                    <div style='font-size:0.75rem; color:var(--text-muted);'>ID: {$row['national_id']}</div>
                                  </td>";
                            echo "<td style='max-width:250px;'>
                                    <div style='font-size:0.85rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;'>$complaint</div>
                                    $vitals
                                  </td>";
                            echo "<td><span class='badge badge-success'><i class='fa-solid fa-hand-holding-medical'></i> {$row['insurance']}</span></td>";
                            echo "<td>$headIcon</td>";
                            echo "<td>
                                    <a href='?open_case={$row['case_id']}' class='btn btn-primary' style='padding: 8px 12px; font-size: 0.8rem; border-radius:8px;'>
                                        <i class='fa-solid fa-eye'></i> Open Fish
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center;'>No incoming clinical cards.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Specific Patient Consultation (If case is open) -->
    <?php
    if (isset($_GET['open_case'])) {
        $cid = (int) $_GET['open_case'];
        $res = $conn->query("SELECT pc.*, p.* FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id WHERE pc.case_id = $cid");
        $case = $res->fetch_assoc();
        if ($case) {
            ?>
            <section class="card" style="margin-top: 2rem; border: 2px solid var(--primary); background: #fff;">
                <h3 class="card-title"><i class="fa-solid fa-comment-medical"></i> Consultation:
                    <?= $case['first_name'] . " " . $case['last_name'] ?>
                </h3>

                <form method="POST">
                    <input type="hidden" name="case_id" value="<?= $case['case_id'] ?>">
                    <input type="hidden" name="patient_id" value="<?= $case['patient_id'] ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div
                            style="grid-column: span 2; display: flex; flex-direction: column; gap: 1rem; background: #f1f5f9; padding: 1.5rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <label
                                        style="font-weight: 700; font-size: 0.75rem; color: var(--primary); text-transform: uppercase;"><i
                                            class="fa-solid fa-comment-dots"></i> Chief Complaint / Symptoms</label>
                                    <div
                                        style="font-size: 1rem; margin-top: 0.5rem; background: white; padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
                                        <?= nl2br(htmlspecialchars($case['chief_complaint'])) ?>
                                    </div>
                                </div>
                                <div style="width: 250px; margin-left: 1.5rem;">
                                    <label
                                        style="font-weight: 700; font-size: 0.75rem; color: var(--accent); text-transform: uppercase;"><i
                                            class="fa-solid fa-gauge-high"></i> Vitals Captured</label>
                                    <div
                                        style="font-size: 1.1rem; font-weight: 700; margin-top: 0.5rem; color: var(--text-main);">
                                        <?= htmlspecialchars($case['vitals'] ?: 'No vitals recorded') ?>
                                    </div>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <label style="font-weight: 600; font-size: 0.8rem;">PATIENT EMAIL (UPDATE OPTIONAL)</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($case['email']) ?>"
                                    placeholder="email@example.com"
                                    style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); margin-top:0.5rem;">
                            </div>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.8rem;">DIAGNOSIS (TYPE OF DISEASE)</label>
                            <textarea name="diagnosis" required
                                style="width:100%; height:100px; padding:0.75rem; border-radius:8px; border:1px solid var(--border);"></textarea>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.8rem;">TREATMENT & PRESCRIPTION</label>
                            <textarea name="treatment" required
                                style="width:100%; height:100px; padding:0.75rem; border-radius:8px; border:1px solid var(--border);"></textarea>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.8rem;">MEDICAL TEST RECORDS</label>
                            <textarea name="tests" placeholder="Lab results, BP, Pulse..."
                                style="width:100%; height:80px; padding:0.75rem; border-radius:8px; border:1px solid var(--border);"></textarea>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.8rem;">OPERATION / SURGERY SUMMARY</label>
                            <textarea name="operation" placeholder="If operation was performed..."
                                style="width:100%; height:80px; padding:0.75rem; border-radius:8px; border:1px solid var(--border);"></textarea>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.8rem;">WARD ADMISSION (BEDSICK ROOM)?</label>
                            <select name="admission"
                                style="width:100%; padding:0.75rem; border-radius:8px; border:1px solid var(--border); background:#fff;">
                                <option value="No">No (Outpatient)</option>
                                <option value="Yes">Yes (Admit to Ward)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="complete_consultation" class="btn btn-primary"
                        style="margin-top: 2rem; width: 100%; justify-content: center; height: 50px; font-weight: 700;">
                        <i class="fa-solid fa-cloud-arrow-up"></i> SECURE CONSULTATION & SEND TO BILLING
                    </button>
                </form>
            </section>
            <?php
        }
    }

    if (isset($_POST['complete_consultation'])) {
        $cid = (int) $_POST['case_id'];
        $pid = (int) $_POST['patient_id'];
        $diag = $_POST['diagnosis'];
        $treat = $_POST['treatment'];
        $tests = $_POST['tests'];
        $ops = $_POST['operation'];
        $email = $_POST['email'];
        $date = date('Y-m-d H:i:s');

        // Update patient email if provided
        $conn->query("UPDATE patients SET email = '$email' WHERE patient_id = $pid");

        // Save Medical Record (Using linked Doctor ID from session)
        $doc_id = $_SESSION['related_id'] ?? 1;
        $stmt = $conn->prepare("INSERT INTO medicalrecords (patient_id, doctor_id, diagnosis, treatment, test_results, operation_details, record_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $pid, $doc_id, $diag, $treat, $tests, $ops, $date);

        if ($stmt->execute()) {
            // Update Case Status
            $conn->query("UPDATE patient_cases SET status = 'Completed' WHERE case_id = $cid");

            if ($_POST['admission'] == 'Yes') {
                echo "<script>alert('Consultation Recorded. Redirecting to Ward Management.'); window.location='manage_rooms.php?patient_id=$pid';</script>";
            } else {
                echo "<script>alert('Consultation Recorded. Redirecting to Payment/Billing Service.'); window.location='billing.php?patient_id=$pid';</script>";
            }
        }
    }
    ?>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>