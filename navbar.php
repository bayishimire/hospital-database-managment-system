<?php
if (!isset($_SESSION['user_id']))
    return;
$role = $_SESSION['role'];
$rid = $_SESSION['related_id'] ?? 0;

// Notification count for clinic
$clinicBadge = 0;
if ($role == 'SuperAdmin') {
    $clinicBadge = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'Pending'")->fetch_row()[0] ?? 0;
} elseif ($role == 'Doctor') {
    $clinicBadge = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'Pending' AND (doctor_id = $rid OR doctor_id = 0)")->fetch_row()[0] ?? 0;
}
// Notification for Billing (Pending Payments)
$billBadge = 0;
if (in_array($role, ['SuperAdmin', 'Reception', 'Admin', 'Staff'])) {
    $billBadge = $conn->query("SELECT COUNT(*) FROM patient_cases WHERE status = 'BillingPending'")->fetch_row()[0] ?? 0;
}
?>
<nav>
    <!-- Primary Dashboard Link -->
    <?php if ($role == 'SuperAdmin'): ?>
        <a href="admin_dashboard.php"
            class="<?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-rocket"></i> Mission Control
        </a>
    <?php else: ?>
        <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
    <?php endif; ?>

    <!-- Critical Path: Intake & Clinic -->
    <?php if ($role == 'SuperAdmin' || $role == 'Admin' || $role == 'Staff' || $role == 'Reception'): ?>
        <a href="intake_service.php" style="background: rgba(37, 99, 235, 0.05); color: var(--primary); font-weight: 700;">
            <i class="fa-solid fa-clipboard-user"></i> Reception
        </a>
    <?php endif; ?>

    <?php if ($role == 'SuperAdmin' || $role == 'Staff'): ?>
        <a href="patients.php"><i class="fa-solid fa-hospital-user"></i> Patients</a>
    <?php endif; ?>

    <?php if ($role == 'SuperAdmin' || $role == 'Doctor'): ?>
        <a href="doctor_portal.php" style="position: relative;">
            <i class="fa-solid fa-user-doctor"></i> Clinic
            <?php if ($clinicBadge > 0): ?>
                <span
                    style="position: absolute; top: 5px; right: 5px; background: #ef4444; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 0.65rem; display: flex; align-items: center; justify-content: center; font-weight: 900; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    <?= $clinicBadge ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <!-- Management Hub (Dot Menu) - REDUCES CONTENT CLUTTER -->
    <div class="nav-dropdown">
        <a href="#" style="background: transparent; color: var(--text-muted); padding: 0.5rem 0.25rem;">
            <i class="fa-solid fa-ellipsis-vertical" style="font-size: 1.25rem; color: var(--text-main);"></i>
        </a>
        <div class="nav-dropdown-content"
            style="right: 0; left: auto; width: 220px; border-top: 3px solid var(--primary);">
            <div
                style="padding: 0.5rem 1rem; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #f1f5f9; margin-bottom: 0.5rem;">
                System Control
            </div>

            <?php if ($role == 'SuperAdmin'): ?>
                <a href="manage_users.php"><i class="fa-solid fa-user-shield"></i> Access Control</a>
                <a href="manage_staff.php"><i class="fa-solid fa-users-gear"></i> Staff Mgmt</a>
                <a href="manage_departments.php"><i class="fa-solid fa-hotel"></i> Hospital Depts</a>
            <?php endif; ?>

            <?php if (in_array($role, ['SuperAdmin', 'Admin', 'Staff', 'Reception'])): ?>
                <a href="manage_doctors.php"><i class="fa-solid fa-user-doctor"></i> Doctor Profiles</a>
            <?php endif; ?>

            <div
                style="padding: 0.5rem 1rem; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0.5rem 0; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;">
                Operations
            </div>

            <?php if ($role == 'SuperAdmin' || $role == 'Staff' || $role == 'Doctor' || $role == 'Patient'): ?>
                <a href="appointments.php"><i class="fa-solid fa-calendar-check"></i> Appointments</a>
            <?php endif; ?>
            <?php if ($role == 'SuperAdmin' || $role == 'Staff' || $role == 'Doctor'): ?>
                <a href="medicines.php"><i class="fa-solid fa-pills"></i> Pharmacy Desk</a>
                <a href="manage_rooms.php"><i class="fa-solid fa-bed"></i> Rooms & Beds</a>
            <?php endif; ?>

            <a href="billing.php"
                style="border-top: 1px solid #f1f5f9; margin-top: 5px; padding-top: 10px; font-weight: 700; color: var(--primary);">
                <i class="fa-solid fa-money-check-dollar"></i> BILLING CENTER
                <?php if ($billBadge > 0): ?>
                    <span
                        style="background: #ef4444; color: white; padding: 2px 7px; border-radius: 50%; font-size: 0.6rem; font-weight: 900;"><?= $billBadge ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <a href="logout.php" style="margin-left: auto; color: #ef4444;">
        <i class="fa-solid fa-power-off"></i>
    </a>
</nav>