<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');

    if (empty($email)) {
        $error = alert('Please enter your email address.', 'danger');
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = alert('Please enter a valid email address.', 'danger');
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = alert('No active user found with that email address.', 'danger');
        } else {
            $user = $result->fetch_assoc();
            $user_id = $user['id'];

            // Generate a secure random token
            $token = bin2hex(random_bytes(32));

            // Insert token into password_resets table with DB-side expiry
            $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $stmt->bind_param("is", $user_id, $token);
            if ($stmt->execute()) {
                // For demonstration, show the reset link directly (since no email)
                $resetLink = SITE_URL . "/reset-password.php?token=" . $token;
                $success = alert("Password reset link: <a href=\"$resetLink\">$resetLink</a><br><small>Link expires in 1 hour.</small>", 'success');
            } else {
                $error = alert('Failed to create password reset link. Please try again later.', 'danger');
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
                    <h4 class="mb-4">Forgot Password</h4>
                    <?php
                    echo $error;
                    echo $success;
                    ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Enter your registered email address</label>
                            <input type="email" class="form-control" name="email" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                        <div class="mt-3 text-center">
                            <a href="index.php">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
