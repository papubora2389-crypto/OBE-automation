<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = getDB();
$msg = '';

if ($_POST) {
    if ($_POST['action'] === 'add') {
        $username = sanitize($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $email = sanitize($_POST['email']);
        $full_name = sanitize($_POST['full_name']);
        $role = sanitize($_POST['role']);
        $department = sanitize($_POST['department']);
        $program = sanitize($_POST['program']);

        $stmt = $db->prepare("INSERT INTO users (username, password, email, full_name, role, department, program) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $password, $email, $full_name, $role, $department, $program);
        if ($stmt->execute()) {
            $msg = alert('User added successfully', 'success');
        } else {
            $msg = alert('Error: Username or email already exists', 'danger');
        }
    }
}

$result = $db->query("SELECT * FROM users ORDER BY role, full_name");

include '../includes/header.php';
?>

<h2 class="mb-4">Users Management</h2>
<?php echo $msg; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add User
</button>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['username']; ?></strong></td>
                        <td><?php echo $row['full_name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $row['role'] === 'admin' ? 'danger' : ($row['role'] === 'faculty' ? 'primary' : 'success'); ?>">
                                <?php echo ucfirst($row['role']); ?>
                            </span>
                        </td>
                        <td><?php echo $row['department']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $row['is_active'] ? 'success' : 'secondary'; ?>">
                                <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" id="usernameLabel">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" name="department" id="department" required>
                            <option value="computer science and engineering">Computer Science and Engineering</option>
                        </select>
                    </div>
                    <div class="mb-3" id="programField">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program" id="program" required>
                            <option value="btech">BTech</option>
                            <option value="mtech">MTech</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
document.getElementById('role').addEventListener('change', function() {
    var role = this.value;
    var usernameLabel = document.getElementById('usernameLabel');
    var programField = document.getElementById('programField');
    if (role === 'student') {
        usernameLabel.textContent = 'Roll Number';
        programField.style.display = 'block';
    } else {
        usernameLabel.textContent = 'Username';
        programField.style.display = 'none';
    }
});

// Trigger change event on page load to set initial label and visibility
document.getElementById('role').dispatchEvent(new Event('change'));
</script>
