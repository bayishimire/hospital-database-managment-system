<?php
/**
 * ACCESS CONTROL GATEKEEPER
 * This file provides a standardized way to restrict page access based on user roles.
 */

function restrict_access($allowed_roles = [])
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $current_role = $_SESSION['role'] ?? null;

    // If role is SuperAdmin, they have a bypass for all administrative/clinical areas
    if ($current_role === 'SuperAdmin') {
        return;
    }

    // Check if the current role is in the allowed list
    if (!in_array($current_role, $allowed_roles)) {
        display_unauthorized_page($current_role);
        exit();
    }
}

function display_unauthorized_page($role)
{
    include 'header.php';
    ?>
    <div style="min-height: 70vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">
        <div class="card"
            style="max-width: 500px; text-align: center; padding: 4rem; border-radius: 30px; border: 2px dashed #f87171; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1);">
            <div
                style="background: #fef2f2; width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2.5rem; border: 4px solid #fee2e2;">
                <i class="fa-solid fa-shield-slash" style="font-size: 3.5rem; color: #ef4444;"></i>
            </div>

            <h2 style="font-weight: 800; color: #1e293b; margin-bottom: 1rem; letter-spacing: -0.025em;">Access Restricted
            </h2>
            <p style="color: #64748b; font-size: 1rem; line-height: 1.6; margin-bottom: 2.5rem;">
                Your current identity as <strong style="color:#ef4444;">
                    <?= strtoupper($role ?? 'GUEST') ?>
                </strong>
                does not possess the required clinical or administrative privileges to access this secure zone.
            </p>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <a href="dashboard.php" class="btn btn-primary"
                    style="justify-content: center; padding: 1rem; border-radius: 12px; font-weight: 700;">
                    <i class="fa-solid fa-house-chimney"></i> Return to Safety Hub
                </a>
                <a href="logout.php" style="color: #ef4444; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                    <i class="fa-solid fa-power-off"></i> Switch Identity Profile
                </a>
            </div>
        </div>
    </div>
    <?php
    include 'footer.php';
}
?>