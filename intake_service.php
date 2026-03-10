<?php require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/access_control.php';
restrict_access(['Staff', 'Reception', 'Admin']);
?>
<?php include 'header.php'; ?>

<style>
    /* ============================================
   THE FISH — Hospital Clinical Intake Sheet
   Premium Document Design
   ============================================ */
    .fish-wrapper {
        max-width: 900px;
        margin: 0 auto;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }

    .fish-document {
        background: #fff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 4px 16px rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f2;
    }

    /* -------- HEADER BANNER -------- */
    .fish-header {
        background: linear-gradient(135deg, #1a3a6b 0%, #1565c0 55%, #0d47a1 100%);
        padding: 0;
        position: relative;
        overflow: hidden;
    }

    .fish-header::before {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 50%;
    }

    .fish-header::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: -30px;
        width: 140px;
        height: 140px;
        background: rgba(255, 255, 255, 0.04);
        border-radius: 50%;
    }

    .fish-header-inner {
        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;
        gap: 1.5rem;
        padding: 1.8rem 2.2rem;
        position: relative;
        z-index: 1;
    }

    .fish-logo-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(6px);
        border: 1.5px solid rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
        flex-shrink: 0;
    }

    .fish-title-block h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 900;
        color: white;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .fish-title-block p {
        margin: 4px 0 0 0;
        font-size: 0.78rem;
        color: rgba(255, 255, 255, 0.75);
        font-weight: 500;
    }

    .fish-reg-badge {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 10px 18px;
        text-align: center;
        color: white;
        flex-shrink: 0;
    }

    .fish-reg-badge .label {
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.7;
        display: block;
        margin-bottom: 3px;
    }

    .fish-reg-badge .value {
        font-size: 0.85rem;
        font-weight: 800;
    }

    /* Strip below header */
    .fish-strip {
        height: 5px;
        background: linear-gradient(90deg, #e53935, #e91e63, #f57c00, #fdd835, #43a047, #1e88e5, #8e24aa);
    }

    /* -------- SECTION LABELS -------- */
    .fish-body {
        padding: 2rem 2.2rem;
    }

    .fish-section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #1565c0;
        margin-bottom: 1rem;
        margin-top: 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #e8eef7;
    }

    .fish-section-label i {
        font-size: 0.85rem;
    }

    /* -------- FORM FIELDS -------- */
    .fish-field-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #64748b;
        margin-bottom: 5px;
    }

    .fish-field-label i {
        color: #1565c0;
        margin-right: 4px;
    }

    .fish-input,
    .fish-select,
    .fish-textarea {
        width: 100%;
        padding: 0.72rem 1rem;
        border-radius: 10px;
        border: 1.5px solid #e2e8f0;
        background: #f8faff;
        font-size: 0.88rem;
        color: #1e293b;
        font-family: inherit;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        box-sizing: border-box;
    }

    .fish-input:focus,
    .fish-select:focus,
    .fish-textarea:focus {
        outline: none;
        border-color: #1565c0;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.1);
    }

    .fish-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%231565c0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        padding-right: 2.2rem;
        cursor: pointer;
    }

    .fish-textarea {
        resize: vertical;
        min-height: 80px;
        line-height: 1.6;
    }

    /* Input wrapper for icon overlay */
    .fish-input-wrap {
        position: relative;
    }

    .fish-input-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.8rem;
        pointer-events: none;
    }

    /* Grid layouts */
    .fish-grid-3 {
        display: grid;
        grid-template-columns: 2.2fr 1.5fr 0.8fr;
        gap: 1rem;
    }

    .fish-grid-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.8rem;
    }

    .fish-grid-2 {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 0.8rem;
    }

    .fish-grid-address {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.8rem;
    }

    /* -------- ADDRESS BLOCK -------- */
    .fish-address-block {
        background: linear-gradient(135deg, #f0f4ff 0%, #f8faff 100%);
        border: 1.5px solid #c7d2fe;
        border-radius: 14px;
        padding: 1.2rem 1.5rem 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .fish-address-block::before {
        content: 'RWANDA ADDRESS REGISTRY';
        position: absolute;
        top: -9px;
        left: 20px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: #1565c0;
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* -------- VITALS BLOCK -------- */
    .fish-vitals-block {
        background: linear-gradient(135deg, #fff5f5 0%, #fef2f2 100%);
        border: 1.5px solid #fecaca;
        border-radius: 14px;
        padding: 1.2rem 1.5rem 1.5rem;
        position: relative;
    }

    .fish-vitals-block::before {
        content: 'CLINICAL VITALS';
        position: absolute;
        top: -9px;
        left: 20px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: #e53935;
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* -------- COMPLAINT BLOCK -------- */
    .fish-complaint-block {
        background: linear-gradient(135deg, #f0fdf4 0%, #f7fff8 100%);
        border: 1.5px solid #bbf7d0;
        border-radius: 14px;
        padding: 1.2rem 1.5rem 1.5rem;
        position: relative;
    }

    .fish-complaint-block::before {
        content: 'CHIEF COMPLAINT & SYMPTOMS';
        position: absolute;
        top: -9px;
        left: 20px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: #16a34a;
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* -------- INSURANCE BLOCK -------- */
    .fish-ins-block {
        background: linear-gradient(135deg, #fefce8 0%, #fffbeb 100%);
        border: 1.5px solid #fde68a;
        border-radius: 14px;
        padding: 1.2rem 1.5rem 1.5rem;
        position: relative;
    }

    .fish-ins-block::before {
        content: 'INSURANCE & BILLING';
        position: absolute;
        top: -9px;
        left: 20px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: #d97706;
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* -------- HEAD OF FAMILY TOGGLE -------- */
    .fish-toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 0.5rem;
    }

    .fish-toggle-row input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #1565c0;
        flex-shrink: 0;
    }

    .fish-toggle-row span {
        font-size: 0.78rem;
        color: #475569;
        font-weight: 600;
    }

    /* -------- DOCTOR REFERRAL -------- */
    .fish-doctor-block {
        background: linear-gradient(135deg, #f5f0ff 0%, #faf7ff 100%);
        border: 1.5px solid #ddd6fe;
        border-radius: 14px;
        padding: 1.2rem 1.5rem 1.5rem;
        position: relative;
    }

    .fish-doctor-block::before {
        content: 'SPECIALIST REFERRAL';
        position: absolute;
        top: -9px;
        left: 20px;
        font-size: 0.58rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        background: #7c3aed;
        color: white;
        padding: 2px 10px;
        border-radius: 20px;
    }

    /* -------- SUBMIT BUTTON -------- */
    .fish-submit-btn {
        width: 100%;
        padding: 1.1rem 2rem;
        background: linear-gradient(135deg, #1a3a6b 0%, #1565c0 100%);
        color: white;
        border: none;
        border-radius: 14px;
        font-size: 1rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.25s ease;
        box-shadow: 0 6px 20px rgba(21, 101, 192, 0.35);
        margin-top: 1.8rem;
    }

    .fish-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 28px rgba(21, 101, 192, 0.45);
    }

    .fish-submit-btn:active {
        transform: translateY(0);
    }

    .fish-submit-btn i {
        font-size: 1.2rem;
    }

    /* -------- DIVIDER -------- */
    .fish-divider {
        border: none;
        border-top: 1.5px dashed #e2e8f0;
        margin: 1.8rem 0;
    }

    /* -------- STATUS BADGES -------- */
    .nid-badge,
    .ins-badge,
    .v-badge {
        font-size: 0.62rem;
        font-weight: 700;
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 4px;
        min-height: 18px;
    }

    /* -------- SUCCESS / ERROR RESULT -------- */
    .fish-result {
        border-radius: 14px;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-top: 1.5rem;
        border: 1.5px solid;
    }

    .fish-result.success {
        background: #ecfdf5;
        border-color: #16a34a;
        color: #15803d;
    }

    .fish-result.error {
        background: #fef2f2;
        border-color: #dc2626;
        color: #b91c1c;
    }

    .fish-result i {
        font-size: 2rem;
        flex-shrink: 0;
    }

    .fish-result-title {
        font-size: 1.05rem;
        font-weight: 800;
        display: block;
        margin-bottom: 3px;
    }

    .fish-result-detail {
        font-size: 0.85rem;
    }

    /* -------- FOOTER SEAL -------- */
    .fish-footer-seal {
        background: #f8faff;
        border-top: 1.5px dashed #dde4f0;
        padding: 1rem 2.2rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.65rem;
        color: #94a3b8;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .fish-footer-seal span i {
        margin-right: 4px;
    }

    @media (max-width: 700px) {

        .fish-grid-3,
        .fish-grid-4,
        .fish-grid-address {
            grid-template-columns: 1fr 1fr;
        }

        .fish-grid-2 {
            grid-template-columns: 1fr;
        }

        .fish-header-inner {
            grid-template-columns: auto 1fr;
        }

        .fish-reg-badge {
            display: none;
        }
    }
</style>

<div class="fish-wrapper">

    <!-- ===== THE FISH: DOCUMENT SHELL ===== -->
    <div class="fish-document">

        <!-- HEADER -->
        <div class="fish-header">
            <div class="fish-header-inner">
                <div class="fish-logo-badge">
                    <i class="fa-solid fa-file-medical-alt"></i>
                </div>
                <div class="fish-title-block">
                    <h1><i class="fa-solid fa-fish"
                            style="font-size:1.1rem; margin-right:6px; opacity:0.8;"></i>CLINICAL INTAKE RECORD <span
                            style="font-size:0.7rem; background:rgba(255,255,255,0.15); padding:2px 10px; border-radius:20px; vertical-align:middle; font-weight:600;">THE
                            FISH</span></h1>
                    <p><i class="fa-solid fa-hospital"></i>&nbsp; Pre-Treatment Patient Registration &amp; Doctor
                        Referral &nbsp;|&nbsp; Reception Desk</p>
                </div>
                <div class="fish-reg-badge">
                    <span class="label">Desk Time</span>
                    <span class="value" id="live-time">--:--</span>
                    <span class="label" style="margin-top:6px;">Date</span>
                    <span class="value" id="live-date">---</span>
                </div>
            </div>
        </div>
        <div class="fish-strip"></div>

        <!-- FORM -->
        <form method="POST" id="intakeForm">
            <div class="fish-body">

                <!-- ── SECTION 1: PATIENT IDENTITY ── -->
                <p class="fish-section-label"><i class="fa-solid fa-id-card"></i> Section 1 — Patient Identity</p>
                <div class="fish-grid-3" style="margin-bottom:1.5rem;">
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-user-tag"></i> Full Name</label>
                        <div class="fish-input-wrap">
                            <input type="text" name="full_name" required placeholder="First &amp; Last Name"
                                class="fish-input">
                            <i class="fish-input-icon fa-solid fa-pen-to-square"></i>
                        </div>
                    </div>
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-fingerprint"></i> National ID</label>
                        <input type="text" name="national_id" required placeholder="16-Digit Govt. ID" maxlength="16"
                            class="fish-input" id="nidField">
                        <div class="nid-badge" id="nid-status"></div>
                    </div>
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-venus-mars"></i> Gender</label>
                        <select name="gender" required class="fish-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Extra identity fields -->
                <div class="fish-grid-4" style="margin-bottom:1.5rem;">
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-calendar-days"></i> Date of Birth</label>
                        <input type="date" name="date_of_birth" class="fish-input">
                    </div>
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-phone"></i> Phone Number</label>
                        <input type="text" name="phone" placeholder="07X XXX XXXX" class="fish-input">
                    </div>
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-droplet"></i> Blood Group</label>
                        <select name="blood_group" class="fish-select">
                            <option value="">— Optional —</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="Unknown">Unknown</option>
                        </select>
                    </div>
                    <div>
                        <label class="fish-field-label"><i class="fa-solid fa-people-roof"></i> Parent /
                            Guardian</label>
                        <input type="text" name="parent_name" placeholder="Parent or guardian name" class="fish-input">
                    </div>
                </div>

                <hr class="fish-divider">

                <!-- ── SECTION 2: ADDRESS ── -->
                <p class="fish-section-label"><i class="fa-solid fa-map-location-dot"></i> Section 2 — Rwanda Address
                </p>
                <div class="fish-address-block">
                    <!-- Province & District: cascading dropdowns -->
                    <div class="fish-grid-address" style="margin-top:0.5rem;">
                        <div>
                            <label class="fish-field-label" style="color:#4f46e5;"><i class="fa-solid fa-flag"></i>
                                Province</label>
                            <select id="province" name="province" class="fish-select" required>
                                <option value="">Province</option>
                            </select>
                        </div>
                        <div>
                            <label class="fish-field-label" style="color:#4f46e5;"><i class="fa-solid fa-city"></i>
                                District</label>
                            <select id="district" name="district" class="fish-select" required>
                                <option value="">District</option>
                            </select>
                        </div>
                        <div>
                            <label class="fish-field-label" style="color:#4f46e5;"><i class="fa-solid fa-compass"></i>
                                Sector</label>
                            <select id="sector" name="sector" class="fish-select">
                                <option value="">Sector</option>
                            </select>
                        </div>
                        <!-- CELL & VILLAGE: plain text inputs, no dropdown -->
                        <div>
                            <label class="fish-field-label" style="color:#4f46e5;"><i class="fa-solid fa-map-pin"></i>
                                Cell</label>
                            <input type="text" id="cell" name="cell" placeholder="Enter cell name" class="fish-input">
                        </div>
                        <div>
                            <label class="fish-field-label" style="color:#4f46e5;"><i
                                    class="fa-solid fa-house-flag"></i> Village</label>
                            <input type="text" id="village" name="village" placeholder="Enter village name"
                                class="fish-input">
                        </div>
                    </div>
                    <div
                        style="margin-top:0.8rem; display:flex; align-items:center; gap:6px; font-size:0.68rem; color:#6366f1;">
                        <i class="fa-solid fa-circle-info"></i>
                        Province &amp; District auto-load from national registry. Cell &amp; Village entered manually.
                    </div>
                </div>

                <hr class="fish-divider">

                <!-- ── SECTION 3: CLINICAL & INSURANCE ── -->
                <p class="fish-section-label"><i class="fa-solid fa-stethoscope"></i> Section 3 — Clinical Information
                </p>
                <div class="fish-grid-2" style="gap:1.2rem; margin-bottom:1.5rem;">

                    <!-- Vitals -->
                    <div class="fish-vitals-block">
                        <div class="fish-grid-2" style="margin-top:0.5rem; gap:0.8rem;">
                            <div>
                                <label class="fish-field-label" style="color:#b91c1c;"><i
                                        class="fa-solid fa-heart-pulse"></i> Blood Pressure (Optional)</label>
                                <input type="text" name="bp" placeholder="e.g. 120/80 mmHg" class="fish-input">
                            </div>
                            <div>
                                <label class="fish-field-label" style="color:#b91c1c;"><i
                                        class="fa-solid fa-temperature-half"></i> Temperature (Optional)</label>
                                <input type="text" name="temp" placeholder="e.g. 37.0 °C" class="fish-input">
                            </div>
                            <div>
                                <label class="fish-field-label" style="color:#b91c1c;"><i
                                        class="fa-solid fa-weight-scale"></i> Weight (kg)</label>
                                <input type="text" name="weight" placeholder="e.g. 65 kg" class="fish-input">
                            </div>
                            <div>
                                <label class="fish-field-label" style="color:#b91c1c;"><i
                                        class="fa-solid fa-ruler-vertical"></i> Height (Optional)</label>
                                <input type="text" name="height" placeholder="e.g. 170 cm" class="fish-input">
                            </div>
                        </div>
                    </div>

                    <!-- Insurance -->
                    <div class="fish-ins-block">
                        <div style="margin-top:0.5rem;">
                            <label class="fish-field-label" style="color:#92400e;"><i
                                    class="fa-solid fa-shield-halved"></i> Insurance / Payment Plan</label>
                            <select name="insurance" id="insurance" required class="fish-select">
                                <option value="National Support (RSSI)">National Support (RSSI)</option>
                                <option value="RAMA">RAMA</option>
                                <option value="MMI">MMI</option>
                                <option value="Private (UAP/BRITAM)">Private Insurance</option>
                                <option value="None">Cash (Out-of-Pocket)</option>
                            </select>
                            <div class="ins-badge" id="insurance-status"></div>
                        </div>
                        <hr class="fish-divider" style="margin:1rem 0;">
                        <label class="fish-field-label" style="color:#92400e; margin-bottom:2px;"><i
                                class="fa-solid fa-house-user"></i> Family Registration</label>
                        <div class="fish-toggle-row">
                            <input type="checkbox" name="is_head" id="is_head">
                            <span>This patient is the <strong>Head of Family</strong> on the insurance plan</span>
                        </div>
                    </div>

                </div>

                <!-- Chief Complaint -->
                <div class="fish-complaint-block" style="margin-bottom:1.5rem;">
                    <div style="margin-top:0.5rem;">
                        <label class="fish-field-label" style="color:#15803d;"><i
                                class="fa-solid fa-comment-medical"></i> Initial Complaint (Optional)</label>
                        <textarea name="chief_complaint"
                            placeholder="Describe the primary reason for today's visit — symptoms, onset, duration, severity..."
                            class="fish-textarea" style="min-height:90px; margin-top:4px;"></textarea>
                    </div>
                </div>

                <hr class="fish-divider">

                <!-- ── SECTION 4: REFERRAL ── -->
                <p class="fish-section-label"><i class="fa-solid fa-user-doctor"></i> Section 4 — Specialist Referral
                </p>
                <div class="fish-doctor-block">
                    <div style="margin-top:0.5rem;">
                        <label class="fish-field-label" style="color:#6d28d9;"><i class="fa-solid fa-user-md"></i> Refer
                            Patient To</label>
                        <select name="doctor_id" required class="fish-select">
                            <option value="">— Select Available Specialist —</option>
                            <?php
                            $docRes = $conn->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors");
                            while ($d = $docRes->fetch_assoc()) {
                                echo "<option value='{$d['doctor_id']}'>Dr. {$d['first_name']} {$d['last_name']} – {$d['specialization']}</option>";
                            }
                            ?>
                        </select>
                        <p
                            style="font-size:0.68rem; color:#7c3aed; margin:8px 0 0 0; display:flex; gap:5px; align-items:center;">
                            <i class="fa-solid fa-circle-info"></i>
                            The selected doctor will receive this clinical record immediately upon submission.
                        </p>
                    </div>
                </div>

                <!-- SUBMIT -->
                <button type="submit" name="create_fish" class="fish-submit-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                    Finalize &amp; Dispatch Clinical Record to Doctor
                </button>

            </div><!-- /fish-body -->
        </form>

        <?php
        if (isset($_POST['create_fish'])) {
            $name_parts = explode(" ", trim($_POST['full_name']), 2);
            $fname = $name_parts[0];
            $lname = isset($name_parts[1]) ? $name_parts[1] : '';
            $nid = trim($_POST['national_id']);
            $gender = $_POST['gender'];
            $phone = $_POST['phone'] ?? '';
            $dob = $_POST['date_of_birth'] ?? null;
            $blood = $_POST['blood_group'] ?? '';
            $parent = $_POST['parent_name'] ?? '';

            // Address — resolve province/district names from DB, cell/village are raw text
            $province_id = $_POST['province'] ?? '';
            $district_id = $_POST['district'] ?? '';
            $sector_id = $_POST['sector'] ?? '';
            $cell_text = trim($_POST['cell'] ?? '');
            $village_text = trim($_POST['village'] ?? '');

            $d_name = '';
            if ($district_id) {
                $r = $conn->query("SELECT name FROM administrative_divisions WHERE id=" . (int) $district_id);
                if ($r)
                    $d_name = $r->fetch_assoc()['name'] ?? '';
            }
            // Build full address string
            $p_name = '';
            if ($province_id) {
                $r2 = $conn->query("SELECT name FROM administrative_divisions WHERE id=" . (int) $province_id);
                if ($r2)
                    $p_name = $r2->fetch_assoc()['name'] ?? '';
            }
            $s_name = '';
            if ($sector_id) {
                $r3 = $conn->query("SELECT name FROM administrative_divisions WHERE id=" . (int) $sector_id);
                if ($r3)
                    $s_name = $r3->fetch_assoc()['name'] ?? '';
            }
            $full_address = implode(', ', array_filter([$village_text, $cell_text, $s_name, $d_name, $p_name]));

            // Vitals concat
            $bp = trim($_POST['bp'] ?? '');
            $temp = trim($_POST['temp'] ?? '');
            $weight = trim($_POST['weight'] ?? '');
            $height = trim($_POST['height'] ?? '');
            $vitals_parts = [];
            if ($bp)
                $vitals_parts[] = "BP: $bp";
            if ($temp)
                $vitals_parts[] = "Temp: $temp";
            if ($weight)
                $vitals_parts[] = "Wt: $weight";
            if ($height)
                $vitals_parts[] = "Ht: $height";
            $vitals = implode(' | ', $vitals_parts);

            $insurance = $_POST['insurance'];
            $is_head = isset($_POST['is_head']) ? 1 : 0;
            $doc_id = (int) $_POST['doctor_id'];
            $complaint = $_POST['chief_complaint'];

            // ── INSERT/UPDATE Patient using ACTUAL DB columns ──
            // Columns: first_name, last_name, date_of_birth, gender, phone,
            //          address, blood_group, national_id, district, cell,
            //          parent_name, is_head_of_family, insurance
            if ($dob === '')
                $dob = null;

            $stmt = $conn->prepare("
                INSERT INTO patients
                    (first_name, last_name, date_of_birth, gender, phone, address, blood_group, national_id, district, cell, parent_name, is_head_of_family, insurance)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    first_name=VALUES(first_name),
                    last_name=VALUES(last_name),
                    date_of_birth=VALUES(date_of_birth),
                    gender=VALUES(gender),
                    phone=VALUES(phone),
                    address=VALUES(address),
                    blood_group=VALUES(blood_group),
                    district=VALUES(district),
                    cell=VALUES(cell),
                    parent_name=VALUES(parent_name),
                    is_head_of_family=VALUES(is_head_of_family),
                    insurance=VALUES(insurance)
            ");
            $stmt->bind_param(
                "sssssssssssss",
                $fname,
                $lname,
                $dob,
                $gender,
                $phone,
                $full_address,
                $blood,
                $nid,
                $d_name,
                $cell_text,
                $parent,
                $is_head,
                $insurance
            );

            if ($stmt->execute()) {
                $p_id = $conn->insert_id ?: $conn->query("SELECT patient_id FROM patients WHERE national_id='$nid'")->fetch_assoc()['patient_id'];

                // ── Create patient_cases (The Fish) ──
                $stmt2 = $conn->prepare("INSERT INTO patient_cases (patient_id, doctor_id, insurance_id, chief_complaint, vitals, head_family_id_match, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
                $stmt2->bind_param("iisssi", $p_id, $doc_id, $insurance, $complaint, $vitals, $is_head);

                if ($stmt2->execute()) {
                    $doc_row = $conn->query("SELECT first_name, last_name FROM doctors WHERE doctor_id=$doc_id")->fetch_assoc();
                    $doc_name = $doc_row ? "Dr. {$doc_row['first_name']} {$doc_row['last_name']}" : "the assigned specialist";
                    echo "
                    <div class='fish-result success' style='margin: 0 2.2rem 2rem;'>
                        <i class='fa-solid fa-circle-check'></i>
                        <div>
                            <span class='fish-result-title'><i class='fa-solid fa-clipboard-check'></i> INTAKE SUCCESSFUL</span>
                            <span class='fish-result-detail'>Clinical record created and dispatched to <strong>$doc_name</strong>. Patient ID: <strong>#$p_id</strong>.</span>
                        </div>
                    </div>";
                } else {
                    echo "<div class='fish-result error' style='margin: 0 2.2rem 2rem;'>
                            <i class='fa-solid fa-triangle-exclamation'></i>
                            <div>
                                <span class='fish-result-title'>CASE RECORD ERROR</span>
                                <span class='fish-result-detail'>" . htmlspecialchars($conn->error) . "</span>
                            </div>
                          </div>";
                }
            } else {
                echo "<div class='fish-result error' style='margin: 0 2.2rem 2rem;'>
                        <i class='fa-solid fa-triangle-exclamation'></i>
                        <div>
                            <span class='fish-result-title'>PATIENT RECORD ERROR</span>
                            <span class='fish-result-detail'>" . htmlspecialchars($conn->error) . "</span>
                        </div>
                      </div>";
            }
        }
        ?>

        <!-- FOOTER SEAL -->
        <div class="fish-footer-seal">
            <span><i class="fa-solid fa-shield-check"></i> SECURE INTAKE — CONFIDENTIAL MEDICAL DOCUMENT</span>
            <span><i class="fa-solid fa-hospital"></i> Hospital Management System — Reception Desk</span>
            <span><i class="fa-solid fa-clock"></i> <span id="footer-time"></span></span>
        </div>

    </div><!-- /fish-document -->
</div><!-- /fish-wrapper -->

<script src="assets/js/intake_helper.js"></script>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>