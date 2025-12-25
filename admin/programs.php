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
        $code = sanitize($_POST['code']);
        $name = sanitize($_POST['name']);
        $dept = sanitize($_POST['department']);
        $duration = intval($_POST['duration']);

        $stmt = $db->prepare("INSERT INTO programs (code, name, department, duration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $code, $name, $dept, $duration);
        if ($stmt->execute()) {
            $msg = alert('Program added successfully', 'success');
        }
    }
}

$result = $db->query("SELECT * FROM programs ORDER BY name");

include '../includes/header.php';
?>

<h2 class="mb-4">Programs Management</h2>
<?php echo $msg; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add Program
</button>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Duration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['code']; ?></strong></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['department']; ?></td>
                        <td><?php echo $row['duration']; ?> years</td>
                        <td>
                            <a href="outcomes.php?program_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i> Outcomes
                            </a>
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
                <h5 class="modal-title">Add Program</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <input type="text" class="form-control" name="department" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Duration (Years)</label>
                        <input type="number" class="form-control" name="duration" value="4" required>
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
