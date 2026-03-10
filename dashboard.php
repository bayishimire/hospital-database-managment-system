<?php require_once __DIR__ . '/connection.php'; ?>
<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$related_id = $_SESSION['related_id'] ?? 0;

// HANDLE LAB COMPLETION (Return to Doctor)
if (isset($_GET['complete_lab']) && in_array($role, ['SuperAdmin', 'Staff', 'Reception', 'Admin'])) {
    $cid = (int) $_GET['complete_lab'];
    $conn->query("UPDATE patient_cases SET status = 'Pending' WHERE case_id = $cid");
    header("Location: dashboard.php?lab_sent_back=1");
    exit();
}

// ─── DATA AGGREGATION ────────────────────────────────────────────────────────

// All roles get these base counts
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0] ?? 0;
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0] ?? 0;
$availRooms = $conn->query("SELECT COUNT(*) FROM rooms WHERE availability_status = 'Available'")->fetch_row()[0] ?? 0;
$pendingQueue = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'Pending'")->fetch_row()[0] ?? 0;
$labQueue = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'LabPending'")->fetch_row()[0] ?? 0;

// Role-specific data
if ($role == 'SuperAdmin') {
    $revenueToday = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing WHERE DATE(billing_date) = CURDATE()")->fetch_row()[0] ?? 0;
    $totalStaff = $conn->query("SELECT COUNT(*) FROM staff")->fetch_row()[0] ?? 0;

    // Charts
    $arrLabels = [];
    $arrData = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $arrLabels[] = date('D d', strtotime($d));
        $cnt = $conn->query("SELECT COUNT(*) FROM patients WHERE patient_id % 7 = $i")->fetch_row()[0] ?? 0;
        $arrData[] = (int) $cnt;
    }
    $revLabels = [];
    $revData = [];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $revLabels[] = date('D d', strtotime($d));
        $amt = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing WHERE DATE(billing_date) = '$d'")->fetch_row()[0] ?? 0;
        $revData[] = (float) $amt;
    }
} elseif ($role == 'Doctor') {
    $myCases = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE doctor_id = $related_id AND status = 'Pending'")->fetch_row()[0] ?? 0;
    $myConsults = $conn->query("SELECT COUNT(*) FROM medicalrecords WHERE doctor_id = $related_id")->fetch_row()[0] ?? 0;
} elseif ($role == 'Patient') {
    $myBills = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing WHERE patient_id = $related_id AND payment_status = 'Pending'")->fetch_row()[0] ?? 0;
    $myRecords = $conn->query("SELECT COUNT(*) FROM medicalrecords WHERE patient_id = $related_id")->fetch_row()[0] ?? 0;
}
?>
<?php include 'header.php'; ?>

<style>
    .dashboard-container {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    /* Premium Stat Cards */
    .stat-card {
        background: white;
        padding: 28px;
        border-radius: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1);
        border-color: var(--primary);
    }

    .stat-icon-bg {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 5rem;
        color: var(--primary);
        opacity: 0.03;
        transform: rotate(15deg);
        z-index: 0;
    }

    .stat-icon-main {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        z-index: 1;
        position: relative;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }

    .stat-val {
        font-size: 2.6rem;
        font-weight: 900;
        color: var(--text-main);
        line-height: 1;
        margin-bottom: 6px;
        z-index: 1;
        position: relative;
        letter-spacing: -0.02em;
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        z-index: 1;
        position: relative;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .shortcut-btn {
        position: absolute;
        top: 24px;
        right: 24px;
        width: 36px;
        height: 36px;
        background: #f8fafc;
        color: var(--text-muted);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: 0.3s;
        z-index: 2;
        border: 1px solid var(--border);
    }

    .shortcut-btn:hover {
        background: var(--primary);
        color: white;
        transform: rotate(15deg) scale(1.1);
    }

    /* Dashboard Content Grid */
    .dashboard-content {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 1100px) {
        .dashboard-content {
            grid-template-columns: 1fr;
        }
    }

    .feature-card {
        border-radius: 24px;
        border: 1px solid var(--border);
        padding: 30px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .pulse-circle {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #10b981;
        position: relative;
    }

    .pulse-circle::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #10b981;
        animation: pulse-ring 2s infinite;
    }

    @keyframes pulse-ring {
        0% {
            transform: scale(1);
            opacity: 0.5;
        }

        100% {
            transform: scale(3);
            opacity: 0;
        }
    }

    .role-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 3.5rem;
        background: #fff;
        padding: 25px 35px;
        border-radius: 24px;
        border: 1px solid var(--border);
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--primary), #4f46e5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        border-radius: 16px;
        background: #f8fafc;
        transition: 0.2s;
        border: 1px solid transparent;
    }

    .activity-item:hover {
        background: #fff;
        border-color: var(--border);
        transform: translateX(5px);
    }

    /* CONSOLE BOXES FOR BILLING DASHBOARD WIDGET */
    .console-box {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 0;
        overflow: hidden;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        text-decoration: none !important;
        color: inherit !important;
        margin-bottom: 1rem;
    }

    .console-box:hover {
        transform: translateY(-5px);
        border-color: #2563eb;
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1);
    }

    .console-header {
        padding: 1.2rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .console-body {
        padding: 1.2rem;
        flex-grow: 1;
    }

    .console-footer {
        padding: 0.8rem 1.2rem;
        background: #2563eb;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 0.75rem;
    }
</style>

<div class="dashboard-container">

    <!-- ── DYNAMIC ROLE HEADER ── -->
    <div class="role-header">
        <div class="user-info">
            <div class="user-avatar" style="overflow:hidden;">
                <?php
                $pImg = $_SESSION['profile_image'] ?? '';
                $pExists = (!empty($pImg) && file_exists(__DIR__ . '/' . $pImg));

                if ($pExists):
                    ?>
                    <img src="<?= htmlspecialchars($pImg) ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <?php
                    if ($role == 'SuperAdmin')
                        echo '<i class="fa-solid fa-crown"></i>';
                    elseif ($role == 'Doctor')
                        echo '<i class="fa-solid fa-stethoscope"></i>';
                    elseif ($role == 'Patient')
                        echo '<i class="fa-solid fa-user-injured"></i>';
                    else
                        echo '<i class="fa-solid fa-user-tie"></i>';
                    ?>
                <?php endif; ?>
            </div>
            <div>
                <div style="display:flex; align-items:center; gap:10px;">
                    <h1 style="margin:0; font-size:1.8rem; font-weight:950; letter-spacing:-0.03em;">Hello,
                        <?= $_SESSION['username'] ?>
                    </h1>
                    <span
                        style="background:var(--primary); color:white; padding:4px 12px; border-radius:50px; font-size:0.7rem; font-weight:800; text-transform:uppercase;"><?= $role ?></span>
                </div>
                <p
                    style="margin:5px 0 0; color:var(--text-muted); font-weight:600; display:flex; align-items:center; gap:8px;">
                    <span class="pulse-circle"></span> System Status: Online &bull; <?= date('l, d F Y') ?>
                </p>
            </div>
        </div>
        <div style="display:flex; gap:15px;">
            <div class="badge badge-success" style="padding:12px 20px; border-radius:14px; font-weight:700;">
                <i class="fa-solid fa-shield-check"></i> SECURE SESSION
            </div>
            <?php if ($role == 'SuperAdmin'): ?>
                <a href="admin_dashboard.php" class="btn btn-primary"
                    style="padding:12px 20px; border-radius:14px; background:#1e293b; color:#fff; font-weight:700;">
                    <i class="fa-solid fa-rocket"></i> OPEN MISSION CONTROL
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── KPI GRID: TAILORED FOR EACH ROLE ── -->
    <div class="stats-grid">

        <?php if ($role == 'SuperAdmin'): ?>
            <!-- SuperAdmin View: Analytics & Revenue -->
            <div class="stat-card">
                <i class="fa-solid fa-hospital-user stat-icon-bg"></i>
                <a href="patients.php" class="shortcut-btn" title="View Registry"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#eff6ff; color:#2563eb;"><i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-val"><?= $totalPatients ?></div>
                <div class="stat-label">Total Patient Registry</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-user-doctor stat-icon-bg"></i>
                <a href="doctor_portal.php" class="shortcut-btn" title="Manage Doctors"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#f5f3ff; color:#7c3aed;"><i
                        class="fa-solid fa-stethoscope"></i></div>
                <div class="stat-val"><?= $totalDoctors ?></div>
                <div class="stat-label">Active Medical Staff</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-wallet stat-icon-bg"></i>
                <a href="billing.php" class="shortcut-btn" title="Revenue Hub"><i class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#fefce8; color:#ca8a04;"><i
                        class="fa-solid fa-sack-dollar"></i></div>
                <div class="stat-val">$<?= number_format($revenueToday, 0) ?></div>
                <div class="stat-label">Revenue Generated Today</div>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid #ef4444;">
                <i class="fa-solid fa-fire stat-icon-bg"></i>
                <a href="intake_service.php" class="shortcut-btn" style="color:#ef4444" title="Emergency Intake"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#fff1f2; color:#e11d48;"><i
                        class="fa-solid fa-clipboard-list"></i></div>
                <div class="stat-val" style="color:#e11d48;"><?= $pendingQueue ?></div>
                <div class="stat-label">Pending Referral Cases</div>
            </div>

        <?php elseif ($role == 'Doctor'): ?>
            <!-- Doctor View: My Clinic & Patients -->
            <div class="stat-card" style="border-bottom: 4px solid #f97316;">
                <i class="fa-solid fa-user-clock stat-icon-bg"></i>
                <a href="doctor_portal.php" class="shortcut-btn" style="color:#f97316" title="My Cases"><i
                        class="fa-solid fa-arrow-right"></i></a>
                <div class="stat-icon-main" style="background:#fff7ed; color:#ea580c;"><i
                        class="fa-solid fa-book-medical"></i></div>
                <div class="stat-val" style="color:#ea580c;"><?= $myCases ?></div>
                <div class="stat-label">My Pending Consultations</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-calendar-check stat-icon-bg"></i>
                <a href="appointments.php" class="shortcut-btn" title="My Schedule"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#eff6ff; color:#2563eb;"><i
                        class="fa-solid fa-calendar-day"></i></div>
                <div class="stat-val"><?= $totalPatients ?></div>
                <div class="stat-label">Patient Database Registry</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-file-waveform stat-icon-bg"></i>
                <div class="stat-icon-main" style="background:#f0fdf4; color:#16a34a;"><i
                        class="fa-solid fa-user-check"></i></div>
                <div class="stat-val"><?= $myConsults ?></div>
                <div class="stat-label">Lifetime Consultations</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-bed stat-icon-bg"></i>
                <a href="manage_rooms.php" class="shortcut-btn" title="Room Status"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#ecfdf5; color:#059669;"><i class="fa-solid fa-door-open"></i>
                </div>
                <div class="stat-val"><?= $availRooms ?></div>
                <div class="stat-label">Hospital Beds Available</div>
            </div>

        <?php elseif ($role == 'Patient'): ?>
            <!-- Patient View: Personal Health & Billing -->
            <div class="stat-card">
                <i class="fa-solid fa-file-prescription stat-icon-bg"></i>
                <a href="doctor_portal.php" class="shortcut-btn" title="My History"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#eff6ff; color:#2563eb;"><i
                        class="fa-solid fa-folder-medical"></i></div>
                <div class="stat-val"><?= $myRecords ?></div>
                <div class="stat-label">My Medical Record Logs</div>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid #f59e0b;">
                <i class="fa-solid fa-file-invoice-dollar stat-icon-bg"></i>
                <a href="billing.php" class="shortcut-btn" style="color:#f59e0b" title="Pay Now"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#fffbeb; color:#d97706;"><i class="fa-solid fa-wallet"></i>
                </div>
                <div class="stat-val" style="color:#d97706;">$<?= number_format($myBills, 2) ?></div>
                <div class="stat-label">Outstanding Health Balance</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-user-doctor stat-icon-bg"></i>
                <div class="stat-icon-main" style="background:#f5f3ff; color:#7c3aed;"><i class="fa-solid fa-user-md"></i>
                </div>
                <div class="stat-val"><?= $totalDoctors ?></div>
                <div class="stat-label">Active Doctors Available</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-hospital stat-icon-bg"></i>
                <div class="stat-icon-main" style="background:#ecfdf5; color:#10b981;"><i
                        class="fa-solid fa-circle-check"></i></div>
                <div class="stat-val">100%</div>
                <div class="stat-label">System Node Connectivity</div>
            </div>

        <?php else: // Staff / Service / Reception ?>
            <div class="stat-card" style="border-bottom: 4px solid #2563eb;">
                <i class="fa-solid fa-clipboard-user stat-icon-bg"></i>
                <a href="intake_service.php" class="shortcut-btn" style="color:#2563eb" title="Start Intake"><i
                        class="fa-solid fa-plus"></i></a>
                <div class="stat-icon-main" style="background:#eff6ff; color:#2563eb;"><i class="fa-solid fa-id-card"></i>
                </div>
                <div class="stat-val" style="color:#2563eb;"><?= $pendingQueue ?></div>
                <div class="stat-label">Patients Awaiting Intake</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-bed stat-icon-bg"></i>
                <a href="manage_rooms.php" class="shortcut-btn" title="Room Control"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#ecfdf5; color:#10b981;"><i class="fa-solid fa-door-open"></i>
                </div>
                <div class="stat-val"><?= $availRooms ?></div>
                <div class="stat-label">Available Care Stations</div>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-users stat-icon-bg"></i>
                <a href="patients.php" class="shortcut-btn" title="Patient Master"><i
                        class="fa-solid fa-chevron-right"></i></a>
                <div class="stat-icon-main" style="background:#f8fafc; color:var(--text-main);"><i
                        class="fa-solid fa-hospital-user"></i></div>
                <div class="stat-val"><?= $totalPatients ?></div>
                <div class="stat-label">Global Patient Registry</div>
            </div>
            <div class="stat-card" style="border-bottom: 4px solid #7c3aed;">
                <i class="fa-solid fa-microscope stat-icon-bg"></i>
                <div class="stat-icon-main" style="background:#f5f3ff; color:#7c3aed;"><i class="fa-solid fa-vials"></i>
                </div>
                <div class="stat-val" style="color:#7c3aed;"><?= $labQueue ?></div>
                <div class="stat-label">In Laboratory (Pending Tests)</div>
            </div>
        <?php endif; ?>

    </div>

    <!-- ── DYNAMIC CONTENT PANELS ── -->
    <div class="dashboard-content">

        <!-- MAIN PANEL: TAILORED CONTENT -->
        <div class="feature-card">
            <?php if ($role == 'SuperAdmin'): ?>
                <h2 class="feature-title"><i class="fa-solid fa-chart-area" style="color:var(--primary)"></i> System
                    Performance Analytics</h2>
                <div style="margin-bottom:30px;">
                    <canvas id="patientChart" height="110"></canvas>
                </div>
                <div style="background:#f8fafc; padding:20px; border-radius:16px; border:1px solid #f1f5f9;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h4
                            style="margin:0; font-size:0.9rem; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.05em;">
                            Financial Trend (7 Days)</h4>
                        <span class="badge badge-success">Live Stream</span>
                    </div>
                    <canvas id="revenueChart" height="150"></canvas>
                </div>

            <?php elseif ($role == 'Doctor'): ?>
                <h2 class="feature-title" style="justify-content:space-between;">
                    <span><i class="fa-solid fa-wave-square" style="color:#ef4444"></i> Live Consultation Queue</span>
                    <a href="doctor_portal.php" style="font-size:0.8rem; color:var(--primary); text-decoration:none;">View
                        All Case Records &rarr;</a>
                </h2>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Patient Identity</th>
                                <th>Submission Time</th>
                                <th>Security/Insurance</th>
                                <th>Clinical Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fish = $conn->query("SELECT pc.*, p.first_name, p.last_name, p.insurance FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id WHERE pc.doctor_id = $related_id AND pc.status = 'Pending' LIMIT 5");
                            while ($row = $fish->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div
                                                style="width:30px; height:30px; background:#eff6ff; border-radius:8px; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:0.8rem;">
                                                <i class="fa-solid fa-user"></i>
                                            </div>
                                            <strong><?= $row['first_name'] ?>         <?= $row['last_name'] ?></strong>
                                        </div>
                                    </td>
                                    <td><?= date('H:i', strtotime($row['created_at'])) ?> (Online)</td>
                                    <td><span class="badge badge-success"><?= $row['insurance'] ?></span></td>
                                    <td><a href="doctor_portal.php?case_id=<?= $row['case_id'] ?>" class="btn"
                                            style="padding:6px 15px; font-size:0.75rem; border-radius:10px;"><i
                                                class="fa-solid fa-comment-medical"></i> Open Case</a></td>
                                </tr>
                            <?php endwhile;
                            if ($fish->num_rows == 0)
                                echo "<tr><td colspan='4' class='text-muted' align='center' style='padding:40px;'>No active patients in your private queue.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($role == 'Patient'): ?>
                <h2 class="feature-title"><i class="fa-solid fa-notes-medical" style="color:#10b981"></i> My Recent Clinical
                    Logs</h2>
                <div style="display:grid; gap:16px;">
                    <?php
                    $recs = $conn->query("SELECT m.*, d.first_name, d.last_name FROM medicalrecords m JOIN doctors d ON m.doctor_id = d.doctor_id WHERE m.patient_id = $related_id ORDER BY m.record_date DESC LIMIT 4");
                    while ($r = $recs->fetch_assoc()):
                        ?>
                        <div class="activity-item">
                            <div
                                style="width:40px; height:40px; background:#eff6ff; border-radius:12px; display:flex; align-items:center; justify-content:center; color:var(--primary);">
                                <i class="fa-solid fa-file-medical-alt"></i>
                            </div>
                            <div style="flex:1;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong style="color:var(--text-main);">Clinical Consultation with Dr.
                                        <?= $r['first_name'] ?>         <?= $r['last_name'] ?></strong>
                                    <small class="text-muted"
                                        style="font-weight:700;"><?= date('M d, Y', strtotime($r['record_date'])) ?></small>
                                </div>
                                <p style="margin:4px 0 0; font-size:0.85rem; color:var(--text-muted);"><?= $r['diagnosis'] ?>
                                </p>
                            </div>
                        </div>
                    <?php endwhile;
                    if ($recs->num_rows == 0)
                        echo "<div style='text-align:center; padding:40px; color:var(--text-muted);'>No historical records found for your account.</div>"; ?>
                </div>

            <?php else: // Staff / Service Pipeline ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Column 1: Clinical & Lab Pipeline -->
                    <section>
                        <h2 class="feature-title"><i class="fa-solid fa-microscope" style="color:var(--primary)"></i>
                            Clinical & Lab Pipeline</h2>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Patient Registry</th>
                                        <th>Assigned To</th>
                                        <th>Diagnostic Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $fish = $conn->query("SELECT pc.*, p.first_name, p.last_name, p.insurance, d.first_name as dfname, d.last_name as dlname FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id LEFT JOIN doctors d ON pc.doctor_id = d.doctor_id WHERE pc.status IN ('Pending', 'LabPending') ORDER BY pc.created_at DESC LIMIT 5");
                                    if ($fish && $fish->num_rows > 0) {
                                        while ($row = $fish->fetch_assoc()) {
                                            $statusLabel = ($row['status'] == 'LabPending') ? "<span class='badge' style='background:#f5f3ff; color:#7c3aed;'>LAB PENDING</span>" : "<span class='badge badge-danger'>CLINIC PENDING</span>";
                                            $docName = ($row['dfname'] ? "Dr. " . $row['dfname'] : '<span class="text-muted">TBD</span>');
                                            $action = ($row['status'] == 'LabPending') ? "<a href='?complete_lab={$row['case_id']}' class='btn' style='padding:4px 10px; font-size:0.65rem; background:#10b981; color:white; border:none;'><i class='fa-solid fa-flask'></i> COMPLETE LAB</a>" : "<span class='text-muted' style='font-size:0.7rem;'>Doctor Treatment...</span>";
                                            echo "<tr>
                                                <td><strong>{$row['first_name']} {$row['last_name']}</strong></td>
                                                <td>$docName</td>
                                                <td>$statusLabel</td>
                                                <td style='text-align:right;'>$action</td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' align='center' style='padding:20px;' class='text-muted'>No active medical referrals.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Column 2: Financial Settlement (The "Console Boxes") -->
                    <section>
                        <h2 class="feature-title"><i class="fa-solid fa-headset" style="color:#2563eb"></i> Settlement
                            Console</h2>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php
                            $billingCases = $conn->query("SELECT pc.*, p.first_name, p.last_name, p.insurance, mr.treatment, d.first_name as dfname 
                                                         FROM patient_cases pc 
                                                         JOIN patients p ON pc.patient_id = p.patient_id 
                                                         LEFT JOIN medicalrecords mr ON p.patient_id = mr.patient_id AND mr.record_date >= pc.created_at
                                                         LEFT JOIN doctors d ON mr.doctor_id = d.doctor_id
                                                         WHERE pc.status = 'BillingPending' 
                                                         ORDER BY pc.created_at DESC LIMIT 3");
                            if ($billingCases && $billingCases->num_rows > 0):
                                while ($b = $billingCases->fetch_assoc()): ?>
                                    <a href="billing.php?patient_id=<?= $b['patient_id'] ?>" class="console-box">
                                        <div class="console-header">
                                            <strong
                                                style="font-size: 0.95rem;"><?= htmlspecialchars($b['first_name'] . ' ' . $b['last_name']) ?></strong>
                                            <span
                                                style="font-size: 0.6rem; background: #eff6ff; padding: 4px 8px; border-radius: 12px; font-weight: 800; color: #1e40af;">
                                                BY DR. <?= strtoupper($b['dfname'] ?? 'SYS') ?>
                                            </span>
                                        </div>
                                        <div class="console-body" style="padding: 1rem;">
                                            <div style="font-size: 0.7rem; color: #64748b; margin-bottom: 5px;">
                                                <i class="fa-solid fa-id-card"></i> INS:
                                                <strong><?= htmlspecialchars($b['insurance']) ?></strong>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #475569; font-style: italic;">
                                                <?= htmlspecialchars(substr($b['treatment'] ?? 'Medication Pending...', 0, 45)) ?>...
                                            </div>
                                        </div>
                                        <div class="console-footer" style="padding: 0.6rem;">
                                            <i class="fa-solid fa-calculator"></i> CALC BILL
                                        </div>
                                    </a>
                                <?php endwhile; else: ?>
                                <div
                                    style="text-align: center; padding: 2rem; border: 2px dashed #e2e8f0; border-radius: 20px; color: #94a3b8;">
                                    <i class="fa-solid fa-check-circle" style="font-size: 2rem; margin-bottom: 0.5rem;"></i>
                                    <p style="font-size: 0.8rem; font-weight: 600;">No pending payments.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </div>

        <!-- SIDE PANEL: QUICK ACCESS -->
        <div class="feature-card">
            <h2 class="feature-title"><i class="fa-solid fa-bolt" style="color:#f59e0b"></i> Control Actions</h2>
            <div style="display:flex; flex-direction:column; gap:12px;">
                <?php if ($role == 'SuperAdmin' || $role == 'Staff' || $role == 'Service'): ?>
                    <a href="intake_service.php" class="btn btn-primary"
                        style="justify-content:center; padding:15px; border-radius:14px; font-weight:700;"><i
                            class="fa-solid fa-plus-circle"></i> New Patient Intake</a>
                    <a href="patients.php" class="btn"
                        style="background:#f8fafc; color:var(--text-main); justify-content:center; padding:15px; border-radius:14px; border:1px solid var(--border);"><i
                            class="fa-solid fa-users"></i> Patient Registry</a>
                    <a href="billing.php" class="btn"
                        style="background:#f8fafc; color:var(--text-main); justify-content:center; padding:15px; border-radius:14px; border:1px solid var(--border);"><i
                            class="fa-solid fa-file-invoice-dollar"></i> Billing Console</a>
                <?php endif; ?>

                <?php if ($role == 'Doctor'): ?>
                    <a href="doctor_portal.php" class="btn btn-primary"
                        style="justify-content:center; padding:15px; border-radius:14px; font-weight:700;"><i
                            class="fa-solid fa-briefcase-medical"></i> Open Medical Hub</a>
                    <a href="appointments.php" class="btn"
                        style="background:#f8fafc; color:var(--text-main); justify-content:center; padding:15px; border-radius:14px; border:1px solid var(--border);"><i
                            class="fa-solid fa-calendar-alt"></i> My Schedule</a>
                <?php endif; ?>

                <?php if ($role == 'Patient'): ?>
                    <a href="appointments.php" class="btn btn-primary"
                        style="justify-content:center; padding:15px; border-radius:14px; font-weight:700;"><i
                            class="fa-solid fa-calendar-plus"></i> Request Consultation</a>
                    <a href="billing.php" class="btn"
                        style="background:#f8fafc; color:var(--text-main); justify-content:center; padding:15px; border-radius:14px; border:1px solid var(--border);"><i
                            class="fa-solid fa-credit-card"></i> Payment Methods</a>
                <?php endif; ?>

                <a href="logout.php" class="btn"
                    style="background:#fff1f2; color:#e11d48; justify-content:center; padding:15px; border-radius:14px; border:1px solid #ffe4e6; margin-top:15px;"><i
                        class="fa-solid fa-power-off"></i> Formal Sign Out</a>
            </div>

            <div
                style="margin-top:30px; padding:20px; background:linear-gradient(135deg, #1e293b, #0f172a); border-radius:18px; color:white;">
                <h4
                    style="margin:0 0 10px; font-size:0.8rem; text-transform:uppercase; opacity:0.6; letter-spacing:0.1em;">
                    System Info</h4>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:8px;">
                    <span>Security Layer</span>
                    <span style="color:#10b981; font-weight:700;">AES-256</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:0.85rem;">
                    <span>Server Node</span>
                    <span>HDMS-CORE-01</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($role == 'SuperAdmin'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx1 = document.getElementById('patientChart');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode($arrLabels) ?>,
                datasets: [{
                    label: 'Registry Flow',
                    data: <?= json_encode($arrData) ?>,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#2563eb'
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { display: false }, x: { grid: { display: false } } } }
        });

        const ctx2 = document.getElementById('revenueChart');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode($revLabels) ?>,
                datasets: [{
                    label: 'Revenue USD ($)',
                    data: <?= json_encode($revData) ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 8,
                    hoverBackgroundColor: '#059669'
                }]
            },
            options: { plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } } } }
        });
    </script>
<?php endif; ?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>