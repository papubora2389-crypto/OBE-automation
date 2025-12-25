<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = getDB();
$msg = '';

// Handle AJAX requests for course data
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();

    if ($course) {
        echo json_encode(['success' => true, 'course' => $course]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Course not found']);
    }
    exit;
}

if ($_POST) {
    if ($_POST['action'] === 'add') {
        $program_id = intval($_POST['program_id']);
        $code = sanitize($_POST['code']);
        $name = sanitize($_POST['name']);
        $credits = intval($_POST['credits']);
        $semester = intval($_POST['semester']);
        $academic_year = sanitize($_POST['academic_year']);
        $faculty_id = $_POST['faculty_id'] ? intval($_POST['faculty_id']) : null;

        $stmt = $db->prepare("INSERT INTO courses (program_id, code, name, credits, semester, academic_year, faculty_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississi", $program_id, $code, $name, $credits, $semester, $academic_year, $faculty_id);
        if ($stmt->execute()) {
            $msg = alert('Course added successfully', 'success');
        }
    } elseif ($_POST['action'] === 'edit') {
        $course_id = intval($_POST['course_id']);
        $program_id = intval($_POST['program_id']);
        $code = sanitize($_POST['code']);
        $name = sanitize($_POST['name']);
        $credits = intval($_POST['credits']);
        $semester = intval($_POST['semester']);
        $academic_year = sanitize($_POST['academic_year']);
        $faculty_id = $_POST['faculty_id'] ? intval($_POST['faculty_id']) : null;

        $stmt = $db->prepare("UPDATE courses SET program_id = ?, code = ?, name = ?, credits = ?, semester = ?, academic_year = ?, faculty_id = ? WHERE id = ?");
        $stmt->bind_param("ississii", $program_id, $code, $name, $credits, $semester, $academic_year, $faculty_id, $course_id);
        if ($stmt->execute()) {
            $msg = alert('Course updated successfully', 'success');
        } else {
            $msg = alert('Failed to update course', 'danger');
        }
    } elseif ($_POST['action'] === 'delete') {
        $course_id = intval($_POST['course_id']);

        // Check if course has enrollments or assessments
        $check_stmt = $db->prepare("SELECT COUNT(*) as cnt FROM enrollments WHERE course_id = ?");
        $check_stmt->bind_param("i", $course_id);
        $check_stmt->execute();
        $enrollment_count = $check_stmt->get_result()->fetch_assoc()['cnt'];

        $check_stmt = $db->prepare("SELECT COUNT(*) as cnt FROM assessments WHERE course_id = ?");
        $check_stmt->bind_param("i", $course_id);
        $check_stmt->execute();
        $assessment_count = $check_stmt->get_result()->fetch_assoc()['cnt'];

        if ($enrollment_count > 0 || $assessment_count > 0) {
            $msg = alert('Cannot delete course: It has enrolled students or assessments. Please remove them first.', 'danger');
        } else {
            $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
            $stmt->bind_param("i", $course_id);
            if ($stmt->execute()) {
                $msg = alert('Course deleted successfully', 'success');
            } else {
                $msg = alert('Failed to delete course', 'danger');
            }
        }
    }
}

$result = $db->query("SELECT c.*, p.name as program_name, u.full_name as faculty_name
                      FROM courses c
                      JOIN programs p ON c.program_id = p.id
                      LEFT JOIN users u ON c.faculty_id = u.id
                      ORDER BY c.semester, c.code");

include '../includes/header.php';
?>

<h2 class="mb-4">Courses Management</h2>
<?php echo $msg; ?>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add Course
</button>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Program</th>
                        <th>Semester</th>
                        <th>Faculty</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['code']; ?></strong></td>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['program_name']; ?></td>
                        <td><?php echo $row['semester']; ?></td>
                        <td><?php echo $row['faculty_name'] ?? '-'; ?></td>
                        <td>
                            <a href="course-detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-sm btn-warning" onclick="editCourse(<?php echo $row['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['code']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
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
                <h5 class="modal-title">Add Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php
                            $prog_result = $db->query("SELECT * FROM programs ORDER BY name");
                            while ($prog = $prog_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $prog['id']; ?>"><?php echo $prog['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Credits</label>
                        <input type="number" class="form-control" name="credits" value="4" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <input type="number" class="form-control" name="semester" min="1" max="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Faculty (Optional)</label>
                        <select class="form-select" name="faculty_id">
                            <option value="">None</option>
                            <?php
                            $fac_result = $db->query("SELECT * FROM users WHERE role='faculty' ORDER BY full_name");
                            while ($fac = $fac_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $fac['id']; ?>"><?php echo $fac['full_name']; ?></option>
                            <?php endwhile; ?>
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

<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="course_id" id="edit_course_id">
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <select class="form-select" name="program_id" id="edit_program_id" required>
                            <option value="">Select Program</option>
                            <?php
                            $prog_result = $db->query("SELECT * FROM programs ORDER BY name");
                            while ($prog = $prog_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $prog['id']; ?>"><?php echo $prog['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Code</label>
                        <input type="text" class="form-control" name="code" id="edit_code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Credits</label>
                        <input type="number" class="form-control" name="credits" id="edit_credits" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <input type="number" class="form-control" name="semester" id="edit_semester" min="1" max="8" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="academic_year" id="edit_academic_year" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Faculty (Optional)</label>
                        <select class="form-select" name="faculty_id" id="edit_faculty_id">
                            <option value="">None</option>
                            <?php
                            $fac_result = $db->query("SELECT * FROM users WHERE role='faculty' ORDER BY full_name");
                            while ($fac = $fac_result->fetch_assoc()):
                            ?>
                            <option value="<?php echo $fac['id']; ?>"><?php echo $fac['full_name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form method="POST" id="deleteForm" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="course_id" id="deleteCourseId">
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete course <strong id="deleteCourseCode"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.confirmDelete = function(courseId, courseCode) {
        // Set the form values
        document.getElementById('deleteCourseId').value = courseId;
        document.getElementById('deleteCourseCode').textContent = courseCode;

        // Show the modal using Bootstrap 5 API
        const modalElement = document.getElementById('deleteModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    };

    window.submitDelete = function() {
        document.getElementById('deleteForm').submit();
    };

    window.editCourse = function(courseId) {
        // Show loading state
        const modalElement = document.getElementById('editModal');
        if (!modalElement) {
            alert('Modal not found');
            return;
        }

        // Clear previous data
        document.getElementById('edit_course_id').value = '';
        document.getElementById('edit_program_id').value = '';
        document.getElementById('edit_code').value = '';
        document.getElementById('edit_name').value = '';
        document.getElementById('edit_credits').value = '';
        document.getElementById('edit_semester').value = '';
        document.getElementById('edit_academic_year').value = '';
        document.getElementById('edit_faculty_id').value = '';

        // Show modal first
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        modal.show();

        // Fetch course data via AJAX
        fetch('courses.php?ajax=1&course_id=' + courseId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.course) {
                    // Populate the edit modal with course data
                    document.getElementById('edit_course_id').value = data.course.id || '';
                    document.getElementById('edit_program_id').value = data.course.program_id || '';
                    document.getElementById('edit_code').value = data.course.code || '';
                    document.getElementById('edit_name').value = data.course.name || '';
                    document.getElementById('edit_credits').value = data.course.credits || '';
                    document.getElementById('edit_semester').value = data.course.semester || '';
                    document.getElementById('edit_faculty_id').value = data.course.faculty_id || '';

                    console.log('Course data loaded:', data.course);
                } else {
                    modal.hide();
                    alert('Failed to load course data: ' + (data.error || 'Course not found'));
                }
            })
            .catch(error => {
                console.error('Error loading course data:', error);
                modal.hide();
                alert('Failed to load course data. Please try again.');
            });
    };
});
</script>

<?php include '../includes/footer.php'; ?>
