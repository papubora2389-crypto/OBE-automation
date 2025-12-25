<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$db = getDB();
$userObj = getCurrentUser();
$user_id = $userObj['user_id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit;
}

$stmt = $db->prepare("SELECT id, username, full_name, email, role, department, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profileUser = $stmt->get_result()->fetch_assoc();

include 'includes/header.php';
?>

<style>
.card {
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    border: none;
}

.card-header {
    background: linear-gradient(45deg, #007bff, #0056b3);
    color: white;
    font-weight: 600;
    font-size: 1.4rem;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.card-body {
    background-color: #f9faff;
    padding: 2rem 2.5rem;
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.form-label {
    font-weight: 700;
    color: #0056b3;
}

.form-control-plaintext {
    font-size: 1.1rem;
    color: #212529;
    padding-left: 0.5rem;
}

.badge.bg-primary {
    background: linear-gradient(135deg, #0069d9, #004085);
    font-size: 1rem;
    padding: 0.4em 0.75em;
    border-radius: 1rem;
    font-weight: 600;
}

.mb-3 {
    margin-bottom: 1.6rem !important;
}
</style>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                My Profile
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($profileUser['username']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($profileUser['full_name']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($profileUser['email']); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <p class="form-control-plaintext">
                        <span class="badge bg-primary"><?php echo htmlspecialchars(ucfirst($profileUser['role'])); ?></span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <p class="form-control-plaintext"><?php echo htmlspecialchars($profileUser['department'] ?? 'Not specified'); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Member Since</label>
                    <p class="form-control-plaintext"><?php 
                        if (!empty($profileUser['created_at']) && strtotime($profileUser['created_at'])) {
                            echo formatDate($profileUser['created_at']);
                        } else {
                            echo 'Unknown';
                        }
                    ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
