<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>HDMS Premium | Hospital Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Font Awesome for Professional Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Premium Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
        }

        .logo-box {
            width: 42px;
            height: 42px;
            background: var(--accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .header-user-badge {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.08);
            padding: 6px 16px;
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .header-user-badge:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>

<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="brand-logo">
                <div class="logo-box">
                    <i class="fa-solid fa-square-h"></i>
                </div>
                <div>
                    <h1 style="font-size: 1.35rem; margin: 0; line-height: 1;">HDMS <span style="font-weight: 300; opacity: 0.8;">PRO</span></h1>
                    <span style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 2px; opacity: 0.6; font-weight: 800;">Hospital Intelligence</span>
                </div>
            </a>

            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div style="display: flex; align-items: center; gap: 1.5rem;">
                        <div class="header-user-badge">
                            <div style="width: 32px; height: 32px; background: white; color: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; overflow: hidden; border: 2px solid rgba(255,255,255,0.2);">
                                <?php
                                $pImg = $_SESSION['profile_image'] ?? '';
                                $pExists = (!empty($pImg) && file_exists(__DIR__ . '/' . $pImg));
                                if ($pExists):
                                    ?>
                                    <img src="<?= htmlspecialchars($pImg) ?>" alt="Profile" style="width:100%; height:100%; object-fit:cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user-md"></i>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; flex-direction: column;">
                                <span style="color: white; font-weight: 700; font-size: 0.85rem; line-height: 1;"><?= htmlspecialchars($_SESSION['username']) ?></span>
                                <span style="color: rgba(255,255,255,0.6); font-weight: 800; font-size: 0.6rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 2px;"><?= $_SESSION['role'] ?></span>
                            </div>
                        </div>
                        <a href="logout.php" class="btn" style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 8px 15px; font-size: 0.8rem; border: 1px solid rgba(239, 68, 68, 0.2);">
                            <i class="fa-solid fa-power-off"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fa-solid fa-lock"></i> Secure Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php if (isset($_SESSION['user_id'])) include 'navbar.php'; ?>
    <div class="container--main">