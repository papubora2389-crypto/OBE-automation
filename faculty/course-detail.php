<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('faculty');

$db = getDB();
$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT c.*, p.code as prog_code, p.name as prog_name, c.program_id FROM courses c
                     JOIN programs p ON c.program_id = p.id
                     WHERE c.id = ? AND c.faculty_id = ?");
$stmt->bind_param("ii", $course_id, $user_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php");
    exit;
}

if ($_POST) {
    if ($_POST['action'] === 'add_assessment') {
        $title = sanitize($_POST['title']);
        $type = sanitize($_POST['type']);
        $weight = floatval($_POST['weight']);
        $max_marks = intval($_POST['max_marks']);
        $description = sanitize($_POST['description']);

        $stmt = $db->prepare("INSERT INTO assessments (course_id, title, type, weight, max_marks, description, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdisi", $course_id, $title, $type, $weight, $max_marks, $description, $user_id);
        if ($stmt->execute()) {
            $msg = alert('Assessment added successfully', 'success');
        }
    }
}

include '../includes/header.php';
?>

<h2 class="mb-4">
    <i class="fas fa-chalkboard"></i> <?php echo $course['code']; ?> - <?php echo $course['name']; ?>
</h2>

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
            Overview
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="outcomes-tab" data-bs-toggle="tab" data-bs-target="#outcomes" type="button">
            Course Outcomes
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#students" type="button">
            Students
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="mapping-tab" data-bs-toggle="tab" data-bs-target="#mapping" type="button">
            CO-PO Mapping
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="attainment-tab" data-bs-toggle="tab" data-bs-target="#attainment" type="button">
            Attainment
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
            <div class="card-header">Course Outcomes (COs)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Bloom's Level</th>
                                <th>Mapped POs</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT * FROM course_outcomes WHERE course_id = ? ORDER BY code");
                            $stmt->bind_param("i", $course_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($co = $result->fetch_assoc()):
                                $stmt2 = $db->prepare("SELECT po.code FROM co_po_mapping cpm
                                                     JOIN program_outcomes po ON cpm.outcome_id = po.id
                                                     WHERE cpm.co_id = ? AND cpm.outcome_type = 'po'");
                                $stmt2->bind_param("i", $co['id']);
                                $stmt2->execute();
                                $mapped_pos = $stmt2->get_result();
                            ?>
                            <tr>
                                <td><strong><?php echo $co['code']; ?></strong></td>
                                <td><?php echo substr($co['description'], 0, 50); ?>...</td>
                                <td><?php echo bloomLevelLabel(intval($co['bloom_level'])); ?></td>
                                <td>
                                    <?php
                                    $pos = [];
                                    while ($po = $mapped_pos->fetch_assoc()) {
                                        $pos[] = $po['code'];
                                    }
                                    echo implode(', ', $pos) ?: '-';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
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
                                <th>Roll Number</th>
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
                                <td><?php echo $student['username']; ?></td>
                                <td><?php echo formatDate($student['enrollment_date']); ?></td>
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
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>CO Code</th>
                                <th>CO Description</th>
                                <?php
                                // Fetch all POs and PSOs for the program
                                $stmt = $db->prepare("SELECT 'po' as type, id, code FROM program_outcomes WHERE program_id = ? UNION SELECT 'pso' as type, id, code FROM program_specific_outcomes WHERE program_id = ? ORDER BY code");
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
                                    $level = isset($mappings[$co['id']][$key]) ? $mappings[$co['id']][$key] : 0;
                                    echo '<td class="text-center">';
                                    if ($level > 0) {
                                        echo $level;
                                    } else {
                                        echo '-';
                                    }
                                    echo '</td>';
                                }
                                ?>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="attainment">
        <div class="card">
            <div class="card-header">CO Attainment Analysis</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Course Outcome</th>
                                <th>Attainment %</th>
                                <th>Status</th>
                                <th>Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->prepare("SELECT DISTINCT co.* FROM course_outcomes co WHERE co.course_id = ? ORDER BY co.code");
                            $stmt->bind_param("i", $course_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($co = $result->fetch_assoc()):
                                $attainment = calculateAttainment($co['id'], $course_id);
                            ?>
                            <tr>
                                <td><strong><?php echo $co['code']; ?></strong> - <?php echo substr($co['description'], 0, 40); ?>...</td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-<?php echo $attainment >= 60 ? 'success' : 'danger'; ?>"
                                             style="width: <?php echo $attainment; ?>%">
                                            <?php echo round($attainment, 2); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $attainment >= 60 ? 'success' : 'danger'; ?>">
                                        <?php echo $attainment >= 60 ? 'Attained' : 'Not Attained'; ?>
                                    </span>
                                </td>
                                <td>60%</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.nav-link').forEach(tab => {
    tab.addEventListener('click', function() {
        var target = this.getAttribute('data-bs-target');
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });
        document.querySelector(target).classList.add('show', 'active');
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        this.classList.add('active');
    });
});
</script>
<?php include '../includes/footer.php'; ?>
