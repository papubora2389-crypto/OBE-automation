<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = alert('Invalid or missing token.', 'danger');
} else {
    $db = getDB();

    // Check if token exists and is not expired
    $stmt = $db->prepare("SELECT pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = alert('Invalid or expired token.', 'danger');
    } else {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];
        $expires_at = $row['expires_at'];

        if (strtotime($expires_at) < time()) {
            $error = alert('This reset link has expired.', 'danger');
            // Optionally, delete expired token here
            $stmtDel = $db->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmtDel->bind_param("s", $token);
            $stmtDel->execute();
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';

                if (empty($password) || empty($confirm_password)) {
                    $error = alert('Please fill in all password fields.', 'danger');
                } elseif ($password !== $confirm_password) {
                    $error = alert('Passwords do not match.', 'danger');
                } elseif (strlen($password) < 6) {
                    $error = alert('Password must be at least 6 characters long.', 'danger');
                } else {
                    // Hash password and update user
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmtUpdate = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmtUpdate->bind_param("si", $password_hash, $user_id);

                    if ($stmtUpdate->execute()) {
                        // Delete the token after successful reset
                        $stmtDel = $db->prepare("DELETE FROM password_resets WHERE token = ?");
                        $stmtDel->bind_param("s", $token);
                        $stmtDel->execute();

                        $success = alert('Password reset successful! You can now <a href="index.php">login</a>.', 'success');
                    } else {
                        $error = alert('Failed to reset password. Please try again.', 'danger');
                    }
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="mb-4">Reset Password</h4>
                    <?php
                    echo $error;
                    echo $success;

                    if (!$success && empty($error)) {
                    ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
