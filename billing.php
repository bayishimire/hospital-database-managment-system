<?php require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/access_control.php';
restrict_access(['Staff', 'Reception', 'Admin']);
?>
<?php
// INITIALIZE ALL SHARED VARIABLES
$selectedPID = $_GET['patient_id'] ?? null;
$patientData = null;
$clinicData = null;
$userRole = $_SESSION['role'] ?? 'Staff';

// 1. Check if we are viewing a specific Payment Slip (Receipt)
$slipID = $_GET['print_slip'] ?? null;
if ($slipID) {
    include 'header.php';
    echo '<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>';
    echo '<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">';

    $res = $conn->query("SELECT b.*, p.first_name, p.last_name, p.insurance, p.address 
                        FROM billing b JOIN patients p ON b.patient_id = p.patient_id 
                        WHERE b.bill_id = " . (int) $slipID);
    $bill = $res->fetch_assoc();

    if ($bill) {
        // RESTRICTION: Only Admin can download unpaid slips.
        if ($bill['payment_status'] !== 'Paid' && $userRole !== 'Admin') {
            ?>
            <div class="card"
                style="max-width: 600px; margin: 4rem auto; text-align: center; padding: 4rem; border-radius: 30px; border: 2px dashed #f87171; background: #fff; animation: slideUp 0.5s ease-out;">
                <div
                    style="background: #fef2f2; width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; box-shadow: 0 10px 20px rgba(239, 68, 68, 0.1);">
                    <i class="fa-solid fa-hand-holding-dollar" style="font-size: 3rem; color: #ef4444;"></i>
                </div>
                <h2 style="color: #991b1b; font-weight: 950; margin-bottom: 1rem; letter-spacing: -1px;">Payment Required</h2>
                <p style="color: #64748b; font-size: 1.1rem; line-height: 1.6; font-weight: 500;">
                    The settlement for <strong><?= htmlspecialchars($bill['first_name']) ?></strong> is currently <span
                        style="color:#ef4444; font-weight:800;">PENDING</span>.<br>
                    Official slips can only be downloaded after the transaction is finalized.
                </p>
                <div style="margin-top: 2.5rem; display: flex; gap: 1rem; justify-content: center;">
                    <a href="billing.php" class="btn btn-primary"
                        style="padding: 14px 35px; border-radius: 14px; font-weight: 800; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);">Return
                        to Billing</a>
                    <?php if ($userRole === 'Admin'): ?><button onclick="window.print()" class="btn"
                            style="background: #1e293b; color: white; padding: 14px 25px; border-radius:14px; font-weight:700;">Admin
                            Override</button><?php endif; ?>
                </div>
            </div>
            <style>
                @keyframes slideUp {
                    from {
                        transform: translateY(20px);
                        opacity: 0;
                    }

                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
            </style>
            <?php
            include 'footer.php';
            exit();
        }
        ?>
        <div class="card" id="printableSlip"
            style="max-width: 700px; margin: 2rem auto; border: 2px solid #1e293b; padding: 3rem; background: #fff; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15); position: relative; border-radius: 0;">
            <div style="text-align: center; border-bottom: 3px double #1e293b; padding-bottom: 2rem; margin-bottom: 2rem;">
                <h1
                    style="margin: 0; color: #1e293b; font-size: 2.2rem; font-weight: 900; text-transform: uppercase; letter-spacing: 2px;">
                    Hospital Payment Slip</h1>
                <p style="margin: 8px 0; color: #475569; font-weight: 700; font-size: 0.9rem; letter-spacing: 1px;">OFFICIAL
                    RECEIPT • REVENUE UNIT</p>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 3rem; line-height: 1.8;">
                <div>
                    <strong style="color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Patient
                        Information</strong><br>
                    <span
                        style="font-size: 1.3rem; font-weight: 900; color: #0f172a;"><?= htmlspecialchars($bill['first_name'] . " " . $bill['last_name']) ?></span><br>
                    <span style="color: #475569; font-weight: 500;"><?= htmlspecialchars($bill['address']) ?></span>
                </div>
                <div style="text-align: right;">
                    <strong>SLIP ID:</strong> <span
                        style="color:#2563eb; font-weight:800;">#<?= str_pad($bill['bill_id'], 6, '0', STR_PAD_LEFT) ?></span><br>
                    <strong>DATE:</strong> <span
                        style="font-weight:700;"><?= date('M d, Y', strtotime($bill['billing_date'])) ?></span><br>
                    <strong>TIME:</strong> <?= date('H:i A') ?>
                </div>
            </div>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 3rem;">
                <thead>
                    <tr style="background: #f1f5f9; border-top: 2px solid #1e293b; border-bottom: 2px solid #1e293b;">
                        <th
                            style="text-align: left; padding: 15px; color: #1e293b; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">
                            Description of Service</th>
                        <th
                            style="text-align: right; padding: 15px; color: #1e293b; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;">
                            Amount ($)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 20px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 600;">General Hospital
                            Consultation & Diagnostic Review</td>
                        <td
                            style="text-align: right; padding: 20px 15px; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 1.1rem;">
                            $<?= number_format($bill['total_amount'] * 0.7, 2) ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 15px; border-bottom: 6px double #e2e8f0; font-weight: 600;">Pharmacy Billing:
                            Unified Medication & Supplies</td>
                        <td
                            style="text-align: right; padding: 20px 15px; border-bottom: 6px double #e2e8f0; font-weight: 800; font-size: 1.1rem;">
                            $<?= number_format($bill['total_amount'] * 0.3, 2) ?></td>
                    </tr>
                    <tr style="background: #0f172a; color: white;">
                        <td style="padding: 20px; font-weight: 900; text-transform: uppercase;">Final Grand Settlement</td>
                        <td style="text-align: right; padding: 20px; font-weight: 900; font-size: 1.6rem;">
                            $<?= number_format($bill['total_amount'], 2) ?></td>
                    </tr>
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 4rem;">
                <div style="border: 2px solid #e2e8f0; padding: 15px; border-radius: 12px; background: #f8fafc;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                        <i class="fa-solid fa-circle-check" style="color:#10b981;"></i>
                        <span style="font-weight:900; color:#1e293b;">PAYMENT VERIFIED</span>
                    </div>
                    <div style="font-size:0.8rem; color:#64748b;">Transaction Protocol: TLS-HS-<?= $bill['bill_id'] ?></div>
                </div>
                <div style="text-align: center; width: 220px;">
                    <div
                        style="border-bottom: 2px solid #1e293b; margin-bottom: 8px; font-family: 'Dancing Script', cursive; font-size: 1.8rem;">
                        Digital System Signature</div>
                    <small
                        style="color: #64748b; font-weight: 800; text-transform: uppercase; font-size: 0.65rem; letter-spacing: 1px;">Revenue
                        Officer</small>
                </div>
            </div>

            <div id="uiControls"
                style="margin-top: 4rem; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                <button onclick="window.print()" class="btn btn-primary"
                    style="padding:15px 40px; border-radius:14px; font-weight:800; font-size:1.1rem;"><i
                        class="fa-solid fa-print"></i> Print Official Receipt</button>
                <button onclick="window.location='billing.php'" class="btn"
                    style="background:#64748b; color:white; padding:15px 30px; border-radius:14px; margin-top:10px;">Close
                    Desk</button>
            </div>
        </div>
        <?php
    }
    include 'footer.php';
    exit();
}

// 2. Fetch Data for Settlement Desk if patient is selected
if ($selectedPID) {
    $pRes = $conn->query("SELECT * FROM patients WHERE patient_id = " . (int) $selectedPID);
    $patientData = $pRes->fetch_assoc();
    $cRes = $conn->query("SELECT mr.*, pc.chief_complaint, pc.vitals as fish_vitals 
                         FROM medicalrecords mr JOIN patient_cases pc ON mr.patient_id = pc.patient_id 
                         WHERE mr.patient_id = " . (int) $selectedPID . " ORDER BY mr.record_date DESC LIMIT 1");
    $clinicData = $cRes->fetch_assoc();

    // AUTO-CALCULATE MEDICINE TOTAL
    $medTotal = 0;
    $prescriptions = [];
    if ($clinicData) {
        $rid = $clinicData['record_id'];
        $prescRes = $conn->query("SELECT p.*, m.name, m.price FROM prescriptions p JOIN medicines m ON p.medicine_id = m.medicine_id WHERE p.record_id = $rid");
        while ($p = $prescRes->fetch_assoc()) {
            $prescriptions[] = $p;
            $medTotal += ($p['price'] * $p['quantity']);
        }
    }
}

// 3. Handle Settlement Processing
if (isset($_POST['add_bill'])) {
    $pid = (int) $_POST['patient_id'];
    $amt = (float) $_POST['amount'];
    $sts = $conn->real_escape_string($_POST['status']);
    $date = date('Y-m-d H:i:s');
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO billing (patient_id, total_amount, payment_status, billing_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $pid, $amt, $sts, $date);
        $stmt->execute();
        $billID = $conn->insert_id;
        $conn->query("UPDATE patient_cases SET status = 'Completed' WHERE patient_id = $pid AND status = 'BillingPending'");
        $conn->commit();
        echo "<script>window.location='billing.php?print_slip=$billID';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
    }
}
?>

<?php include 'header.php'; ?>

<style>
    .settlement-desk {
        display: grid;
        grid-template-columns: 420px 1fr;
        gap: 40px;
        padding: 10px;
    }

    .clinical-ledger {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        padding: 35px;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .ledger-section {
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 1px solid #f1f5f9;
    }

    .ledger-title {
        font-size: 0.75rem;
        font-weight: 950;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .ledger-content {
        font-size: 1rem;
        color: #1e293b;
        font-weight: 600;
        line-height: 1.7;
    }

    .calc-card {
        background: white;
        border: 2px solid #2563eb;
        border-radius: 35px;
        padding: 45px;
        box-shadow: 0 40px 60px -15px rgba(37, 99, 235, 0.15);
        position: relative;
        overflow: hidden;
    }

    .calc-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 150px;
        height: 150px;
        background: #2563eb;
        opacity: 0.03;
        border-radius: 0 0 0 100%;
        pointer-events: none;
    }

    .fee-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .fee-field {
        width: 100%;
        padding: 18px;
        border-radius: 20px;
        border: 2px solid #e2e8f0;
        font-weight: 900;
        font-size: 1.4rem;
        color: #0f172a;
        transition: 0.3s;
        background: #f8fafc;
    }

    .fee-field:focus {
        border-color: #2563eb;
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .total-display {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 35px;
        border-radius: 28px;
        text-align: center;
        margin: 30px 0;
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.2);
    }

    .total-display span {
        font-size: 0.9rem;
        font-weight: 700;
        opacity: 0.6;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .total-display strong {
        font-size: 3.5rem;
        font-weight: 950;
        display: block;
        letter-spacing: -2px;
    }

    .console-box {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 30px;
        padding: 0;
        overflow: hidden;
        transition: 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        text-decoration: none !important;
        color: inherit !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    }

    .console-box:hover {
        transform: translateY(-8px) scale(1.02);
        border-color: #2563eb;
        box-shadow: 0 25px 30px -10px rgba(37, 99, 235, 0.15);
    }

    .console-header {
        padding: 1.8rem;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .console-body {
        padding: 1.8rem;
        flex-grow: 1;
    }

    .console-footer {
        padding: 1.2rem;
        background: #2563eb;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        font-weight: 900;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    .badge-paid {
        background: #dcfce7;
        color: #15803d;
        font-weight: 800;
        border-radius: 50px;
        padding: 6px 15px;
    }

    .badge-debt {
        background: #fee2e2;
        color: #991b1b;
        font-weight: 800;
        border-radius: 50px;
        padding: 6px 15px;
    }
</style>

<div class="card" style="border:none; background:transparent; padding:0;">
    <?php if ($selectedPID && $patientData): ?>
        <!-- SETTLEMENT DESK VIEW -->
        <div class="settlement-desk">
            <div class="clinical-ledger">
                <div style="text-align:center; margin-bottom:35px;">
                    <div
                        style="width:70px; height:70px; background:rgba(37,99,235,0.05); border-radius:24px; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                        <i class="fa-solid fa-file-invoice" style="font-size:2rem; color:#2563eb;"></i>
                    </div>
                    <h3 style="margin:0; font-size:1.3rem; font-weight:950;">Settlement Audit</h3>
                </div>

                <div class="ledger-section">
                    <div class="ledger-title" style="color:#10b981;"><i class="fa-solid fa-user-shield"></i> Registration
                        Profile</div>
                    <div class="ledger-content">
                        <div style="font-size:1.2rem; font-weight:900; color:#0f172a; margin-bottom:5px;">
                            <?= htmlspecialchars($patientData['first_name'] . ' ' . $patientData['last_name']) ?>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span
                                style="font-weight:700; color:#2563eb; font-size:0.9rem; background:#eff6ff; padding:3px 12px; border-radius:50px;"><?= htmlspecialchars($patientData['insurance']) ?></span>
                            <span style="color:#94a3b8; font-size:0.8rem;">ID:
                                #<?= str_pad($selectedPID, 4, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>
                </div>

                <div class="ledger-section">
                    <div class="ledger-title" style="color:#7c3aed;"><i class="fa-solid fa-microscope"></i> Diagnostic
                        Findings</div>
                    <div class="ledger-content">
                        <div
                            style="background: #f8fafc; padding: 18px; border-radius: 18px; border-left: 6px solid #7c3aed; margin-bottom: 15px;">
                            <label
                                style="font-size:0.65rem; font-weight:950; color:#7c3aed; text-transform:uppercase; margin-bottom:5px; display:block;">Clinical
                                Diagnosis</label>
                            <div style="font-weight:800;">
                                <?= htmlspecialchars($clinicData['diagnosis'] ?? 'Pending Review...') ?>
                            </div>
                        </div>
                        <div
                            style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); padding:22px; border-radius:24px; border:1px solid #ddd6fe;">
                            <label
                                style="font-size:0.65rem; font-weight:950; color:#7c3aed; text-transform:uppercase; margin-bottom:10px; display:block;"><i
                                    class="fa-solid fa-capsules"></i> Pharmacy Dispatch Breakdown</label>
                            <?php if (empty($prescriptions)): ?>
                                <div style="font-size:1rem; font-weight:700; color:#4c1d95; line-height:1.6;">
                                    <?= nl2br(htmlspecialchars($clinicData['treatment'] ?? 'No medication prescribed.')) ?>
                                </div>
                            <?php else: ?>
                                <div style="max-height: 200px; overflow-y:auto;">
                                    <?php foreach ($prescriptions as $p): ?>
                                        <div
                                            style="display:flex; justify-content:space-between; margin-bottom:8px; border-bottom:1px dashed rgba(124,58,237,0.2); padding-bottom:5px;">
                                            <div>
                                                <strong style="color:#4c1d95;"><?= htmlspecialchars($p['name']) ?></strong>
                                                <div style="font-size:0.75rem; color:#7c3aed;"><?= htmlspecialchars($p['dosage']) ?>
                                                </div>
                                            </div>
                                            <div style="text-align:right;">
                                                <div style="font-size:0.85rem; font-weight:700;">x<?= $p['quantity'] ?></div>
                                                <div style="font-size:0.75rem; opacity:0.8;">
                                                    $<?= number_format($p['price'] * $p['quantity'], 2) ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="calc-card">
                <form method="POST">
                    <input type="hidden" name="patient_id" value="<?= (int) $selectedPID ?>">
                    <h2 style="margin:0 0 30px; font-weight:950; font-size:1.8rem; letter-spacing:-1px;"><i
                            class="fa-solid fa-calculator" style="color:#2563eb;"></i> Billing Console</h2>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:25px; margin-bottom:25px;">
                        <div><label class="fee-label">Consultation ($)</label><input type="number" step="0.01" id="fee_cons"
                                class="fee-field calc-val" value="50.00" required></div>
                        <div><label class="fee-label">Laboratory ($)</label><input type="number" step="0.01" id="fee_lab"
                                class="fee-field calc-val"
                                value="<?= ($clinicData && $clinicData['test_results']) ? '25.00' : '0.00' ?>"></div>
                    </div>

                    <div style="margin-bottom:30px;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <label class="fee-label">Pharmacy & Medications ($)</label>
                            <?php if ($medTotal > 0): ?>
                                <span
                                    style="font-size:0.65rem; background:#ecfdf5; color:#059669; padding:2px 8px; border-radius:4px; font-weight:800;"><i
                                        class="fa-solid fa-wand-magic-sparkles"></i> AUTO-CALCULATED</span>
                            <?php endif; ?>
                        </div>
                        <input type="number" step="0.01" name="med_fee" id="fee_med" placeholder="0.00"
                            class="fee-field calc-val" value="<?= number_format($medTotal, 2, '.', '') ?>" required>
                    </div>

                    <div class="total-display">
                        <span>Grand Settlement Amount</span>
                        <strong>$<span id="total_amt_display">50.00</span></strong>
                        <input type="hidden" name="amount" id="final_amount" value="50.00">
                    </div>

                    <div style="margin-bottom:30px;">
                        <label class="fee-label">Settlement Mode</label>
                        <select name="status" class="fee-field" style="background:#f8fafc; cursor:pointer;">
                            <option value="Paid">✓ IMMEDIATE SETTLEMENT</option>
                            <option value="Pending">⚠ ADD TO DEBT / POSTPONE</option>
                        </select>
                    </div>

                    <div style="display:flex; gap:20px;">
                        <a href="billing.php" class="btn"
                            style="flex:1; background:#f1f5f9; color:#475569; padding:20px; border-radius:20px; text-align:center; font-weight:800;">DISCARD</a>
                        <button type="submit" name="add_bill" class="btn btn-primary"
                            style="flex:2.5; padding:20px; border-radius:20px; font-weight:900; font-size:1.1rem; box-shadow: 0 15px 30px rgba(37,99,235,0.25);">
                            <i class="fa-solid fa-shield-check"></i> FINALIZE & PRINT SLIP
                        </button>
                    </div>
                </form>
                <script>
                    const inputs = document.querySelectorAll('.calc-val');
                    const display = document.getElementById('total_amt_display');
                    const hidden = document.getElementById('final_amount');
                    function runCalc() {
                        let t = 0;
                        inputs.forEach(i => t += parseFloat(i.value || 0));
                        display.textContent = t.toFixed(2);
                        hidden.value = t.toFixed(2);
                    }
                    inputs.forEach(i => i.addEventListener('input', runCalc));
                    window.onload = runCalc;
                </script>
            </div>
        </div>
    <?php else: ?>
        <!-- MAIN DASHBOARD VIEW -->
        <div style="display: grid; grid-template-columns: 420px 1fr; gap: 4rem;">
            <section>
                <h3 class="card-title" style="color: #0f172a; margin-bottom: 2.5rem; font-weight:950; font-size:1.5rem;"><i
                        class="fa-solid fa-tower-broadcast" style="color:#ef4444;"></i> Revenue Pipeline</h3>
                <div style="display: flex; flex-direction: column; gap: 1.8rem;">
                    <?php
                    $reps = $conn->query("SELECT pc.*, p.first_name, p.last_name, p.insurance, mr.treatment, d.first_name as dfname, d.last_name as dlname FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id LEFT JOIN medicalrecords mr ON p.patient_id = mr.patient_id AND mr.record_date >= pc.created_at LEFT JOIN doctors d ON mr.doctor_id = d.doctor_id WHERE pc.status = 'BillingPending' ORDER BY pc.created_at ASC");
                    if ($reps && $reps->num_rows > 0):
                        while ($r = $reps->fetch_assoc()): ?>
                            <a href="?patient_id=<?= (int) $r['patient_id'] ?>" class="console-box">
                                <div class="console-header">
                                    <strong
                                        style="font-size: 1.1rem; font-weight:900;"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></strong>
                                    <span
                                        style="font-size: 0.65rem; background: #dbeafe; padding: 5px 12px; border-radius: 50px; font-weight: 800; color: #1e40af; border: 1px solid #bfdbfe;">DR.
                                        <?= strtoupper($r['dfname'] ?? 'SYS') ?></span>
                                </div>
                                <div class="console-body">
                                    <div style="font-size:0.8rem; color:#64748b; margin-bottom:12px; font-weight:700;"><i
                                            class="fa-solid fa-shield-heart"></i> <?= htmlspecialchars($r['insurance']) ?></div>
                                    <div
                                        style="font-size: 0.9rem; color: #475569; background: #f8fafc; padding: 15px; border-radius: 18px; border: 1px solid #f1f5f9; font-weight:600;">
                                        <i class="fa-solid fa-prescription-bottle-medical" style="color:#2563eb;"></i>
                                        <?= htmlspecialchars(substr($r['treatment'] ?? 'No treatment logged.', 0, 70)) ?>...
                                    </div>
                                </div>
                                <div class="console-footer"><i class="fa-solid fa-fingerprint"></i> INITIATE SETTLEMENT</div>
                            </a>
                        <?php endwhile; else: ?>
                        <div
                            style="text-align: center; padding: 5rem 2rem; color: #94a3b8; border: 2px dashed #e2e8f0; border-radius: 35px; background:#fff;">
                            <i class="fa-solid fa-sack-xmark" style="font-size: 4rem; margin-bottom: 20px; opacity:0.3;"></i>
                            <h4 style="font-weight:900; color:#cbd5e1; font-size:1.2rem;">Revenue Queue Empty</h4>
                            <p style="font-weight:600; font-size:0.9rem;">All active medical cases have been settled.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="card-title" style="font-weight:950; font-size:1.5rem;">Official Revenue Ledger</h3>
                    <div style="position:relative;">
                        <i class="fa-solid fa-search" style="position:absolute; left:18px; top:18px; color:#64748b;"></i>
                        <input type="text" id="receiptSearch" placeholder="Search by name or slip ID..."
                            style="padding:15px 15px 15px 45px; border-radius:50px; border:2px solid #e2e8f0; width:350px; outline:none; font-weight:600; font-size:0.9rem;"
                            onfocus="this.style.borderColor='#2563eb'">
                    </div>
                </div>
                <div class="table-container"
                    style="margin-top:2.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 35px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
                    <table class="table">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th
                                    style="padding: 22px; font-weight:950; color:#1e293b; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px;">
                                    Patient Identity</th>
                                <th
                                    style="font-weight:950; color:#1e293b; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px;">
                                    Insurance</th>
                                <th
                                    style="font-weight:950; color:#1e293b; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px;">
                                    Final Bill</th>
                                <th
                                    style="font-weight:950; color:#1e293b; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px;">
                                    Status</th>
                                <th
                                    style="text-align: center; font-weight:950; color:#1e293b; text-transform:uppercase; font-size:0.75rem; letter-spacing:1px;">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="revenueTable">
                            <?php
                            $res = $conn->query("SELECT b.*, p.first_name, p.last_name, p.insurance FROM billing b JOIN patients p ON b.patient_id = p.patient_id ORDER BY b.billing_date DESC LIMIT 15");
                            while ($row = $res->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f8fafc;">
                                    <td style="padding: 20px;">
                                        <div style="font-weight:900; color:#0f172a;">
                                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                        </div>
                                        <div style="font-size:0.75rem; color:#94a3b8; font-weight:600;">
                                            #SLIP-<?= str_pad($row['bill_id'], 5, '0', STR_PAD_LEFT) ?></div>
                                    </td>
                                    <td><span
                                            style="font-weight: 800; color: #64748b; font-size:0.85rem; border: 1px solid #e2e8f0; padding:4px 12px; border-radius:50px;"><?= htmlspecialchars($row['insurance']) ?></span>
                                    </td>
                                    <td><strong
                                            style="color: #2563eb; font-size:1.1rem; font-weight:900;">$<?= number_format($row['total_amount'], 2) ?></strong>
                                    </td>
                                    <td><span
                                            class="<?= ($row['payment_status'] == 'Paid') ? 'badge-paid' : 'badge-debt' ?>"><?= strtoupper($row['payment_status']) ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="?print_slip=<?= (int) $row['bill_id'] ?>" class="btn"
                                            style="background:#0f172a; color:white; padding:10px 22px; border-radius: 12px; font-size: 0.8rem; font-weight:800; display:inline-flex; align-items:center; gap:8px; transition:0.3s;">
                                            <i class="fa-solid fa-file-pdf"></i> DOWNLOAD SLIP
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <script>
                    document.getElementById('receiptSearch')?.addEventListener('input', function (e) {
                        const term = e.target.value.toLowerCase();
                        const rows = document.querySelectorAll('#revenueTable tr');
                        rows.forEach(row => { row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none'; });
                    });
                </script>
            </section>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
<?php $conn->close(); ?>