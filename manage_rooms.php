<?php
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/access_control.php';
restrict_access(['Staff', 'Doctor', 'Service']);

// ─── CRUD LOGIC HANDLERS ───────────────────────────────────────────────────

// 1. DELETE ROOM (SuperAdmin Only)
if (isset($_GET['delete_room']) && $_SESSION['role'] == 'SuperAdmin') {
    $r_id = (int) $_GET['delete_room'];
    // Safety check: Cannot delete room if it has active occupants
    $check = $conn->query("SELECT COUNT(*) FROM patient_rooms WHERE room_id = $r_id AND discharge_date IS NULL")->fetch_row()[0];
    if ($check == 0) {
        if ($conn->query("DELETE FROM rooms WHERE room_id = $r_id")) {
            header("Location: manage_rooms.php?msg=deleted");
        } else {
            die("Error deleting room: " . $conn->error);
        }
    } else {
        header("Location: manage_rooms.php?msg=error_occupied");
    }
    exit();
}

// 2. UPDATE ROOM (SuperAdmin Only)
if (isset($_POST['update_room']) && $_SESSION['role'] == 'SuperAdmin') {
    $rid = (int) $_POST['room_id'];
    $num = $_POST['room_number'];
    $typ = $_POST['type'];
    $cap = (int) $_POST['capacity'];

    $stmt = $conn->prepare("UPDATE rooms SET room_number = ?, type = ?, capacity = ? WHERE room_id = ?");
    $stmt->bind_param("ssii", $num, $typ, $cap, $rid);
    if ($stmt->execute()) {
        header("Location: manage_rooms.php?msg=updated");
    } else {
        die("Error updating room: " . $stmt->error);
    }
    exit();
}

// 3. ADD ROOM (SuperAdmin Only)
if (isset($_POST['add_room']) && $_SESSION['role'] == 'SuperAdmin') {
    $num = $_POST['room_number'];
    $typ = $_POST['type'];
    $cap = (int) $_POST['capacity'];
    $status = 'Available';

    $stmt = $conn->prepare("INSERT INTO rooms (room_number, type, capacity, availability_status) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssis", $num, $typ, $cap, $status);
        if ($stmt->execute()) {
            header("Location: manage_rooms.php?msg=added");
        } else {
            die("Error inserting room: " . $stmt->error);
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
        <h2 class="card-title"><i class="fa-solid fa-bed-pulse"></i> Ward & Room Management</h2>
        <div class="badge badge-success">
            Hospital Bed Capacity
        </div>
    </div>

    <!-- Feedback Messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'added'): ?>
            <div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i
                    class='fa-solid fa-circle-check'></i> NEW ROOM CREATED SUCCESSFULLY</div>
        <?php elseif ($_GET['msg'] == 'deleted'): ?>
            <div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i
                    class='fa-solid fa-trash-can'></i> ROOM PERMANENTLY REMOVED</div>
        <?php elseif ($_GET['msg'] == 'updated'): ?>
            <div class='badge badge-success'
                style='width:100%; padding:1rem; margin-bottom:1.5rem; background:#eff6ff; color:#2563eb; border:1px solid #dbeafe;'>
                <i class='fa-solid fa-pen-to-square'></i> ROOM DETAILS UPDATED
            </div>
        <?php elseif ($_GET['msg'] == 'error_occupied'): ?>
            <div class='badge badge-danger' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i
                    class='fa-solid fa-triangle-exclamation'></i> ERROR: CANNOT DELETE OCCUPIED ROOM</div>
        <?php elseif ($_GET['msg'] == 'admitted'): ?>
            <div class='badge badge-success' style='width:100%; padding:1rem; margin-bottom:1.5rem;'><i
                    class='fa-solid fa-check'></i> PATIENT ADMITTED TO WARD</div>
        <?php endif; ?>
    <?php endif; ?>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; align-items: start;">

        <!-- Registration/Edit Form Section -->
        <?php if ($_SESSION['role'] == 'SuperAdmin'): ?>
            <?php
            $editRoom = null;
            if (isset($_GET['edit_id'])) {
                $editRoom = $conn->query("SELECT * FROM rooms WHERE room_id = " . (int) $_GET['edit_id'])->fetch_assoc();
            }
            ?>
            <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
                <h3 class="card-title" style="font-size: 1.1rem;">
                    <i class="fa-solid <?= $editRoom ? 'fa-pen-to-square' : 'fa-plus-circle' ?>"></i>
                    <?= $editRoom ? 'Modify Room ' . $editRoom['room_number'] : 'Register Hospital Room' ?>
                </h3>
                <form method="POST">
                    <?php if ($editRoom): ?>
                        <input type="hidden" name="room_id" value="<?= $editRoom['room_id'] ?>">
                    <?php endif; ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Room
                            Number</label>
                        <input type="text" name="room_number" value="<?= $editRoom['room_number'] ?? '' ?>" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Ward
                            Category</label>
                        <select name="type" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <option value="General" <?= ($editRoom && $editRoom['type'] == 'General') ? 'selected' : '' ?>>
                                General Ward</option>
                            <option value="ICU" <?= ($editRoom && $editRoom['type'] == 'ICU') ? 'selected' : '' ?>>Intensive
                                Care Unit (ICU)</option>
                            <option value="Private" <?= ($editRoom && $editRoom['type'] == 'Private') ? 'selected' : '' ?>>
                                Private Premium Room</option>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Bed
                            Capacity</label>
                        <input type="number" name="capacity" value="<?= $editRoom['capacity'] ?? '1' ?>" min="1" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <?php if ($editRoom): ?>
                        <button type="submit" name="update_room" class="btn btn-primary"
                            style="width: 100%; background: var(--text-main);">
                            <i class="fa-solid fa-save"></i> Apply Changes
                        </button>
                        <a href="manage_rooms.php" class="btn"
                            style="width: 100%; justify-content: center; margin-top: 0.5rem; background: #f1f5f9; color: var(--text-muted); font-size: 0.8rem;">Cancel
                            Edit</a>
                    <?php else: ?>
                        <button type="submit" name="add_room" class="btn btn-primary" style="width: 100%;">
                            <i class="fa-solid fa-door-open"></i> Create New Room
                        </button>
                    <?php endif; ?>
                </form>
            </section>
        <?php endif; ?>

        <!-- Admission Assignment Section -->
        <?php
        $pID = $_GET['patient_id'] ?? null;
        $patient = null;
        if ($pID) {
            $res = $conn->query("SELECT * FROM patients WHERE patient_id = " . (int) $pID);
            $patient = $res->fetch_assoc();
        }
        ?>
        <?php if ($patient): ?>
            <section class="card"
                style="box-shadow: none; border: 2px solid var(--accent); background: #f0fff4; margin-bottom: 0;">
                <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-user-plus"></i> Admit:
                    <?= htmlspecialchars($patient['first_name'] . " " . $patient['last_name']) ?>
                </h3>
                <form method="POST">
                    <input type="hidden" name="patient_id" value="<?= $patient['patient_id'] ?>">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Select
                            Bed</label>
                        <select name="room_id" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: white;">
                            <option value="">-- Choose Available Room --</option>
                            <?php
                            $roomRes = $conn->query("SELECT * FROM rooms WHERE availability_status = 'Available'");
                            while ($r = $roomRes->fetch_assoc()) {
                                echo "<option value='{$r['room_id']}'>Room {$r['room_number']} ({$r['type']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 0.4rem;">Admission
                            Date</label>
                        <input type="datetime-local" name="adm_date" value="<?= date('Y-m-d\TH:i') ?>" required
                            style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border);">
                    </div>
                    <button type="submit" name="confirm_admission" class="btn"
                        style="width: 100%; background: var(--accent); color: white;">
                        <i class="fa-solid fa-check-circle"></i> Complete Admission
                    </button>
                </form>
                <?php
                if (isset($_POST['confirm_admission'])) {
                    $pid = (int) $_POST['patient_id'];
                    $rid = (int) $_POST['room_id'];
                    $date = $_POST['adm_date'];
                    $stmt = $conn->prepare("INSERT INTO patient_rooms (patient_id, room_id, admission_date) VALUES (?, ?, ?)");
                    $stmt->bind_param("iis", $pid, $rid, $date);
                    if ($stmt->execute()) {
                        $conn->query("UPDATE rooms SET availability_status = 'Occupied' WHERE room_id = $rid");
                        echo "<script>window.location='manage_rooms.php?msg=admitted';</script>";
                    }
                }
                ?>
            </section>
        <?php else: ?>
            <div
                style="border: 2px dashed var(--border); border-radius: 12px; padding: 3.5rem 2rem; text-align: center; color: var(--text-muted);">
                <i class="fa-solid fa-bed" style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.2;"></i>
                <p style="font-size: 0.9rem; font-weight: 500;">Select a patient to proceed with ward admission assignment.
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Room Inventory Table -->
    <div style="margin-top: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="card-title" style="margin-bottom: 0;"><i class="fa-solid fa-list-ul"></i> Facility Room Status
            </h3>
            <div style="display: flex; gap: 0.5rem;">
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Available</span>
                <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Occupied</span>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Room No.</th>
                        <th>Category</th>
                        <th>Capacity</th>
                        <th>Occupancy</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT r.*, 
                            (SELECT COUNT(*) FROM patient_rooms pr WHERE pr.room_id = r.room_id AND pr.discharge_date IS NULL) as current_occupancy 
                            FROM rooms r ORDER BY r.room_number ASC";
                    $res = $conn->query($sql);
                    if ($res) {
                        while ($row = $res->fetch_assoc()) {
                            $isFull = ($row['availability_status'] == 'Occupied');
                            $statusClass = $isFull ? 'badge-danger' : 'badge-success';
                            $categoryIcon = ($row['type'] == 'ICU') ? 'fa-microchip' : (($row['type'] == 'Private') ? 'fa-crown' : 'fa-users');
                            $role = $_SESSION['role'];
                            ?>
                            <tr>
                                <td><strong>Room <?= htmlspecialchars($row['room_number']) ?></strong></td>
                                <td><i class='fa-solid <?= $categoryIcon ?>'
                                        style='font-size: 0.8rem; margin-right: 5px; opacity: 0.6;'></i><?= htmlspecialchars($row['type']) ?>
                                </td>
                                <td><?= htmlspecialchars($row['capacity']) ?> Beds</td>
                                <td><?= $row['current_occupancy'] ?> Patients</td>
                                <td><span class='badge <?= $statusClass ?>'><?= $row['availability_status'] ?></span></td>
                                <td style="text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                                        <!-- VIEW ACTION (Everyone) -->
                                        <a href="?view_id=<?= $row['room_id'] ?>" class="btn"
                                            style="padding: 5px 8px; background: #eff6ff; color: var(--primary);"
                                            title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <?php if ($role == 'SuperAdmin'): ?>
                                            <!-- EDIT ACTION (Admin Only) -->
                                            <a href="?edit_id=<?= $row['room_id'] ?>" class="btn"
                                                style="padding: 5px 8px; background: #f8fafc; color: var(--text-main); border: 1px solid var(--border);"
                                                title="Edit Room">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <!-- DELETE ACTION (Admin Only) -->
                                            <a href="?delete_room=<?= $row['room_id'] ?>" class="btn"
                                                style="padding: 5px 8px; background: #fff1f2; color: #e11d48;"
                                                onclick="return confirm('Erase this room from database?');" title="Delete Room">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Modal (Dynamic content if view_id is set) -->
<?php if (isset($_GET['view_id'])): ?>
    <?php
    $viewId = (int) $_GET['view_id'];
    $room = $conn->query("SELECT * FROM rooms WHERE room_id = $viewId")->fetch_assoc();
    $occupants = $conn->query("SELECT p.first_name, p.last_name, pr.admission_date FROM patient_rooms pr JOIN patients p ON pr.patient_id = p.patient_id WHERE pr.room_id = $viewId AND pr.discharge_date IS NULL");
    ?>
    <div
        style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 500px; padding: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0;"><i class="fa-solid fa-circle-info"></i> Room <?= $room['room_number'] ?> Insights
                </h3>
                <a href="manage_rooms.php"
                    style="color: var(--text-muted); font-size: 1.5rem; text-decoration: none;">&times;</a>
            </div>
            <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 12px;">
                    <small
                        style="color: var(--text-muted); text-transform: uppercase; font-weight: 700; font-size: 0.65rem;">Category
                        & Capacity</small>
                    <div style="font-weight: 600; margin-top: 5px;"><?= $room['type'] ?> Ward | <?= $room['capacity'] ?>
                        Beds</div>
                </div>
                <div>
                    <small
                        style="color: var(--text-muted); text-transform: uppercase; font-weight: 700; font-size: 0.65rem;">Active
                        Occupants</small>
                    <ul style="list-style: none; padding: 0; margin-top: 10px;">
                        <?php if ($occupants->num_rows > 0): ?>
                            <?php while ($occ = $occupants->fetch_assoc()): ?>
                                <li
                                    style="display: flex; align-items: center; gap: 10px; padding: 0.75rem; background: #fff; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 0.5rem; font-size: 0.9rem;">
                                    <i class="fa-solid fa-user-injured" style="color: var(--primary);"></i>
                                    <strong><?= $occ['first_name'] . ' ' . $occ['last_name'] ?></strong>
                                    <small
                                        style="margin-left: auto; color: var(--text-muted);"><?= date('M d', strtotime($occ['admission_date'])) ?></small>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li style="color: var(--text-muted); font-style: italic; font-size: 0.85rem;">No patients currently
                                in this room.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <a href="manage_rooms.php" class="btn btn-primary" style="width: 100%; justify-content: center;">Close
                Inspection</a>
        </div>
    </div>
<?php endif; ?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>