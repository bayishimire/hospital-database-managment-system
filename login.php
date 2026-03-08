<?php
require_once __DIR__ . '/connection.php';

// If already logged in, skip login page
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, username, password, role, related_id, profile_image FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['related_id'] = $user['related_id'];
            $_SESSION['profile_image'] = $user['profile_image'];

            // Role-Based Redirection
            if ($user['role'] == 'SuperAdmin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password. Access denied.";
        }
    } else {
        $error = "User identity not found.";
    }
}

// Include header (this opens HTML tags and container)
include 'header.php';
?>

<div
    style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 1.5rem;">
    <div class="card"
        style="width: 100%; max-width: 380px; padding: 2.25rem; border: none; box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.12); border-radius: 16px; background: rgba(255,255,255,0.9); backdrop-filter: blur(8px);">
        <div style="text-align:center; margin-bottom: 2rem;">
            <div
                style="width: 60px; height: 60px; background: var(--primary); color: white; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 1.25rem; box-shadow: 0 8px 16px -4px rgba(37, 99, 235, 0.3);">
                <i class="fa-solid fa-shield-halved fa-pulse"></i>
            </div>
            <h2
                style="font-weight: 800; color: var(--text-main); font-size: 1.5rem; letter-spacing: -0.025em; margin-bottom: 0.4rem;">
                HMS Secure Entry</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">Please verify identity to
                proceed</p>
        </div>

        <?php if ($error): ?>
            <div
                style="background: #fff1f2; color: #e11d48; padding: 0.75rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #fecdd3; display: flex; align-items: center; gap: 0.6rem;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 1rem;"></i> <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 1.25rem;">
                <label
                    style="display: block; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; padding-left: 0.2rem;">Username</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-circle-user"
                        style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 1rem;"></i>
                    <input type="text" name="username" placeholder="Identity Handle" required autofocus
                        style="width: 100%; padding: 0.8rem 0.8rem 0.8rem 2.8rem; border-radius: 12px; border: 2px solid #f1f5f9; background: #f8fafc; font-size: 0.95rem; transition: all 0.3s ease; outline: none;"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.background='white';"
                        onblur="this.style.borderColor='#f1f5f9'; this.style.background='#f8fafc';">
                </div>
            </div>

            <div style="margin-bottom: 2rem;">
                <label
                    style="display: block; font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; padding-left: 0.2rem;">Secure
                    Access Key</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-lock"
                        style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--primary); font-size: 1rem;"></i>
                    <input type="password" name="password" placeholder="••••••••" required
                        style="width: 100%; padding: 0.8rem 0.8rem 0.8rem 2.8rem; border-radius: 12px; border: 2px solid #f1f5f9; background: #f8fafc; font-size: 0.95rem; transition: all 0.3s ease; outline: none;"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.background='white';"
                        onblur="this.style.borderColor='#f1f5f9'; this.style.background='#f8fafc';">
                </div>
            </div>

            <button type="submit" class="btn btn-primary"
                style="width: 100%; justify-content: center; padding: 0.9rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; box-shadow: 0 8px 12px -3px rgba(37, 99, 235, 0.25);">
                Authenticate Portal <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>

        <div
            style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; text-align: center; font-size: 0.8rem; color: var(--text-muted);">
            <i class="fa-solid fa-shield-check" style="color: var(--accent);"></i> Secure Session Active
        </div>
    </div>
</div>

<?php
// Include footer (this closes the container and HTML tags)
include 'footer.php';
?>