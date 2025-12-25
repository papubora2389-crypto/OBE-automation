<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('admin');

$db = getDB();
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';

$stmt = $db->prepare("SELECT c.*, p.code as prog_code, p.name as prog_name, u.full_name as faculty_name
                     FROM courses c
                     JOIN programs p ON c.program_id = p.id
                     LEFT JOIN users u ON c.faculty_id = u.id
                     WHERE c.id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php");
    exit;
}

if ($_POST) {
        if ($_POST['action'] === 'add_co') {
            $code = sanitize($_POST['code']);
            $description = sanitize($_POST['description']);
            $bloom_level = intval($_POST['bloom_level']);

            $stmt = $db->prepare("INSERT INTO course_outcomes (course_id, code, description, bloom_level) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $course_id, $code, $description, $bloom_level);
            if ($stmt->execute()) {
                $msg = alert('Course outcome added successfully', 'success');
            }
        } elseif ($_POST['action'] === 'add_mapping') {
            // Validate that co_id and either po_id or pso_id are set and valid
            if (isset($_POST['co_id'])) {
                $co_id = intval($_POST['co_id']);
                $correlation_level = intval($_POST['correlation_level']);
                if ($co_id > 0 && $correlation_level >= 1 && $correlation_level <= 3) {
                    if (isset($_POST['po_id']) && intval($_POST['po_id']) > 0) {
                        $outcome_type = 'po';
                        $outcome_id = intval($_POST['po_id']);
                        $stmt = $db->prepare("INSERT INTO co_po_mapping (co_id, outcome_type, outcome_id, correlation_level) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE correlation_level = VALUES(correlation_level)");
                        $stmt->bind_param("isii", $co_id, $outcome_type, $outcome_id, $correlation_level);
                        if ($stmt->execute()) {
                            $msg = alert('CO-PO mapping added successfully', 'success');
                        }
                    } elseif (isset($_POST['pso_id']) && intval($_POST['pso_id']) > 0) {
                        $outcome_type = 'pso';
                        $outcome_id = intval($_POST['pso_id']);
                        $stmt = $db->prepare("INSERT INTO co_po_mapping (co_id, outcome_type, outcome_id, correlation_level) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE correlation_level = VALUES(correlation_level)");
                        $stmt->bind_param("isii", $co_id, $outcome_type, $outcome_id, $correlation_level);
                        if ($stmt->execute()) {
                            $msg = alert('CO-PSO mapping added successfully', 'success');
                        }
                    } else {
                        $msg = alert('PO or PSO must be selected', 'danger');
                    }
                } else {
                    $msg = alert('Invalid CO or correlation level selected', 'danger');
                }
            } else {
                $msg = alert('CO must be selected', 'danger');
            }
        } elseif ($_POST['action'] === 'delete_co') {
            $co_id = intval($_POST['co_id']);
            $stmt = $db->prepare("DELETE FROM course_outcomes WHERE id = ?");
            $stmt->bind_param("i", $co_id);
            if ($stmt->execute()) {
                $msg = alert('Course outcome deleted successfully', 'success');
            } else {
                $msg = alert('Error deleting course outcome', 'danger');
            }
        } elseif ($_POST['action'] === 'delete_mapping') {
            $mapping_id = intval($_POST['mapping_id']);
            $stmt = $db->prepare("DELETE FROM co_po_mapping WHERE id = ?");
            $stmt->bind_param("i", $mapping_id);
            if ($stmt->execute()) {
                $msg = alert('CO-PO mapping deleted successfully', 'success');
            } else {
                $msg = alert('Error deleting CO-PO mapping', 'danger');
            }
        } elseif ($_POST['action'] === 'update_mappings') {
            if (isset($_POST['correlation']) && is_array($_POST['correlation'])) {
                $updated = 0;
                foreach ($_POST['correlation'] as $co_id => $outcomes) {
                    foreach ($outcomes as $outcome_key => $level) {
                        list($outcome_type, $outcome_id) = explode('_', $outcome_key, 2);
                        $level = intval($level);
                        if ($level > 0) {
                            // Insert or update
                            $stmt = $db->prepare("INSERT INTO co_po_mapping (co_id, outcome_type, outcome_id, correlation_level) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE correlation_level = VALUES(correlation_level)");
                            $stmt->bind_param("isii", $co_id, $outcome_type, $outcome_id, $level);
                            $stmt->execute();
                            $updated++;
                        } else {
                            // Delete if exists
                            $stmt = $db->prepare("DELETE FROM co_po_mapping WHERE co_id = ? AND outcome_type = ? AND outcome_id = ?");
                            $stmt->bind_param("isi", $co_id, $outcome_type, $outcome_id);
                            $stmt->execute();
                        }
                    }
                }
                $msg = alert('Mappings updated successfully (' . $updated . ' correlations set)', 'success');
            } else {
                $msg = alert('No data to update', 'warning');
            }
        }
    }

include '../includes/header.php';
?>

<h2 class="mb-4">
    <i class="fas fa-chalkboard"></i> <?php echo $course['code']; ?> - <?php echo $course['name']; ?>
</h2>
<?php echo $msg; ?>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
            Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="outcomes-tab" data-bs-toggle="tab" data-bs-target="#outcomes" type="button">
            Outcomes
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="mapping-tab" data-bs-toggle="tab" data-bs-target="#mapping" type="button">
            CO-PO Mapping
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button">
            Enrollments
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="overview">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Course Information</div>
                    <div class="card-body">
                        <p><strong>Program:</strong> <?php echo $course['prog_code'] . ' - ' . $course['prog_name']; ?></p>
                        <p><strong>Code:</strong> <?php echo $course['code']; ?></p>
                        <p><strong>Name:</strong> <?php echo $course['name']; ?></p>
                        <p><strong>Credits:</strong> <?php echo $course['credits']; ?></p>
                        <p><strong>Semester:</strong> <?php echo $course['semester']; ?></p>
                        <p><strong>Faculty:</strong> <?php echo $course['faculty_name'] ?? '-'; ?></p>
                        <p><strong>Academic Year:</strong> <?php echo $course['academic_year']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Statistics</div>
                    <div class="card-body">
                        <?php
                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM enrollments WHERE course_id = ?");
                        $stmt->bind_param("i", $course_id);
                        $stmt->execute();
                        $enroll = $stmt->get_result()->fetch_assoc();

                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM course_outcomes WHERE course_id = ?");
                        $stmt->bind_param("i", $course_id);
                        $stmt->execute();
                        $outcomes = $stmt->get_result()->fetch_assoc();

                        $stmt = $db->prepare("SELECT COUNT(*) as count FROM assessments WHERE course_id = ?");
                        $stmt->bind_param("i", $course_id);
                        $stmt->execute();
                        $assessments = $stmt->get_result()->fetch_assoc();
                        ?>
                        <p><strong>Enrolled Students:</strong> <?php echo $enroll['count']; ?></p>
                        <p><strong>Course Outcomes:</strong> <?php echo $outcomes['count']; ?></p>
                        <p><strong>Assessments:</strong> <?php echo $assessments['count']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="outcomes">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Course Outcomes</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCoModal">
                    <i class="fas fa-plus"></i> Add CO
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Bloom's Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                            $stmt->bind_param("i", $course_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($co = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong><?php echo $co['code']; ?></strong></td>
                                <td><?php echo $co['description']; ?></td>
                                <td><?php echo bloomLevelLabel(intval($co['bloom_level'])); ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Delete this CO?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete_co">
                                        <input type="hidden" name="co_id" value="<?php echo $co['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete CO">
                                            <i class="fas fa-trash"></i>
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
    </div>

    <div class="tab-pane fade" id="mapping">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">CO-PO Mapping</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_mappings">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>CO Code</th>
                                    <th>CO Description</th>
                                    <?php
                                    // Fetch all POs and PSOs for the program
                                    $stmt = $db->prepare("SELECT 'po' as type, id, code FROM program_outcomes WHERE program_id = ? UNION SELECT 'pso' as type, id, code FROM program_specific_outcomes WHERE program_id = ? ORDER BY type, CAST(REGEXP_REPLACE(code, '[^0-9]', '') AS UNSIGNED)");
                                    $stmt->bind_param("ii", $course['program_id'], $course['program_id']);
                                    $stmt->execute();
                                    $outcomes_result = $stmt->get_result();
                                    $outcomes = [];
                                    while ($outcome = $outcomes_result->fetch_assoc()) {
                                        $outcomes[] = $outcome;
                                        echo '<th class="text-center">' . $outcome['code'] . '</th>';
                                    }
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch all COs for the course
                                $stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                                $stmt->bind_param("i", $course_id);
                                $stmt->execute();
                                $cos_result = $stmt->get_result();

                                // Fetch all mappings for the course
                                $stmt = $db->prepare("SELECT cpm.co_id, cpm.outcome_type, cpm.outcome_id, cpm.correlation_level FROM co_po_mapping cpm
                                                   JOIN course_outcomes co ON cpm.co_id = co.id
                                                   WHERE co.course_id = ?");
                                $stmt->bind_param("i", $course_id);
                                $stmt->execute();
                                $mappings_result = $stmt->get_result();
                                $mappings = [];
                                while ($mapping = $mappings_result->fetch_assoc()) {
                                    $mappings[$mapping['co_id']][$mapping['outcome_type'] . '_' . $mapping['outcome_id']] = $mapping['correlation_level'];
                                }

                                while ($co = $cos_result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo $co['code']; ?></strong></td>
                                    <td><?php echo $co['description']; ?></td>
                                    <?php
                                    foreach ($outcomes as $outcome) {
                                        $key = $outcome['type'] . '_' . $outcome['id'];
                                        $current_level = isset($mappings[$co['id']][$key]) ? $mappings[$co['id']][$key] : 0;
                                        $input_name = 'correlation[' . $co['id'] . '][' . $outcome['type'] . '_' . $outcome['id'] . ']';
                                        echo '<td class="text-center">';
                                        echo '<select name="' . $input_name . '" class="form-select form-select-sm" style="width: auto; margin: 0 auto;">';
                                        echo '<option value="0">-</option>';
                                        for ($i = 1; $i <= 3; $i++) {
                                            $selected = ($current_level == $i) ? ' selected' : '';
                                            echo '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
                                        }
                                        echo '</select>';
                                        echo '</td>';
                                    }
                                    ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="students">
        <div class="card">
            <div class="card-header">Enrolled Students</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Enrollment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT u.*, e.enrollment_date FROM enrollments e
                                               JOIN users u ON e.student_id = u.id
                                               WHERE e.course_id = ? ORDER BY u.full_name");
                            $stmt->bind_param("i", $course_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($student = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $student['full_name']; ?></td>
                                <td><?php echo $student['email']; ?></td>
                                <td><?php echo formatDate($student['enrollment_date']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCoModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Course Outcome</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_co">
                    <div class="mb-3">
                        <label class="form-label">CO Code</label>
                        <input type="text" class="form-control" name="code" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bloom's Level</label>
                        <select class="form-select" name="bloom_level" required>
                            <?php foreach (getBloomLevels() as $key => $label): ?>
                            <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
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
