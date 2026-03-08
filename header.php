<?php
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Hospital Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Professional Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Premium Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header>
        <div class="header-content" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 1.5rem;">
                <div
                    style="width: 45px; height: 45px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff;">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <h1 style="font-weight: 700; font-size: 1.5rem; margin: 0;">HDMS <span
                        style="font-weight: 300; opacity: 0.8;">Portal</span></h1>
            </div>

            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div class="user-profile"
                            style="display: flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.1); padding: 0.25rem 0.75rem; border-radius: 30px;">
                            <div
                                style="width: 32px; height: 32px; background: white; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; overflow: hidden; flex-shrink: 0;">
                                <?php
                                $pImg = $_SESSION['profile_image'] ?? '';
                                $pExists = (!empty($pImg) && file_exists(__DIR__ . '/' . $pImg));
                                if ($pExists):
                                    ?>
                                    <img src="<?= htmlspecialchars($pImg) ?>" alt="Profile" class="user-avatar"
                                        style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-shield"></i>
                                <?php endif; ?>
                            </div>
                            <span
                                style="color: white; font-weight: 500; font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        </div>
                        <a href="logout.php"
                            style="color: white; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 500; background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 8px;">
                            <i class="fa-solid fa-power-off"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php"
                        style="color: white; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; font-weight: 500; background: rgba(255,255,255,0.1); padding: 0.5rem 1rem; border-radius: 8px;">
                        <i class="fa-solid fa-key"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php if (isset($_SESSION['user_id']))
        include 'navbar.php'; ?>
    <div class="container--main">