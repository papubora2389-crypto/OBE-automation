<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = getDB();
$msg = '';
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if ($_POST) {
    if ($_POST['action'] === 'add_po') {
        $code = sanitize($_POST['code']);
        $description = sanitize($_POST['description']);
        $pid = intval($_POST['program_id']);

        $stmt = $db->prepare("INSERT INTO program_outcomes (program_id, code, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $pid, $code, $description);
        if ($stmt->execute()) {
            $msg = alert('PO added successfully', 'success');
        }
    } elseif ($_POST['action'] === 'add_pso') {
        $code = sanitize($_POST['code']);
        $description = sanitize($_POST['description']);
        $pid = intval($_POST['program_id']);

        $stmt = $db->prepare("INSERT INTO program_specific_outcomes (program_id, code, description) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $pid, $code, $description);
        if ($stmt->execute()) {
            $msg = alert('PSO added successfully', 'success');
        }
    } elseif ($_POST['action'] === 'delete_po') {
        $po_id = intval($_POST['po_id']);
        $stmt = $db->prepare("DELETE FROM program_outcomes WHERE id = ?");
        $stmt->bind_param("i", $po_id);
        if ($stmt->execute()) {
            $msg = alert('PO deleted successfully', 'success');
        } else {
            $msg = alert('Error deleting PO', 'danger');
        }
    } elseif ($_POST['action'] === 'delete_pso') {
        $pso_id = intval($_POST['pso_id']);
        $stmt = $db->prepare("DELETE FROM program_specific_outcomes WHERE id = ?");
        $stmt->bind_param("i", $pso_id);
        if ($stmt->execute()) {
            $msg = alert('PSO deleted successfully', 'success');
        } else {
            $msg = alert('Error deleting PSO', 'danger');
        }
    }
}

include '../includes/header.php';
?>

<h2 class="mb-4">Program Outcomes Management</h2>
<?php echo $msg; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <form method="GET" class="row g-3">
            <div class="col-md-10">
                <label class="form-label">Select Program</label>
                <select name="program_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Select Program --</option>
                    <?php
                    $prog_result = $db->query("SELECT * FROM programs ORDER BY name");
                    while ($prog = $prog_result->fetch_assoc()):
                    ?>
                    <option value="<?php echo $prog['id']; ?>" <?php echo ($program_id == $prog['id']) ? 'selected' : ''; ?>>
                        <?php echo $prog['code'] . ' - ' . $prog['name']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($program_id > 0): ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Program Outcomes (POs)</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPOModal">
                    <i class="fas fa-plus"></i> Add PO
                </button>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT * FROM program_outcomes WHERE program_id = ? ORDER BY code");
                $stmt->bind_param("i", $program_id);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <div class="list-group">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?php echo $row['code']; ?></h6>
                            <p class="mb-0 small"><?php echo $row['description']; ?></p>
                        </div>
                        <form method="POST" onsubmit="return confirm('Delete this PO?');" class="m-0 p-0">
                            <input type="hidden" name="action" value="delete_po">
                            <input type="hidden" name="po_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete PO">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Program Specific Outcomes (PSOs)</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPSOModal">
                    <i class="fas fa-plus"></i> Add PSO
                </button>
            </div>
            <div class="card-body">
                <?php
                $stmt = $db->prepare("SELECT * FROM program_specific_outcomes WHERE program_id = ? ORDER BY code");
                $stmt->bind_param("i", $program_id);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <div class="list-group">
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1"><?php echo $row['code']; ?></h6>
                            <p class="mb-0 small"><?php echo $row['description']; ?></p>
                        </div>
                        <form method="POST" onsubmit="return confirm('Delete this PSO?');" class="m-0 p-0">
                            <input type="hidden" name="action" value="delete_pso">
                            <input type="hidden" name="pso_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete PSO">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addPOModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Program Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_po">
                    <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">PO Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., PO1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
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

<div class="modal fade" id="addPSOModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Program Specific Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_pso">
                    <input type="hidden" name="program_id" value="<?php echo $program_id; ?>">
                    <div class="mb-3">
                        <label class="form-label">PSO Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g., PSO1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
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
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
