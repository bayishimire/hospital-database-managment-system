<?php 
require_once __DIR__ . '/connection.php';

// Strict RBAC: SuperAdmin only access
if ($_SESSION['role'] !== 'SuperAdmin') {
    header("Location: dashboard.php");
    exit();
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $strong_passwords = isset($_POST['require_strong_passwords']) ? 1 : 0;
    $enforce_2fa = isset($_POST['enforce_2fa']) ? 1 : 0;
    $session_timeout = (int)$_POST['session_timeout'];
    $email_alerts = isset($_POST['email_alerts']) ? 1 : 0;
    $critical_sms = isset($_POST['critical_sms']) ? 1 : 0;
    $maintenance_warnings = isset($_POST['maintenance_warnings']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE system_settings SET 
        require_strong_passwords = ?, 
        enforce_2fa = ?, 
        session_timeout = ?, 
        email_alerts = ?, 
        critical_sms = ?, 
        maintenance_warnings = ? 
        WHERE id = 1");
    $stmt->bind_param("iiiiii", $strong_passwords, $enforce_2fa, $session_timeout, $email_alerts, $critical_sms, $maintenance_warnings);
    
    if ($stmt->execute()) {
        $message = "Configuration saved successfully!";
    } else {
        $message = "Error saving configuration: " . $conn->error;
    }
}

// Fetch current settings
$settings = $conn->query("SELECT * FROM system_settings WHERE id = 1")->fetch_assoc();
?>

<?php include 'header.php'; ?>

<style>
    .settings-container {
        max-width: 900px;
        margin: 2rem auto;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
    }

    .settings-card {
        background: white;
        padding: 2rem;
        border-radius: 24px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }

    .settings-card:hover {
        transform: translateY(-5px);
    }

    .settings-header {
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .settings-title {
        font-size: 1.8rem;
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-main);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 1rem;
        border-radius: 12px;
        transition: background 0.2s;
        cursor: pointer;
    }

    .checkbox-group:hover {
        background: #f8fafc;
    }

    .checkbox-group input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: var(--primary);
    }

    .setting-info {
        flex: 1;
    }

    .setting-label {
        display: block;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-main);
    }

    .setting-desc {
        display: block;
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .input-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        font-family: inherit;
        font-weight: 600;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }

    .input-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }

    .save-btn {
        background: var(--primary);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 800;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px -2px rgba(37, 99, 235, 0.25);
    }

    .save-btn:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.3);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 14px;
        margin-bottom: 2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #10b981;
    }

    .global-integration {
        margin-top: 2rem;
        padding: 1.5rem;
        background: #f8fafc;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
    }

    .integration-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .integration-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
        line-height: 1.5;
    }
</style>

<div class="settings-container">
    <div class="settings-header">
        <div>
            <h1 class="settings-title">System <span style="color: var(--primary);">Settings</span></h1>
            <p style="color: var(--text-muted); font-weight: 600;">Configure hospital-wide application parameters.</p>
        </div>
        <form method="POST" id="settingsForm">
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="settings-grid">
        <!-- Security Preferences -->
        <div class="settings-card">
            <h2 class="section-title">
                <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i> Security Preferences
            </h2>
            
            <label class="checkbox-group">
                <input type="checkbox" name="require_strong_passwords" <?= $settings['require_strong_passwords'] ? 'checked' : '' ?>>
                <div class="setting-info">
                    <span class="setting-label">Require Strong Passwords</span>
                    <span class="setting-desc">Enforce complex passwords for all staff accounts</span>
                </div>
            </label>

            <label class="checkbox-group">
                <input type="checkbox" name="enforce_2fa" <?= $settings['enforce_2fa'] ? 'checked' : '' ?>>
                <div class="setting-info">
                    <span class="setting-label">Two-Factor Authentication (2FA)</span>
                    <span class="setting-desc">Global 2FA requirement for administrative roles</span>
                </div>
            </label>

            <div class="form-group" style="padding: 1rem;">
                <label class="setting-label" style="margin-bottom: 0.5rem;">Session Timeout</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="number" name="session_timeout" class="input-control" style="width: 100px;" value="<?= $settings['session_timeout'] ?>" min="1">
                    <span style="font-weight: 700; color: var(--text-muted); font-size: 0.9rem;">minutes</span>
                </div>
                <span class="setting-desc" style="margin-top: 0.5rem;">Auto-logout inactive users after specified time</span>
            </div>
        </div>

        <!-- System Notifications -->
        <div class="settings-card">
            <h2 class="section-title">
                <i class="fa-solid fa-bell" style="color: var(--accent);"></i> System Notifications
            </h2>

            <label class="checkbox-group">
                <input type="checkbox" name="email_alerts" <?= $settings['email_alerts'] ? 'checked' : '' ?>>
                <div class="setting-info">
                    <span class="setting-label">Email Alerts</span>
                    <span class="setting-desc">Send daily summary reports to administrators</span>
                </div>
            </label>

            <label class="checkbox-group">
                <input type="checkbox" name="critical_sms" <?= $settings['critical_sms'] ? 'checked' : '' ?>>
                <div class="setting-info">
                    <span class="setting-label">Critical Event SMS</span>
                    <span class="setting-desc">SMS notifications for emergency ward admissions</span>
                </div>
            </label>

            <label class="checkbox-group">
                <input type="checkbox" name="maintenance_warnings" <?= $settings['maintenance_warnings'] ? 'checked' : '' ?>>
                <div class="setting-info">
                    <span class="setting-label">Maintenance Warnings</span>
                    <span class="setting-desc">Show scheduled maintenance banners to all staff</span>
                </div>
            </label>
        </div>
    </div>

    <div class="global-integration">
        <h3 class="integration-title">
            <i class="fa-solid fa-globe" style="color: var(--primary);"></i> Global Integration
        </h3>
        <p class="integration-text">
            These settings are applied globally and affect all hospital departments and staff roles. 
            Changes take effect immediately upon saving.
        </p>
    </div>

    <div style="margin-top: 3rem; display: flex; justify-content: flex-end;">
        <button type="submit" class="save-btn">
            <i class="fa-solid fa-floppy-disk"></i> Save Configuration
        </button>
    </div>
    </form>
</div>

<?php include 'footer.php'; ?>
<?php $conn->close(); ?>
