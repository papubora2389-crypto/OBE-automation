<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        // JWT token is set inside login()
        header("Location: dashboard.php");
        exit;
    } else {
        $error = alert('Invalid username or password', 'danger');
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="row g-0">
                    <div class="col-md-6 bg-primary text-white p-5 d-none d-md-block">
                        <h2 class="mb-4"><i class="fas fa-graduation-cap fa-3x"></i></h2>
                        <h3>OBE Management System</h3>
                        <p class="mt-3">Automate your Outcome-Based Education processes</p>
                        <ul class="mt-4">
                            <li>Course Outcome Management</li>
                            <li>Assessment Tracking</li>
                            <li>Bloom's Taxonomy Analysis</li>
                            <li>Question Paper Authoring</li>
                            <li>Course File Management</li>
                        </ul>
                    </div>
                    <div class="col-md-6 p-5">
                        <h4 class="mb-4">Login</h4>
                        <?php echo $error; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" name="username" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                            <div class="mt-3 text-center">
                                <a href="forgot-password.php">Forgot Password?</a>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
