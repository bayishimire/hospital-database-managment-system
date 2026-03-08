<?php require_once __DIR__ . '/connection.php'; ?>
<?php
// RBAC: SuperAdmin only for user management
if ($_SESSION['role'] != 'SuperAdmin') {
    header("Location: dashboard.php");
    exit();
}
$isSuperAdmin = true; // Since we passed the above check
?>
<?php include 'header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 class="card-title"><i class="fa-solid fa-user-shield"></i> System Privilege & Access Management</h2>
        <div class="badge badge-success">
            <i class="fa-solid fa-check-circle"></i> Authenticated Admin Session
        </div>
    </div>

    <p style="color: var(--text-muted); margin-bottom: 2.5rem; max-width: 800px;">
        As a Super Administrator, you are responsible for provisioning identity access and defining strict privilege
        boundaries for hospital personnel and patients.
    </p>

    <!-- Provisioning Controls -->
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2.5rem;">

        <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
            <h3 class="card-title" style="font-size: 1.1rem;"><i class="fa-solid fa-id-card-clip"></i> Provision Access
            </h3>
            <form method="POST" enctype="multipart/form-data">
                <div style="margin-bottom: 1.25rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Identity
                        Handle (Username)</label>
                    <input type="text" name="username" placeholder="e.g. system_handle" required
                        style="width: 100%; padding: 0.875rem; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div style="margin-bottom: 1.25rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Secure
                        Access Key</label>
                    <input type="password" name="password" placeholder="••••••••" required
                        style="width: 100%; padding: 0.875rem; border-radius: 10px; border: 1px solid var(--border);">
                </div>
                <div style="margin-bottom: 2rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Privilege
                        Designation</label>
                    <select name="role" id="roleSelector" required onchange="toggleDoctorLink(this.value)"
                        style="width: 100%; padding: 0.875rem; border-radius: 10px; border: 1px solid var(--border); background: white;">
                        <option value="SuperAdmin">SuperAdmin (Full Control Plane)</option>
                        <option value="Doctor">Doctor (Clinical & Health Records)</option>
                        <option value="Service">Service Personnel (Reception & Intake)</option>
                        <option value="Staff">General Staff (Operational Logistics)</option>
                        <option value="Patient">Patient Identity (Limited Access)</option>
                    </select>
                </div>
                <!-- Linked Doctor Profile (Conditional) -->
                <div id="doctorLinkField" style="margin-bottom: 2rem; display: none;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Link
                        to Specialist Profile</label>
                    <select name="related_id"
                        style="width: 100%; padding: 0.875rem; border-radius: 10px; border: 1px solid var(--border); background: white;">
                        <option value="0">-- Select Doctor Profile --</option>
                        <?php
                        $docRes = $conn->query("SELECT doctor_id, first_name, last_name, specialization FROM doctors");
                        while ($d = $docRes->fetch_assoc()) {
                            echo "<option value='{$d['doctor_id']}'>Dr. {$d['first_name']} {$d['last_name']} ({$d['specialization']})</option>";
                        }
                        ?>
                    </select>
                </div>
                <script>
                    function toggleDoctorLink(role) {
                        document.getElementById('doctorLinkField').style.display = (role === 'Doctor') ? 'block' : 'none';
                    }
                </script>
                <div style="margin-bottom: 2rem;">
                    <label
                        style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem;">Profile
                        Photo <span style="font-weight:400;">(Optional)</span></label>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div id="avatarPreviewWrap"
                            style="width: 56px; height: 56px; border-radius: 50%; background: #f1f5f9; border: 2px dashed var(--border); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;">
                            <i class="fa-solid fa-user" id="avatarPreviewIcon"
                                style="color: var(--text-muted); font-size: 1.5rem;"></i>
                            <img id="avatarPreviewImg" src="" alt=""
                                style="display:none; width:100%; height:100%; object-fit:cover;">
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="profile_image" id="profileImageInput"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                style="width: 100%; font-size: 0.85rem; color: var(--text-muted);">
                            <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.35rem;">JPG, PNG, GIF
                                or WEBP &bull; Max 2 MB</p>
                        </div>
                    </div>
                </div>
                <script>
                    document.getElementById('profileImageInput').addEventListener('change', function () {
                        const file = this.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                document.getElementById('avatarPreviewImg').src = e.target.result;
                                document.getElementById('avatarPreviewImg').style.display = 'block';
                                document.getElementById('avatarPreviewIcon').style.display = 'none';
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                </script>
                <button type="submit" name="add_user" class="btn btn-primary"
                    style="width: 100%; justify-content: center; padding: 1rem;">
                    <i class="fa-solid fa-lock-open"></i> Authorize Identity
                </button>
            </form>

            <?php
            if (isset($_POST['add_user'])) {
                $u = $_POST['username'];
                $p = $_POST['password'];
                $r = $_POST['role'];
                $rid = isset($_POST['related_id']) ? (int) $_POST['related_id'] : 0;

                // Handle optional profile image upload
                $profileImagePath = null;
                if (!empty($_FILES['profile_image']['name'])) {
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $ftype = mime_content_type($_FILES['profile_image']['tmp_name']);
                    $fsize = $_FILES['profile_image']['size'];
                    if (!in_array($ftype, $allowed)) {
                        echo "<div style='margin-top:1rem;padding:1rem;background:#fff1f2;color:#be123c;border-radius:10px;border:1px solid #fecdd3;font-size:0.875rem;'><i class='fa-solid fa-triangle-exclamation'></i> Invalid file type. Only JPG, PNG, GIF, WEBP allowed.</div>";
                    } elseif ($fsize > 2 * 1024 * 1024) {
                        echo "<div style='margin-top:1rem;padding:1rem;background:#fff1f2;color:#be123c;border-radius:10px;border:1px solid #fecdd3;font-size:0.875rem;'><i class='fa-solid fa-triangle-exclamation'></i> File too large. Maximum size is 2 MB.</div>";
                    } else {
                        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                        $filename = 'avatar_' . uniqid() . '.' . strtolower($ext);
                        $dest = __DIR__ . '/assets/uploads/avatars/' . $filename;
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $dest)) {
                            $profileImagePath = 'assets/uploads/avatars/' . $filename;
                        }
                    }
                }

                $stmt = $conn->prepare("INSERT INTO users (username, password, role, related_id, profile_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $u, $p, $r, $rid, $profileImagePath);
                if ($stmt->execute()) {
                    echo "<div style='margin-top: 1.5rem; padding: 1rem; background: #ecfdf5; color: #065f46; border-radius: 10px; font-size: 0.9rem; border: 1px solid #a7f3d0;'>
                            <i class='fa-solid fa-circle-check'></i> Access provisioned for identity '$u' (Linked ID: $rid).
                          </div>";
                }
            }

            // Handle photo update for existing user
            if (isset($_POST['update_photo']) && isset($_POST['target_user_id'])) {
                $tid = (int) $_POST['target_user_id'];
                if (!empty($_FILES['update_image']['name'])) {
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $ftype = mime_content_type($_FILES['update_image']['tmp_name']);
                    $fsize = $_FILES['update_image']['size'];
                    if (in_array($ftype, $allowed) && $fsize <= 2 * 1024 * 1024) {
                        $ext = pathinfo($_FILES['update_image']['name'], PATHINFO_EXTENSION);
                        $filename = 'avatar_' . $tid . '_' . uniqid() . '.' . strtolower($ext);
                        $dest = __DIR__ . '/assets/uploads/avatars/' . $filename;
                        if (move_uploaded_file($_FILES['update_image']['tmp_name'], $dest)) {
                            $newPath = 'assets/uploads/avatars/' . $filename;
                            // Delete old image from disk
                            $oldRes = $conn->query("SELECT profile_image FROM users WHERE user_id=$tid");
                            if ($old = $oldRes->fetch_assoc()) {
                                if (!empty($old['profile_image']) && file_exists(__DIR__ . '/' . $old['profile_image'])) {
                                    unlink(__DIR__ . '/' . $old['profile_image']);
                                }
                            }
                            $conn->query("UPDATE users SET profile_image='$newPath' WHERE user_id=$tid");
                            // Update session if it's the current user
                            if ($tid == $_SESSION['user_id']) {
                                $_SESSION['profile_image'] = $newPath;
                            }
                            echo "<script>window.location='manage_users.php';</script>";
                        }
                    }
                }
            }
            ?>
        </section>

        <!-- Access Roster -->
        <section class="card" style="box-shadow: none; border: 1px solid var(--border); margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 class="card-title" style="font-size: 1.1rem; margin-bottom: 0;"><i class="fa-solid fa-id-badge"></i>
                    Active Identity Roster</h3>
                <span style="font-size: 0.75rem; color: var(--text-muted);">Current Database Size:
                    <?= $conn->query("SELECT count(*) FROM users")->fetch_row()[0] ?></span>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Avatar</th>
                            <th>Identity Identification</th>
                            <th>Functional Role</th>
                            <th>Provisioned Date</th>
                            <th>Administration</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
                        while ($row = $res->fetch_assoc()) {
                            $roleClass = 'badge-pending';
                            if ($row['role'] == 'SuperAdmin')
                                $roleClass = 'badge-danger';
                            if ($row['role'] == 'Doctor')
                                $roleClass = 'badge-success';

                            $avatarPath = $row['profile_image'] ?? null;
                            $hasAvatar = !empty($avatarPath) && file_exists(__DIR__ . '/' . $avatarPath);

                            echo "<tr>";
                            // Avatar column
                            echo "<td>
                                    <div style='width:42px;height:42px;border-radius:50%;overflow:hidden;background:#f1f5f9;border:2px solid var(--border);display:flex;align-items:center;justify-content:center;flex-shrink:0;'>";
                            if ($hasAvatar) {
                                echo "<img src='" . htmlspecialchars($avatarPath) . "' alt='" . htmlspecialchars($row['username']) . "' style='width:100%;height:100%;object-fit:cover;'>";
                            } else {
                                echo "<i class='fa-solid fa-fingerprint' style='color:var(--text-muted);font-size:1.1rem;'></i>";
                            }
                            echo "</div></td>";
                            // Username column
                            echo "<td><strong>" . htmlspecialchars($row['username']) . "</strong></td>";
                            echo "<td><span class='badge $roleClass'>" . strtoupper($row['role']) . "</span></td>";
                            echo "<td>" . date('Y-m-d', strtotime($row['created_at'])) . "</td>";
                            echo "<td style='white-space:nowrap;'>";
                            // Inline photo upload form
                            echo "
                            <details style='display:inline; cursor:pointer;' title='Upload / Change Photo'>
                                <summary style='list-style:none; display:inline;'>
                                    <span class='btn' style='padding:0.3rem 0.6rem; font-size:0.8rem; background:#f1f5f9; color:var(--text-muted); border-radius:8px;' title='Upload Photo'><i class='fa-solid fa-camera'></i></span>
                                </summary>
                                <div style='position:absolute; background:white; border:1px solid var(--border); border-radius:12px; padding:1rem; margin-top:0.5rem; box-shadow:var(--shadow); z-index:50; width:220px;'>
                                    <form method='POST' enctype='multipart/form-data'>
                                        <input type='hidden' name='target_user_id' value='" . $row['user_id'] . "'>
                                        <p style='font-size:0.8rem;font-weight:600;color:var(--text-muted);margin-bottom:0.5rem;'>Upload photo for <strong>" . htmlspecialchars($row['username']) . "</strong></p>
                                        <input type='file' name='update_image' accept='image/jpeg,image/png,image/gif,image/webp' style='font-size:0.8rem;width:100%;margin-bottom:0.75rem;'>
                                        <button type='submit' name='update_photo' class='btn btn-primary' style='width:100%;justify-content:center;padding:0.5rem;font-size:0.8rem;'><i class='fa-solid fa-upload'></i> Save Photo</button>
                                    </form>
                                </div>
                            </details>";
                            if ($row['user_id'] != $_SESSION['user_id']) {
                                echo "&nbsp;<a href='?revoke=" . $row['user_id'] . "' class='btn' style='color:#ef4444;padding:0.3rem 0.6rem;font-size:0.8rem;background:#fff1f2;border-radius:8px;' title='Revoke Access'><i class='fa-solid fa-user-xmark'></i></a>";
                            } else {
                                echo "&nbsp;<small style='color:var(--text-muted);font-size:0.75rem;'>Your Session</small>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Privilege Guidance Section -->
    <div class="card" style="margin-top: 3rem; background: #f8fafc; border: 1px solid var(--border); box-shadow: none;">
        <h4 style="margin-bottom: 1rem;"><i class="fa-solid fa-circle-info"></i> Administration Protocol</h4>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;">
            <div style="font-size: 0.875rem;">
                <h5 style="color: var(--primary); margin-bottom: 0.5rem;">SuperAdmin Privilege</h5>
                <p style="color: var(--text-muted);">Grants complete oversight of the system control room, including
                    financial ledgers and full personnel management.</p>
            </div>
            <div style="font-size: 0.875rem;">
                <h5 style="color: var(--accent); margin-bottom: 0.5rem;">Doctor Privilege</h5>
                <p style="color: var(--text-muted);">Provides clinical authorization for medical record entry,
                    prescription management, and diagnostic reviews.</p>
            </div>
            <div style="font-size: 0.875rem;">
                <h5 style="color: #6366f1; margin-bottom: 0.5rem;">Service Personnel</h5>
                <p style="color: var(--text-muted);">Handles the initial **Clinical Intake (The Fish)**, verifies
                    insurance, captures geographic data, and refers patients to specialists.</p>
            </div>
            <div style="font-size: 0.875rem;">
                <h5 style="color: #64748b; margin-bottom: 0.5rem;">General Staff</h5>
                <p style="color: var(--text-muted);">Manages facility logistics, room assignments, and general
                    operational support across the hospital.</p>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($_GET['revoke'])) {
    $id = (int) $_GET['revoke'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM users WHERE user_id = $id");
        echo "<script>window.location='manage_users.php';</script>";
    }
}
?>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>