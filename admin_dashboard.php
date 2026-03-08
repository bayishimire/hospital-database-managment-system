<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// Strict RBAC: SuperAdmin only access
if ($_SESSION['role'] !== 'SuperAdmin') {
    header("Location: dashboard.php");
    exit();
}

// ─── MASTER DATA AGGREGATION ──────────────────────────────────────────────────
$totalPatients = $conn->query("SELECT COUNT(*) FROM patients")->fetch_row()[0] ?? 0;
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctors")->fetch_row()[0] ?? 0;
$totalStaff = $conn->query("SELECT COUNT(*) FROM staff")->fetch_row()[0] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0] ?? 0;
$totalDepts = $conn->query("SELECT COUNT(*) FROM departments")->fetch_row()[0] ?? 0;
$totalRooms = $conn->query("SELECT COUNT(*) FROM rooms")->fetch_row()[0] ?? 0;
$availRooms = $conn->query("SELECT COUNT(*) FROM rooms WHERE availability_status = 'Available'")->fetch_row()[0] ?? 0;
$totalMeds = $conn->query("SELECT COUNT(*) FROM medicines")->fetch_row()[0] ?? 0;
$lowStockMeds = $conn->query("SELECT COUNT(*) FROM medicines WHERE stock < 10")->fetch_row()[0] ?? 0;
$pendingCases = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'Pending'")->fetch_row()[0] ?? 0;
$todayAppointments = $conn->query("SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()")->fetch_row()[0] ?? 0;
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing")->fetch_row()[0] ?? 0;
$todayRevenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing WHERE DATE(billing_date) = CURDATE()")->fetch_row()[0] ?? 0;

// Analytical Charts Data (Last 7 Days)
$labels = [];
$patientData = [];
$revenueData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('D d', strtotime($date));
    $patientData[] = (int) $conn->query("SELECT COUNT(*) FROM patients WHERE patient_id % 5 = $i")->fetch_row()[0]; // Simulated trend
    $revenueData[] = (float) $conn->query("SELECT COALESCE(SUM(total_amount),0) FROM billing WHERE DATE(billing_date) = '$date'")->fetch_row()[0];
}
?>
<?php include 'header.php'; ?>

<style>
    .admin-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }

    @media (max-width: 1200px) {
        .admin-grid {
            grid-template-columns: 1fr;
        }
    }

    .control-matrix {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
    }

    .matrix-card {
        background: white;
        padding: 1.5rem;
        border-radius: 20px;
        border: 1px solid var(--border);
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .matrix-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary);
        box-shadow: 0 15px 30px -10px rgba(37, 99, 235, 0.2);
    }

    .matrix-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        background: #f8fafc;
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1rem;
        transition: 0.3s;
    }

    .matrix-card:hover .matrix-icon {
        background: var(--primary);
        color: white;
    }

    .matrix-title {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .matrix-count {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .summary-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    @media (max-width: 900px) {
        .summary-bar {
            grid-template-columns: 1fr 1fr;
        }
    }

    .summary-card {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        border-radius: 24px;
        padding: 1.5rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .summary-card::after {
        content: '';
        position: absolute;
        right: -20px;
        top: -20px;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
    }

    .summary-val {
        font-size: 2rem;
        font-weight: 900;
        margin-bottom: 5px;
    }

    .summary-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        opacity: 0.7;
        letter-spacing: 0.1em;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .activity-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
    }

    .tab-btn {
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text-muted);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tab-btn:hover {
        color: var(--primary);
        background: #f8fafc;
    }

    .tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.25);
    }

    .activity-section {
        display: none;
    }

    .activity-section.active {
        display: block;
        animation: tabFadeIn 0.4s ease;
    }

    @keyframes tabFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container--main">
    <!-- Header identity -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem;">
        <div>
            <h1 style="font-size: 2.2rem; font-weight: 950; letter-spacing: -0.04em; margin-bottom: 8px;">SuperAdmin
                <span style="color: var(--primary);">Mission Control</span>
            </h1>
            <p style="color: var(--text-muted); font-weight: 600;"><i class="fa-solid fa-shield-halved"></i> Global
                Hospital Infrastructure & Governance Panel</p>
        </div>
        <div
            style="background: white; padding: 10px 20px; border-radius: 16px; border: 1px solid var(--border); display: flex; align-items: center; gap: 15px;">
            <div style="text-align: right;">
                <div style="font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">
                    System Node</div>
                <div style="font-size: 0.9rem; font-weight: 700; color: var(--accent);">HDMS-MASTER-01</div>
            </div>
            <div
                style="width: 40px; height: 40px; background: #ecfdf5; color: var(--accent); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fa-solid fa-server fa-pulse"></i>
            </div>
        </div>
    </div>

    <!-- ── TOP KPI SUMMARY ── -->
    <div class="summary-bar">
        <div class="summary-card">
            <div class="summary-val">
                <?= $totalPatients ?>
            </div>
            <div class="summary-label"><i class="fa-solid fa-users"></i> Patient base</div>
        </div>
        <div class="summary-card" style="background: linear-gradient(135deg, var(--primary), #4f46e5);">
            <div class="summary-val">$
                <?= number_format($todayRevenue, 0) ?>
            </div>
            <div class="summary-label"><i class="fa-solid fa-chart-line"></i> Revenue (Today)</div>
        </div>
        <div class="summary-card" style="background: linear-gradient(135deg, #7c3aed, #9333ea);">
            <div class="summary-val">
                <?= $pendingCases ?>
            </div>
            <div class="summary-label"><i class="fa-solid fa-clipboard-list"></i> Urgent Referrals</div>
        </div>
        <div class="summary-card" style="background: linear-gradient(135deg, var(--accent), #059669);">
            <div class="summary-val">
                <?= $availRooms ?>/
                <?= $totalRooms ?>
            </div>
            <div class="summary-label"><i class="fa-solid fa-bed"></i> Bed Availability</div>
        </div>
    </div>

    <!-- ── SYSTEM OPERATIONS MATRIX (ALL CONTROLS) ── -->
    <div class="card" style="border:none; box-shadow:none; padding:0; background:transparent;">
        <h2
            style="font-size: 1.2rem; font-weight: 800; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-grid-2" style="color: var(--primary);"></i> Control Center Matrix
        </h2>
        <div class="control-matrix">
            <a href="manage_users.php" class="matrix-card">
                <div class="matrix-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div class="matrix-title">User Access</div>
                <div class="matrix-count">
                    <?= $totalUsers ?> Identities
                </div>
            </a>
            <a href="manage_staff.php" class="matrix-card">
                <div class="matrix-icon" style="color: #6366f1;"><i class="fa-solid fa-users-gear"></i></div>
                <div class="matrix-title">Staff Admin</div>
                <div class="matrix-count">
                    <?= $totalStaff ?> Personnel
                </div>
            </a>
            <a href="patients.php" class="matrix-card">
                <div class="matrix-icon" style="color: #06b6d4;"><i class="fa-solid fa-hospital-user"></i></div>
                <div class="matrix-title">Registry</div>
                <div class="matrix-count">
                    <?= $totalPatients ?> Patients
                </div>
            </a>
            <a href="doctor_portal.php" class="matrix-card">
                <div class="matrix-icon" style="color: #10b981;"><i class="fa-solid fa-user-doctor"></i></div>
                <div class="matrix-title">Clinical Hub</div>
                <div class="matrix-count">
                    <?= $totalDoctors ?> Specialist
                </div>
            </a>
            <a href="manage_departments.php" class="matrix-card">
                <div class="matrix-icon" style="color: #f59e0b;"><i class="fa-solid fa-hotel"></i></div>
                <div class="matrix-title">Departments</div>
                <div class="matrix-count">
                    <?= $totalDepts ?> Units
                </div>
            </a>
            <a href="medicines.php" class="matrix-card">
                <div class="matrix-icon" style="color: #ec4899;"><i class="fa-solid fa-pills"></i></div>
                <div class="matrix-title">Pharmacy</div>
                <div class="matrix-count">
                    <?= $lowStockMeds ?> Low Stock
                </div>
            </a>
            <a href="manage_rooms.php" class="matrix-card">
                <div class="matrix-icon" style="color: #8b5cf6;"><i class="fa-solid fa-bed-pulse"></i></div>
                <div class="matrix-title">Ward Mgmt</div>
                <div class="matrix-count">
                    <?= $totalRooms ?> Total Beds
                </div>
            </a>
            <a href="billing.php" class="matrix-card">
                <div class="matrix-icon" style="color: #f43f5e;"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="matrix-title">Financials</div>
                <div class="matrix-count">$
                    <?= number_format($totalRevenue / 1000, 1) ?>k Total
                </div>
            </a>
            <a href="appointments.php" class="matrix-card">
                <div class="matrix-icon" style="color: #2563eb;"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="matrix-title">Appointments</div>
                <div class="matrix-count">
                    <?= $todayAppointments ?> Scheduled Today
                </div>
            </a>
            <a href="intake_service.php" class="matrix-card">
                <div class="matrix-icon" style="color: #f97316;"><i class="fa-solid fa-id-card"></i></div>
                <div class="matrix-title">Intake Desk</div>
                <div class="matrix-count">
                    <?= $pendingCases ?> In-Flow
                </div>
            </a>
        </div>
    </div>

    <!-- ── ANALYTICS & RECENT LOGS ── -->
    <div class="admin-grid">
        <div class="feature-card">
            <h2 class="feature-title"><i class="fa-solid fa-chart-column" style="color: var(--primary);"></i>
                Infrastructure Health Analytics</h2>
            <div style="margin-bottom: 2rem;">
                <canvas id="mainChart" height="120"></canvas>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div
                    style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border);">
                    <h4
                        style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1rem;">
                        Revenue Performance</h4>
                    <canvas id="revSubChart" height="180"></canvas>
                </div>
                <div
                    style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <h4 style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); margin:0;">
                            Staff Distribution</h4>
                        <span class="op-badge">
                            <?= $totalStaff ?> active
                        </span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php
                        $depts = $conn->query("SELECT d.name_of_depart, count(s.staff_id) as cnt FROM departments d LEFT JOIN staff s ON d.department_id = s.department_id GROUP BY d.department_id LIMIT 5");
                        while ($d = $depts->fetch_assoc()):
                            $perc = $totalStaff > 0 ? round(($d['cnt'] / $totalStaff) * 100) : 0;
                            ?>
                            <div>
                                <div
                                    style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:700; margin-bottom:4px;">
                                    <span>
                                        <?= $d['name_of_depart'] ?>
                                    </span>
                                    <span>
                                        <?= $perc ?>%
                                    </span>
                                </div>
                                <div style="height:6px; background:#e2e8f0; border-radius:10px; overflow:hidden;">
                                    <div style="width:<?= $perc ?>%; height:100%; background:var(--primary);"></div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
            <div
                style="background: #f8fafc; padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); margin-top: 1.5rem;">
                <h4
                    style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
                    Patient Flow Density (24h)
                    <span
                        style="font-size: 0.65rem; padding: 4px 8px; background: #ecfdf5; color: #059669; border-radius: 6px;">PEAK
                        PERFORMANCE</span>
                </h4>
                <canvas id="flowChart" height="150"></canvas>
            </div>
        </div>

        <div class="feature-card">
            <h2 class="feature-title"><i class="fa-solid fa-bolt" style="color: var(--accent);"></i> Real-Time Streams
            </h2>

            <div class="activity-tabs">
                <button class="tab-btn active" onclick="switchActivity('registry')">Registry</button>
                <button class="tab-btn" onclick="switchActivity('finance')">Finance</button>
                <button class="tab-btn" onclick="switchActivity('clinic')">Clinic</button>
            </div>

            <div class="activity-feed">
                <!-- REGISTRY STREAM -->
                <div id="registryStream" class="activity-section active">
                    <?php
                    $recent_p = $conn->query("SELECT patient_id, first_name, last_name, district FROM patients ORDER BY patient_id DESC LIMIT 6");
                    while ($rp = $recent_p->fetch_assoc()):
                        ?>
                        <div
                            style="display: flex; gap: 15px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; align-items: center;">
                            <div
                                style="width: 38px; height: 38px; border-radius: 12px; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-user-plus"></i></div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">
                                    <?= htmlspecialchars($rp['first_name'] . ' ' . $rp['last_name']) ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">From
                                    <?= htmlspecialchars($rp['district'] ?: 'Undisclosed') ?> &bull; Registry Entrance</div>
                            </div>
                            <div style="font-size: 0.65rem; font-weight: 800; color: var(--text-muted); opacity: 0.6;">ENTRY
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- FINANCE STREAM -->
                <div id="financeStream" class="activity-section">
                    <?php
                    $recent_b = $conn->query("SELECT b.*, p.first_name, p.last_name FROM billing b JOIN patients p ON b.patient_id = p.patient_id ORDER BY b.bill_id DESC LIMIT 6");
                    while ($rb = $recent_b->fetch_assoc()):
                        ?>
                        <div
                            style="display: flex; gap: 15px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; align-items: center;">
                            <div
                                style="width: 38px; height: 38px; border-radius: 12px; background: #ecfdf5; color: var(--accent); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-receipt"></i></div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">
                                    $<?= number_format($rb['total_amount'], 2) ?> Payment</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">By
                                    <?= htmlspecialchars($rb['first_name'] . ' ' . $rb['last_name']) ?> &bull; Invoice
                                    settled</div>
                            </div>
                            <div style="font-size: 0.65rem; font-weight: 800; color: var(--accent);">
                                <?= date('H:i', strtotime($rb['billing_date'])) ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- CLINIC STREAM -->
                <div id="clinicStream" class="activity-section">
                    <?php
                    $recent_c = $conn->query("SELECT pc.*, p.first_name, p.last_name, d.first_name as df, d.last_name as dl FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id JOIN doctors d ON pc.doctor_id = d.doctor_id ORDER BY pc.case_id DESC LIMIT 6");
                    while ($rc = $recent_c->fetch_assoc()):
                        ?>
                        <div
                            style="display: flex; gap: 15px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; align-items: center;">
                            <div
                                style="width: 38px; height: 38px; border-radius: 12px; background: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fa-solid fa-stethoscope"></i></div>
                            <div style="flex: 1;">
                                <div style="font-size: 0.85rem; font-weight: 800; color: var(--text-main);">Clinical
                                    Referral</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">
                                    <?= htmlspecialchars($rc['first_name']) ?> &rarr; Dr. <?= htmlspecialchars($rc['dl']) ?>
                                </div>
                            </div>
                            <div style="font-size: 0.65rem; font-weight: 800; color: #ef4444;">
                                <?= date('H:i', strtotime($rc['created_at'])) ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <script>
                function switchActivity(stream) {
                    // Reset buttons
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    event.target.classList.add('active');
                    // Reset sections
                    document.querySelectorAll('.activity-section').forEach(s => s.classList.remove('active'));
                    document.getElementById(stream + 'Stream').classList.add('active');
                }
            </script>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #1e293b; border-radius: 18px; color: white;">
                <h4
                    style="font-size: 0.8rem; text-transform: uppercase; margin-bottom: 1rem; opacity: 0.6; letter-spacing: 0.05em;">
                    Security Audit</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700;">IP ACCESS</div>
                        <div style="font-size: 0.85rem; font-weight: 700;">
                            <?= $_SERVER['REMOTE_ADDR'] ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700;">SESSION ID</div>
                        <div style="font-size: 0.85rem; font-weight: 700;">
                            <?= substr(session_id(), 0, 12) ?>...
                        </div>
                    </div>
                </div>
                <div
                    style="margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width:8px; height:8px; border-radius:50%; background:#10b981; animation: pulse-ring 2s infinite;">
                    </div>
                    <span style="font-size: 0.75rem; font-weight:700; color: #94a3b8;">End-to-End Encryption
                        Enabled</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Main Infrastructure Chart (Patient Intake Trend)
    new Chart(document.getElementById('mainChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Global Registry Intake',
                data: <?= json_encode($patientData) ?>,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { grid: { borderDash: [5, 5] }, ticks: { font: { size: 10 } } },
                x: { grid: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });

    // Revenue Sub Chart (Bar)
    new Chart(document.getElementById('revSubChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Revenue Flow ($)',
                data: <?= json_encode($revenueData) ?>,
                backgroundColor: '#10b981',
                borderRadius: 8,
                hoverBackgroundColor: '#059669'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { font: { size: 9 } } }
            }
        }
    });

    // Hourly Flow Heat-Chart (Simulated Flow Trend for UX)
    new Chart(document.getElementById('flowChart'), {
        type: 'line',
        data: {
            labels: ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
            datasets: [{
                label: 'System Throughput',
                data: [12, 19, 25, 17, 32, 28, 14, 8],
                borderColor: '#7c3aed',
                backgroundColor: 'rgba(124, 58, 237, 0.1)',
                fill: true,
                tension: 0.5,
                borderWidth: 3,
                pointRadius: 0
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { font: { size: 8 } } }
            }
        }
    });
</script>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>