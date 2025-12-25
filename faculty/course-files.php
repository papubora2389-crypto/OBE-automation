<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$msg = '';

if ($_POST && isset($_FILES['file'])) {
    $course_id = intval($_POST['course_id']);
    $file_type = sanitize($_POST['file_type']);
    $description = sanitize($_POST['description']);

    $upload_result = uploadFile($_FILES['file']);
    if ($upload_result) {
        $file_name = $upload_result['name'];
        $file_size = $_FILES['file']['size'];

        $stmt = $db->prepare("INSERT INTO course_files (course_id, file_type, file_name, file_path, file_size, uploaded_by, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $course_id, $file_type, $file_name, $upload_result['path'], $file_size, $user_id, $description);
        if ($stmt->execute()) {
            $msg = alert('File uploaded successfully', 'success');
        }
    } else {
        $msg = alert('Failed to upload file', 'danger');
    }
}

// Handle file deletion
if ($_POST && isset($_POST['delete_file_id'])) {
    $delete_file_id = intval($_POST['delete_file_id']);

    // Check if the file belongs to the current faculty member
    $stmt = $db->prepare("SELECT file_path FROM course_files WHERE id = ? AND uploaded_by = ?");
    $stmt->bind_param("ii", $delete_file_id, $user_id);
    $stmt->execute();
    $file_result = $stmt->get_result();

    if ($file_result->num_rows > 0) {
        $file = $file_result->fetch_assoc();

        // Delete the physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        // Delete from database
        $stmt = $db->prepare("DELETE FROM course_files WHERE id = ? AND uploaded_by = ?");
        $stmt->bind_param("ii", $delete_file_id, $user_id);
        if ($stmt->execute()) {
            $msg = alert('File deleted successfully', 'success');
        } else {
            $msg = alert('Failed to delete file from database', 'danger');
        }
    } else {
        $msg = alert('File not found or access denied', 'danger');
    }
}

$stmt = $db->prepare("SELECT cf.*, c.code FROM course_files cf
                     JOIN courses c ON cf.course_id = c.id
                     WHERE cf.uploaded_by = ? ORDER BY cf.upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-folder"></i> Course Files</h2>
<?php echo $msg; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
    <i class="fas fa-upload"></i> Upload File
</button>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>File Type</th>
                        <th>File Name</th>
                        <th>Size</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['code']; ?></td>
                        <td><span class="badge bg-info"><?php echo $row['file_type']; ?></span></td>
                        <td><?php echo $row['file_name']; ?></td>
                        <td><?php echo round($row['file_size'] / 1024, 2); ?> KB</td>
                        <td><?php echo formatDate($row['upload_date']); ?></td>
                        <td>
                            <a href="../download.php?id=<?php echo $row['id']; ?>&action=view" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="../download.php?id=<?php echo $row['id']; ?>&action=download" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?')">
                                <input type="hidden" name="delete_file_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select class="form-select" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY code");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $course_result = $stmt->get_result();
                            while ($course = $course_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo $course['code']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Type</label>
                        <select class="form-select" name="file_type" required>
                            <option value="Syllabus">Syllabus</option>
                            <option value="Lesson Plan">Lesson Plan</option>
                            <option value="CO-PO Mapping">CO-PO Mapping</option>
                            <option value="Question Paper">Question Paper</option>
                            <option value="Answer Key">Answer Key</option>
                            <option value="Attainment Report">Attainment Report</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="file" class="form-control" name="file" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
