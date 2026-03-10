<?php require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/access_control.php';
restrict_access(['Doctor']);
$doc_ref_id = $_SESSION['related_id'] ?? 0;
$role = $_SESSION['role'] ?? 'Doctor';

// Fetch Medicines for dropdown
$meds_res = $conn->query("SELECT * FROM medicines ORDER BY name ASC");
$medicines_inventory = [];
while ($m = $meds_res->fetch_assoc()) {
    $medicines_inventory[] = $m;
}
?>
<?php include 'header.php'; ?>

<style>
    /* ============================================
   CLINICAL CONSULTATION DESK — "THE DOCTOR'S SUITE"
   ============================================ */
    .desk-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .desk-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 1.5rem 2.2rem;
        border-radius: 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .desk-title h1 {
        margin: 0;
        font-size: 1.6rem;
        font-weight: 900;
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* SECTION LABELS */
    .section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #1e40af;
        margin-bottom: 1.2rem;
        padding-bottom: 10px;
        border-bottom: 2px solid #e2e8f0;
    }

    /* QUEUE CARD */
    .queue-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .patient-card {
        background: white;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        padding: 1.2rem 1.8rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.25s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .patient-card:hover {
        transform: translateX(5px);
        border-color: #3b82f6;
        box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
    }

    .patient-identity {
        display: flex;
        align-items: center;
        gap: 1.2rem;
    }

    .patient-avatar {
        width: 60px;
        height: 60px;
        background: #eff6ff;
        color: #2563eb;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 1px solid #dbeafe;
    }

    .patient-name {
        font-size: 1.1rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .patient-meta {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
        margin-top: 4px;
        display: flex;
        gap: 10px;
    }

    .complaint-preview {
        max-width: 400px;
        font-size: 0.82rem;
        color: #475569;
        padding: 8px 15px;
        background: #f8fafc;
        border-radius: 10px;
        border-left: 4px solid #3b82f6;
    }

    /* CONSULTATION DESK (OPENED FISH) */
    .consultation-desk {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
        margin-top: 1rem;
        animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* THE LEFT SIDE — READ ONLY FISH DATA */
    .fish-summary-card {
        background: #f8faff;
        border: 1.5px solid #dbeafe;
        border-radius: 20px;
        padding: 1.5rem;
        height: fit-content;
        position: sticky;
        top: 20px;
    }

    .fish-summary-card .header {
        background: #1e40af;
        color: white;
        margin: -1.5rem -1.5rem 1.5rem -1.5rem;
        padding: 1.2rem;
        border-radius: 18px 18px 0 0;
        text-align: center;
    }

    .data-row {
        margin-bottom: 1.2rem;
    }

    .data-label {
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #64748b;
        letter-spacing: 1px;
        display: block;
        margin-bottom: 4px;
    }

    .data-value {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.4;
    }

    .vital-pill {
        background: white;
        border: 1px solid #fee2e2;
        color: #b91c1c;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.75rem;
        display: inline-block;
        margin-right: 5px;
        margin-top: 5px;
    }

    /* THE RIGHT SIDE — TREATMENT RECORD FORM */
    .treatment-form-card {
        background: white;
        border-radius: 20px;
        padding: 2.2rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }

    .treatment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .treatment-input-group {
        margin-bottom: 1.5rem;
    }

    .treatment-input-group label {
        display: block;
        font-size: 0.72rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .treatment-field {
        width: 100%;
        padding: 0.85rem 1rem;
        border-radius: 12px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        font-size: 0.95rem;
        transition: all 0.2s;
    }

    .treatment-field:focus {
        outline: none;
        border-color: #2563eb;
        background: white;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .treatment-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .finalize-btn {
        width: 100%;
        padding: 1.2rem;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        border: none;
        border-radius: 14px;
        font-size: 1.1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 1rem;
    }

    .finalize-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(37, 99, 235, 0.4);
    }

    .badge-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fff7ed;
        color: #9a3412;
        border: 1px solid #ffedd5;
    }

    /* PATHWAY CARDS */
    .pathway-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.2rem;
        margin-top: 1rem;
    }

    .pathway-card {
        cursor: pointer;
        position: relative;
    }

    .pathway-card input {
        display: none;
    }

    .pathway-inner {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: all 0.2s;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .pathway-inner i {
        font-size: 1.8rem;
        color: #64748b;
    }

    .pathway-inner span {
        font-size: 0.85rem;
        font-weight: 900;
        color: #1e293b;
        display: block;
    }

    .pathway-inner p {
        font-size: 0.65rem;
        color: #94a3b8;
        margin: 0;
        line-height: 1.4;
    }

    .pathway-card input:checked+.pathway-inner {
        border-color: #2563eb;
        background: #eff6ff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
    }

    .pathway-card input:checked+.pathway-inner i {
        color: #2563eb;
    }

    @media (max-width: 900px) {
        .consultation-desk {
            grid-template-columns: 1fr;
        }

        .fish-summary-card {
            position: static;
        }

        .pathway-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="desk-wrapper">

    <!-- HEADER -->
    <div class="desk-header">
        <div class="desk-title">
            <h1><i class="fa-solid fa-hospital-user"></i> CLINICAL CONSULTATION DESK</h1>
            <span style="font-size:0.8rem; opacity:0.8; font-weight:500;">Secure Medical Interface — Specialist
                Portal</span>
        </div>
        <div style="text-align:right;">
            <div style="font-weight:800; font-size:0.9rem;">Dr. <?= htmlspecialchars($_SESSION['username']) ?></div>
            <div style="font-size:0.75rem; color:#94a3b8; font-weight:600;"><i class="fa-solid fa-circle"
                    style="color:#22c55e; font-size:0.5rem;"></i> Active Consultation Session</div>
        </div>
    </div>

    <?php if (!isset($_GET['open_case'])): ?>
        <!-- PATIENT QUEUE VIEW -->
        <section>
            <div class="section-label"><i class="fa-solid fa-people-arrows"></i> Registered Patient Queue ("The Fish" Inbox)
            </div>

            <div class="queue-container">
                <?php
                $condition = "pc.status = 'Pending'";
                if ($role !== 'SuperAdmin') {
                    $condition .= " AND (pc.doctor_id = $doc_ref_id OR pc.doctor_id = 0)";
                }

                $sql = "SELECT pc.*, p.first_name, p.last_name, p.gender, p.national_id, p.insurance, p.is_head_of_family 
                    FROM patient_cases pc
                    JOIN patients p ON pc.patient_id = p.patient_id
                    WHERE $condition
                    ORDER BY pc.created_at DESC";
                $res = $conn->query($sql);

                if ($res && $res->num_rows > 0):
                    while ($row = $res->fetch_assoc()):
                        $time = date('H:i', strtotime($row['created_at']));
                        $date = date('M d, Y', strtotime($row['created_at']));
                        ?>
                        <div class="patient-card">
                            <div class="patient-identity">
                                <div class="patient-avatar">
                                    <i class="fa-solid <?= $row['gender'] == 'Female' ? 'fa-user-nurse' : 'fa-user-injured' ?>"></i>
                                </div>
                                <div>
                                    <h3 class="patient-name"><?= $row['first_name'] . ' ' . $row['last_name'] ?></h3>
                                    <div class="patient-meta">
                                        <span><i class="fa-solid fa-hashtag"></i> ID: <?= $row['national_id'] ?></span>
                                        <span><i class="fa-solid fa-shield-halved"></i> <?= $row['insurance'] ?></span>
                                        <span class="badge-status status-pending"><?= $row['status'] ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="complaint-preview">
                                <strong><i class="fa-solid fa-notes-medical"></i> Intake Complaint:</strong><br>
                                <?= htmlspecialchars($row['chief_complaint'] ?: 'No symptoms recorded.') ?>
                            </div>

                            <div style="text-align:right;">
                                <div style="font-size:0.75rem; font-weight:700; color:#1e293b;"><?= $time ?></div>
                                <div style="font-size:0.65rem; color:#64748b;"><?= $date ?></div>
                                <a href="?open_case=<?= $row['case_id'] ?>" class="btn btn-primary"
                                    style="margin-top:8px; padding:8px 20px; border-radius:10px; font-weight:700;">
                                    <i class="fa-solid fa-door-open"></i> START TREATEMENT
                                </a>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                    <div
                        style="text-align:center; padding:4rem; background:#f8fafc; border-radius:20px; border:2px dashed #e2e8f0; color:#64748b;">
                        <i class="fa-solid fa-inbox" style="font-size:3rem; margin-bottom:1rem; opacity:0.3;"></i>
                        <h3>Patient Queue Empty</h3>
                        <p>No new intakes have been assigned to you from reception.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    <?php else:
        // CONSULTATION VIEW (Record Based Fish)
        $cid = (int) $_GET['open_case'];
        $res = $conn->query("SELECT pc.*, p.* FROM patient_cases pc JOIN patients p ON pc.patient_id = p.patient_id WHERE pc.case_id = $cid");
        $case = $res->fetch_assoc();
        if ($case):
            ?>

            <div class="consultation-desk">

                <!-- SIDEBAR: THE FISH (Intake Data) -->
                <div class="fish-summary-card">
                    <div class="header">
                        <i class="fa-solid fa-fish" style="font-size:1.5rem;"></i>
                        <div style="font-size:0.6rem; font-weight:800; letter-spacing:2px; margin-top:5px;">INTAKE RECORD</div>
                        <div style="font-size:1.1rem; font-weight:900;"><?= $case['first_name'] ?></div>
                    </div>

                    <div class="data-row">
                        <span class="data-label">Patient Identity</span>
                        <span class="data-value"><?= $case['first_name'] . ' ' . $case['last_name'] ?></span>
                        <span class="data-value" style="font-size:0.75rem; color:#64748b;">ID: <?= $case['national_id'] ?>
                            (<?= $case['gender'] ?>)</span>
                    </div>

                    <div class="data-row">
                        <span class="data-label">Contact & Residence</span>
                        <span class="data-value"><?= $case['phone'] ?: 'No Phone' ?></span>
                        <span class="data-value" style="font-size:0.7rem; color:#64748b;"><?= $case['address'] ?></span>
                        <span class="data-value" style="font-size:0.7rem; color:#64748b;">District: <?= $case['district'] ?> |
                            Cell: <?= $case['cell'] ?></span>
                    </div>

                    <div class="data-row" style="background:white; padding:10px; border-radius:10px; border:1px solid #fee2e2;">
                        <span class="data-label" style="color:#b91c1c;">Intake Vitals</span>
                        <?php
                        $vitals = explode(' | ', $case['vitals']);
                        foreach ($vitals as $v)
                            echo "<span class='vital-pill'>$v</span>";
                        ?>
                    </div>

                    <div class="data-row"
                        style="background:#f0fdf4; padding:10px; border-radius:10px; border:1px solid #bbf7d0; margin-top:1rem;">
                        <span class="data-label" style="color:#15803d;">Initial Complaint</span>
                        <span class="data-value"
                            style="font-style:italic; font-weight:600; color:#15803d;">"<?= nl2br(htmlspecialchars($case['chief_complaint'])) ?>"</span>
                    </div>

                    <div class="data-row">
                        <span class="data-label">Insurance Billing</span>
                        <span class="data-value" style="color:#3b82f6;"><i class="fa-solid fa-shield-check"></i>
                            <?= $case['insurance_id'] ?></span>
                    </div>

                    <a href="doctor_portal.php" class="btn btn-outline"
                        style="width:100%; margin-top:1rem; border-color:#dbeafe; color:#1e40af;">
                        <i class="fa-solid fa-arrow-left"></i> Close & Exit Room
                    </a>
                </div>

                <!-- MAIN: TREATMENT FORM -->
                <div class="treatment-form-card">
                    <div class="section-label"><i class="fa-solid fa-file-medical"></i>Finalize Treatement &amp; Secure Medical
                        Record</div>

                    <form method="POST">
                        <input type="hidden" name="case_id" value="<?= $case['case_id'] ?>">
                        <input type="hidden" name="patient_id" value="<?= $case['patient_id'] ?>">

                        <div class="treatment-grid">
                            <div class="treatment-input-group">
                                <label><i class="fa-solid fa-envelope"></i> Patient Official Email (Optional — To Send
                                    Record)</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($case['email']) ?>"
                                    placeholder="patient@example.rw" class="treatment-field">
                            </div>

                            <div class="treatment-input-group">
                                <label><i class="fa-solid fa-comment-sms"></i> Mobile Phone (For SMS Notification)</label>
                                <input type="text" name="phone_sms" value="<?= htmlspecialchars($case['phone']) ?>"
                                    placeholder="07X XXX XXXX" class="treatment-field">
                            </div>
                        </div>

                        <div class="treatment-grid">
                            <div class="treatment-input-group" style="grid-column: span 2;">
                                <label><i class="fa-solid fa-microscope"></i> Medical Test Results (Labs/Radiology)</label>
                                <textarea name="tests" placeholder="Describe laboratory findings, pulse, reflexes, etc..."
                                    class="treatment-field treatment-textarea"></textarea>
                            </div>

                            <div class="treatment-input-group">
                                <label><i class="fa-solid fa-magnifying-glass-chart"></i> Official Diagnosis (Disease
                                    Type)</label>
                                <textarea name="diagnosis" required placeholder="Final medical diagnosis..."
                                    class="treatment-field treatment-textarea"></textarea>
                            </div>

                            <div class="treatment-input-group">
                                <label><i class="fa-solid fa-pills"></i> Treatment & Prescription</label>
                                <textarea name="treatment" required placeholder="Medications, dosage, next steps..."
                                    class="treatment-field treatment-textarea"></textarea>
                            </div>

                            <div class="treatment-input-group">
                                <label><i class="fa-solid fa-scissors"></i> Operation / Surgery Summary</label>
                                <textarea name="operation" placeholder="If a surgical procedure was performed..."
                                    class="treatment-field treatment-textarea"></textarea>
                            </div>

                            <!-- DYNAMIC MEDICINE SELECTION -->
                            <div class="treatment-input-group"
                                style="grid-column: span 2; background: #f8fafc; padding: 1.5rem; border-radius: 20px; border: 1px solid #e2e8f0; margin-top: 1rem;">
                                <label style="color: #0f172a; font-weight: 800; margin-bottom: 1rem; display: block;">
                                    <i class="fa-solid fa-capsules"></i> PHARMACY DISPENSING ORDER
                                </label>
                                <div id="medicine-list">
                                    <div class="medicine-row"
                                        style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 10px; margin-bottom: 12px; background: white; padding: 10px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); align-items: center;">
                                        <div style="position: relative;">
                                            <i class="fa-solid fa-pills"
                                                style="position: absolute; left: 12px; top: 18px; color: #94a3b8; font-size: 0.8rem;"></i>
                                            <select name="medicines[]" class="treatment-field" style="padding-left: 35px;">
                                                <option value="">-- Select Medicine --</option>
                                                <?php foreach ($medicines_inventory as $med): ?>
                                                    <option value="<?= $med['medicine_id'] ?>"><?= htmlspecialchars($med['name']) ?>
                                                        ($<?= number_format($med['price'], 2) ?>)</option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <input type="number" name="quantities[]" placeholder="Qty" min="1"
                                            class="treatment-field" style="text-align: center;">
                                        <input type="text" name="dosages[]" placeholder="1x3, 2x1..." class="treatment-field">
                                        <button type="button" class="btn" onclick="this.parentElement.remove()"
                                            style="background: #fee2e2; color: #ef4444; padding: 12px 15px; border-radius: 10px;"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline" onclick="addMedicineRow()"
                                    style="margin-top: 10px; font-size: 0.8rem; padding: 5px 15px;">
                                    <i class="fa-solid fa-plus-circle"></i> ADD ANOTHER MEDICINE
                                </button>

                                <script>
                                    function addMedicineRow() {
                                        const row = document.querySelector('.medicine-row').cloneNode(true);
                                        row.querySelectorAll('input').forEach(input => input.value = '');
                                        row.querySelector('select').value = '';
                                        document.getElementById('medicine-list').appendChild(row);
                                    }
                                </script>
                            </div>

                            <div class="treatment-input-group"
                                style="grid-column: span 2; background: #fff; padding: 1.5rem; border-radius: 20px; border: 2px solid #e2e8f0; margin-top: 1rem;">
                                <label style="color: #1e40af; text-align: center; width: 100%; margin-bottom: 1.2rem;"><i
                                        class="fa-solid fa-route"></i> END OF TREATMENT — SELECT EXIT PATHWAY</label>

                                <div class="pathway-grid">
                                    <label class="pathway-card">
                                        <input type="radio" name="pathway" value="admission">
                                        <div class="pathway-inner">
                                            <i class="fa-solid fa-bed-pulse"></i>
                                            <span>HOSPITAL ADMISSION</span>
                                            <p>Refer to Ward for Inpatient Care</p>
                                        </div>
                                    </label>
                                    <label class="pathway-card">
                                        <input type="radio" name="pathway" value="lab">
                                        <div class="pathway-inner">
                                            <i class="fa-solid fa-vials"></i>
                                            <span>SEND TO LABORATORY</span>
                                            <p>Request Tech Investigation</p>
                                        </div>
                                    </label>
                                    <label class="pathway-card">
                                        <input type="radio" name="pathway" value="billing" checked>
                                        <div class="pathway-inner">
                                            <i class="fa-solid fa-file-invoice-dollar"></i>
                                            <span>SEND TO RECEPTION</span>
                                            <p>Finalize Bill & Pharmacy Costs</p>
                                        </div>
                                    </label>
                                </div>

                                <div id="lab_note"
                                    style="margin-top: 1.5rem; display: none; background: #fdf2f2; padding: 1rem; border-radius: 12px;">
                                    <label style="font-size: 0.65rem; color: #b91c1c;"><i class="fa-solid fa-vial"></i> Lab
                                        Instructions</label>
                                    <input type="text" name="lab_instructions" placeholder="e.g. Malaria RDT, Blood Count..."
                                        class="treatment-field">
                                </div>
                            </div>
                        </div>

                        <script>
                            document.querySelectorAll('input[name="pathway"]').forEach(radio => {
                                radio.addEventListener('change', (e) => {
                                    document.getElementById('lab_note').style.display = (e.target.value === 'lab') ? 'block' : 'none';
                                });
                            });
                        </script>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" name="finalize_treatment" class="finalize-btn">
                                <i class="fa-solid fa-file-export"></i> DISPATCH RECORD TO RECEPTION DASHBOARD
                            </button>
                            <button type="submit" name="finalize_and_download" class="finalize-btn"
                                style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);">
                                <i class="fa-solid fa-file-pdf"></i> FINALIZE & DOWNLOAD REPORT
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <?php
        endif;
    endif;

    // Handle Submission
    if (isset($_POST['finalize_treatment']) || isset($_POST['finalize_and_download'])) {
        $cid = (int) $_POST['case_id'];
        $pid = (int) $_POST['patient_id'];
        $diag = $_POST['diagnosis'];
        $treat = $_POST['treatment'];
        $tests = $_POST['tests'];
        $ops = $_POST['operation'];
        $email = $_POST['email'];
        $phone_sms = $_POST['phone_sms'];
        $date = date('Y-m-d H:i:s');
        $doc_id = $doc_ref_id ?: 1;

        // Update patient info
        $conn->query("UPDATE patients SET email = '$email', phone = '$phone_sms' WHERE patient_id = $pid");

        // Save Medical Record
        $stmt = $conn->prepare("INSERT INTO medicalrecords (patient_id, doctor_id, diagnosis, treatment, record_date, test_results, operation_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssss", $pid, $doc_id, $diag, $treat, $date, $tests, $ops);

        if ($stmt->execute()) {
            $record_id = $stmt->insert_id;
            $pathway = $_POST['pathway'];
            $lab_inst = $_POST['lab_instructions'] ?? '';

            // SAVE PRESCRIPTIONS
            if (isset($_POST['medicines'])) {
                $med_ids = $_POST['medicines'];
                $qtys = $_POST['quantities'];
                $dosages = $_POST['dosages'];

                $presc_stmt = $conn->prepare("INSERT INTO prescriptions (record_id, medicine_id, quantity, dosage) VALUES (?, ?, ?, ?)");
                foreach ($med_ids as $index => $mid) {
                    if (!empty($mid)) {
                        $q = (int) $qtys[$index];
                        $d = $dosages[$index];
                        $presc_stmt->bind_param("iiis", $record_id, $mid, $q, $d);
                        $presc_stmt->execute();
                    }
                }
            }

            // Branching Logic
            if ($pathway === 'lab') {
                // Update Case to LabPending
                $conn->query("UPDATE patient_cases SET status = 'LabPending', chief_complaint = CONCAT(chief_complaint, ' | LAB REQ: ', '$lab_inst') WHERE case_id = $cid");
            } else {
                // Sent to Reception for Billing
                $conn->query("UPDATE patient_cases SET status = 'BillingPending' WHERE case_id = $cid");
            }

            if (isset($_POST['finalize_and_download'])) {
                echo "<script>window.open('download_record.php?case_id=$cid', '_blank');</script>";
            }

            if ($pathway === 'lab') {
                echo "<div style='background:#7c3aed; color:white; padding:2rem; border-radius:20px; text-align:center; margin-top:2rem;'>
                        <i class='fa-solid fa-microscope' style='font-size:3rem; margin-bottom:1rem;'></i>
                        <h2>Sent to Laboratory</h2>
                        <p>Patient ID #$pid has been successfully referred for medical testing. Redirecting to Patient List...</p>
                        <script>setTimeout(()=> { window.location='patients.php'; }, 2000);</script>
                      </div>";
            } elseif ($pathway === 'admission') {
                echo "<div style='background:#1e40af; color:white; padding:2rem; border-radius:20px; text-align:center; margin-top:2rem;'>
                        <i class='fa-solid fa-bed' style='font-size:3rem; margin-bottom:1rem;'></i>
                        <h2>Admission Recommended</h2>
                        <p>Transferring patient to hospitalization ward. Redirecting to Ward Management...</p>
                        <script>setTimeout(()=> { window.location='manage_rooms.php?patient_id=$pid'; }, 2000);</script>
                      </div>";
            } else {
                echo "<div style='background:#16a34a; color:white; padding:2rem; border-radius:20px; text-align:center; margin-top:2rem;'>
                        <i class='fa-solid fa-circle-check' style='font-size:3rem; margin-bottom:1rem;'></i>
                        <h2>Record Dispatched to Reception</h2>
                        <p>The medical record is finalized. Reception dashboard will now receive the record for medicine calculation and final billing.</p>
                        <script>setTimeout(()=> { window.location='doctor_portal.php'; }, 2000);</script>
                      </div>";
            }
        } else {
            echo "<div class='badge badge-danger' style='padding:2rem; margin-top:2rem;'>Error securing record: " . htmlspecialchars($conn->error) . "</div>";
        }
    }
    ?>

</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>