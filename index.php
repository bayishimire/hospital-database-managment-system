<?php require_once __DIR__ . '/connection.php'; ?>
<?php include 'header.php'; ?>

<div class="card" style="text-align: center; padding: 4rem 2rem;">
    <div style="margin-bottom: 2rem;">
        <i class="fa-solid fa-hospital-user"
            style="font-size: 5rem; color: var(--primary); opacity: 0.1; position: absolute; transform: translate(-50%, -50%); left: 50%;"></i>
        <h2 style="font-size: 2.5rem; color: var(--text-main); font-weight: 800; position: relative;">Welcome to Your
            Control Room</h2>
        <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 600px; margin: 1rem auto;">A comprehensive
            hospital management solution designed for real-time operations, staff oversight, and patient care integrity.
        </p>
    </div>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-top: 3rem;">
        <a href="dashboard.php" class="card"
            style="text-decoration: none; transition: transform 0.2s; border: 1px solid var(--border);">
            <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem;"><i
                    class="fa-solid fa-chart-line"></i></div>
            <h3 style="color: var(--text-main);">Live Dashboard</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">View real-time clinical statistics and inventory
                alerts.</p>
        </a>
        <a href="patients.php" class="card"
            style="text-decoration: none; transition: transform 0.2s; border: 1px solid var(--border);">
            <div style="font-size: 2.5rem; color: var(--accent); margin-bottom: 1rem;"><i
                    class="fa-solid fa-hospital-user"></i></div>
            <h3 style="color: var(--text-main);">Patient Registry</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Manage admissions, discharge, and historical
                records.</p>
        </a>
        <a href="doctor_portal.php" class="card"
            style="text-decoration: none; transition: transform 0.2s; border: 1px solid var(--border);">
            <div style="font-size: 2.5rem; color: #8b5cf6; margin-bottom: 1rem;"><i class="fa-solid fa-user-doctor"></i>
            </div>
            <h3 style="color: var(--text-main);">Doctor Portal</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Review today's roster and synchronize appointments.
            </p>
        </a>
        <a href="billing.php" class="card"
            style="text-decoration: none; transition: transform 0.2s; border: 1px solid var(--border);">
            <div style="font-size: 2.5rem; color: #f59e0b; margin-bottom: 1rem;"><i class="fa-solid fa-credit-card"></i>
            </div>
            <h3 style="color: var(--text-main);">Financial Desk</h3>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Generate invoices and monitor payment status.</p>
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>