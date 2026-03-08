<?php require_once __DIR__ . '/connection.php';
if (!in_array($_SESSION['role'], ['SuperAdmin', 'Staff', 'Service'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-clipboard-user"></i> Patient Reception & Clinical Intake</h2>
        <div class="badge badge-pending">
            <i class="fa-solid fa-hospital-user"></i> Registration Desk
        </div>
    </div>

    <!-- Intake Form -->
    <section class="card" style="box-shadow: none; border: 1px solid var(--border);">
        <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-id-card"></i> Create Clinical Record
            ("The Fish")</h3>
        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2rem;">Register patient details, verify
            insurance, and automatically route to an available specialist.</p>

        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <!-- Identity -->
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">FULL
                        NAME (PATIENT)</label>
                    <input type="text" name="full_name" required placeholder="First & Last Name"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">NATIONAL
                        ID / PASSPORT</label>
                    <input type="text" name="national_id" required placeholder="ID Number"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">PARENT
                        / GUARDIAN NAME</label>
                    <input type="text" name="parent_name" placeholder="Name of Mother/Father"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>

                <!-- Geography -->
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">DISTRICT</label>
                    <input type="text" name="district" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">CELL</label>
                    <input type="text" name="cell" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">GENDER</label>
                    <select name="gender" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: #fff;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <!-- Financial/Insurance -->
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">TYPE
                        OF INSURANCE</label>
                    <select name="insurance" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: #fff;">
                        <option value="National Support (RSSI)">National Support (RSSI)</option>
                        <option value="RAMA">RAMA</option>
                        <option value="MMI">MMI</option>
                        <option value="Private (UAP/BRITAM)">Private (UAP/BRITAM)</option>
                        <option value="None">None (Out-of-pocket)</option>
                    </select>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 1.5rem;">
                    <input type="checkbox" name="is_head" id="is_head" style="width: 20px; height: 20px;">
                    <label for="is_head" style="font-size: 0.85rem; font-weight: 600;">Is Head of Family?</label>
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">ASSIGN
                        TO DOCTOR (AUTO-REFERRAL)</label>
                    <select name="doctor_id" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: #fff;">
                        <option value="">-- Select Available Doctor --</option>
                        <?php
                        $docRes = $conn->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors");
                        while ($d = $docRes->fetch_assoc()) {
                            echo "<option value='{$d['doctor_id']}'>Dr. {$d['first_name']} {$d['last_name']} ({$d['specialization']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Clinical Vitals & Complaint -->
                <div style="grid-column: span 2;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">CHIEF
                        COMPLAINT (WHY DID THE PATIENT COME?)</label>
                    <textarea name="chief_complaint" required placeholder="Describe symptoms or reason for visit..."
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); height: 80px;"></textarea>
                </div>
                <div>
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-muted);">VITALS
                        (BP, TEMP, WEIGHT)</label>
                    <input type="text" name="vitals" placeholder="e.g. 120/80, 37°C, 70kg"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>
            </div>

            <button type="submit" name="create_fish" class="btn btn-primary"
                style="margin-top: 2rem; width: 100%; justify-content: center; padding: 1rem;">
                <i class="fa-solid fa-file-medical"></i> Generate Clinical Card & Refer to Doctor
            </button>
        </form>

        <?php
        if (isset($_POST['create_fish'])) {
            $name_parts = explode(" ", $_POST['full_name'], 2);
            $fname = $name_parts[0];
            $lname = isset($name_parts[1]) ? $name_parts[1] : '';
            $nid = $_POST['national_id'];
            $pname = $_POST['parent_name'];
            $district = $_POST['district'];
            $cell = $_POST['cell'];
            $insurance = $_POST['insurance'];
            $is_head = isset($_POST['is_head']) ? 1 : 0;
            $doc_id = (int) $_POST['doctor_id'];
            $gender = $_POST['gender'];
            $complaint = $_POST['chief_complaint'];
            $vitals = $_POST['vitals'];

            // 1. Create/Update Patient Identity
            $stmt = $conn->prepare("INSERT INTO patients (first_name, last_name, national_id, parent_name, district, cell, insurance, is_head_of_family, gender) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE district=?, cell=?, insurance=?, is_head_of_family=?");
            $stmt->bind_param("sssssssissssi", $fname, $lname, $nid, $pname, $district, $cell, $insurance, $is_head, $gender, $district, $cell, $insurance, $is_head);

            if ($stmt->execute()) {
                $p_id = $conn->insert_id ?: $conn->query("SELECT patient_id FROM patients WHERE national_id='$nid'")->fetch_assoc()['patient_id'];

                // 2. Create the "Fish" (Case Record)
                $stmt2 = $conn->prepare("INSERT INTO patient_cases (patient_id, doctor_id, insurance_id, chief_complaint, vitals, head_family_id_match) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("iisssi", $p_id, $doc_id, $insurance, $complaint, $vitals, $is_head);

                if ($stmt2->execute()) {
                    echo "<div class='badge badge-success' style='margin-top:1.5rem; width:100%; padding:1rem; font-size:1rem;'>
                            <i class='fa-solid fa-check-circle'></i> RECEPTION SUCCESS: Clinical Card generated and sent to Specialist.
                          </div>";
                }
            } else {
                echo "<div class='badge badge-danger' style='margin-top:1.5rem; width:100%; padding:1rem;'>Error: " . $conn->error . "</div>";
            }
        }
        ?>
    </section>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>