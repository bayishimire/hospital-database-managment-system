<?php
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/access_control.php';
restrict_access(['Admin', 'Staff', 'Reception']);

// ─── CRUD LOGIC ─────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $d_id = (int) $_GET['delete'];

    // Safety check - optional: don't delete if they have active appointments etc.
    if ($conn->query("DELETE FROM doctors WHERE doctor_id = $d_id")) {
        header("Location: manage_doctors.php?msg=deleted");
    } else {
        die("Error deleting doctor: " . $conn->error);
    }
    exit();
}

if (isset($_POST['add_doctor'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $spec = $_POST['specialization'];
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $dept = (int) $_POST['department_id'];

    $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, specialization, phone, email, department_id) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssi", $fname, $lname, $spec, $phone, $email, $dept);
        if ($stmt->execute()) {
            header("Location: manage_doctors.php?msg=added");
        } else {
            die("Error inserting doctor: " . $stmt->error);
        }
    } else {
        die("Error preparing statement: " . $conn->error);
    }
    exit();
}

if (isset($_POST['update_doctor'])) {
    $did = (int) $_POST['doctor_id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $spec = $_POST['specialization'];
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $dept = (int) $_POST['department_id'];

    $stmt = $conn->prepare("UPDATE doctors SET first_name=?, last_name=?, specialization=?, phone=?, email=?, department_id=? WHERE doctor_id=?");
    if ($stmt) {
        $stmt->bind_param("sssssii", $fname, $lname, $spec, $phone, $email, $dept, $did);
        if ($stmt->execute()) {
            header("Location: manage_doctors.php?msg=updated");
        } else {
            die("Error updating doctor: " . $stmt->error);
        }
    } else {
        die("Error preparing statement: " . $conn->error);
    }
    exit();
}
?>
<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-user-doctor"></i> Clinical Staff (Doctors) Management</h2>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'added'): ?>
            <div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'>
                <i class='fa-solid fa-check'></i> DOCTOR PROFILE ADDED SUCCESSFULLY
            </div>
        <?php elseif ($_GET['msg'] == 'updated'): ?>
            <div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'>
                <i class='fa-solid fa-pen-to-square'></i> DOCTOR PROFILE UPDATED
            </div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'>
                <i class='fa-solid fa-trash'></i> DOCTOR PROFILE DELETED
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">

        <!-- Form Section -->
        <?php
        $editDoc = null;
        if (isset($_GET['edit_id'])) {
            $eid = (int) $_GET['edit_id'];
            $editDoc = $conn->query("SELECT * FROM doctors WHERE doctor_id = $eid")->fetch_assoc();
        }
        ?>
        <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
            <h3 class="card-title" style="font-size: 1.1rem;">
                <i class="fa-solid <?= $editDoc ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i>
                <?= $editDoc ? 'Modify Clinical Profile' : 'Register New Doctor' ?>
            </h3>
            <form method="POST">
                <?php if ($editDoc): ?>
                    <input type="hidden" name="doctor_id" value="<?= $editDoc['doctor_id'] ?>">
                <?php endif; ?>

                <div style="margin-bottom: 1rem; display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">First
                            Name</label>
                        <input type="text" name="first_name" value="<?= $editDoc['first_name'] ?? '' ?>" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Last
                            Name</label>
                        <input type="text" name="last_name" value="<?= $editDoc['last_name'] ?? '' ?>" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Specialization</label>
                    <input type="text" name="specialization" value="<?= $editDoc['specialization'] ?? '' ?>" required
                        placeholder="e.g. Cardiologist, Surgeon, General"
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Department</label>
                    <select name="department_id" required
                        style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                        <option value="">-- Select Department --</option>
                        <?php
                        $deptRes = $conn->query("SELECT * FROM departments");
                        while ($d = $deptRes->fetch_assoc()) {
                            $sel = ($editDoc && $editDoc['department_id'] == $d['department_id']) ? 'selected' : '';
                            echo "<option value='{$d['department_id']}' $sel>{$d['name_of_depart']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="margin-bottom: 1rem; display: flex; gap: 10px;">
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Phone
                            <small>(Optional)</small></label>
                        <input type="text" name="phone" value="<?= $editDoc['phone'] ?? '' ?>"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Email
                            <small>(Optional)</small></label>
                        <input type="email" name="email" value="<?= $editDoc['email'] ?? '' ?>"
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                </div>

                <?php if ($editDoc): ?>
                    <button type="submit" name="update_doctor" class="btn btn-primary"
                        style="width: 100%; background: var(--text-main);">
                        <i class="fa-solid fa-save"></i> Apply Changes
                    </button>
                    <a href="manage_doctors.php" class="btn"
                        style="width: 100%; justify-content: center; margin-top: 0.5rem; background: #f1f5f9; color: var(--text-muted); font-size: 0.8rem;">Cancel
                        Edit</a>
                <?php else: ?>
                    <button type="submit" name="add_doctor" class="btn btn-primary" style="width: 100%;">
                        <i class="fa-solid fa-stethoscope"></i> Register Clinical Profile
                    </button>
                <?php endif; ?>
            </form>
        </section>

        <!-- List Section -->
        <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-list"></i> Doctor Directory</h3>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Specialization</th>
                            <th>Department</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT d.*, dp.name_of_depart FROM doctors d LEFT JOIN departments dp ON d.department_id = dp.department_id ORDER BY d.first_name ASC";
                        $res = $conn->query($sql);
                        if ($res && $res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; color: #0f172a;">Dr.
                                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            <?= htmlspecialchars($row['email']) ?>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-pending">
                                            <?= htmlspecialchars($row['specialization']) ?>
                                        </span></td>
                                    <td>
                                        <?= htmlspecialchars($row['name_of_depart'] ?? 'None') ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <a href="manage_doctors.php?edit_id=<?= $row['doctor_id'] ?>" class="btn"
                                            style="padding: 0.4rem 0.6rem; font-size: 0.8rem; background: #e0f2fe; color: #0284c7; border-radius: 8px;">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <a href="manage_doctors.php?delete=<?= $row['doctor_id'] ?>" class="btn"
                                            onclick="return confirm('Are you sure you want to completely remove this Doctor Profile?')"
                                            style="padding: 0.4rem 0.6rem; font-size: 0.8rem; background: #fee2e2; color: #ef4444; border-radius: 8px;">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding: 2rem; color: #94a3b8;'>No doctors registered yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>

<?php include 'footer.php'; ?>