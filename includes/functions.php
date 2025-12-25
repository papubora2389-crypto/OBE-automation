<?php
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function alert($msg, $type = 'info') {
    return "<div class='alert alert-$type alert-dismissible fade show' role='alert'>
        $msg
        <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
    </div>";
}

function formatDate($date, $format = 'd-m-Y') {
    return date($format, strtotime($date));
}

function getBloomLevels() {
    // Return bloom levels as an associative array with numeric keys and string labels
    return [
        1 => 'Remembering',
        2 => 'Understanding',
        3 => 'Applying',
        4 => 'Analyzing',
        5 => 'Evaluating',
        6 => 'Creating'
    ];
}

// Helper function to get bloom label from numeric level
function bloomLevelLabel($level) {
    $levels = getBloomLevels();
    return $levels[$level] ?? 'Unknown';
}

function getAssessmentTypes() {
    return ['Sessional 1', 'Sessional 2', 'Mid Semester Examination', 'End Semester Examination', 'Lab Examination'];
}

function calculateAttainment($co_id, $course_id) {
    $db = getDB();

    // First, try to get pre-calculated attainment from co_attainment table (includes weightage)
    $stmt = $db->prepare("SELECT ROUND(SUM(ca.attainment_percentage * a.weightage) / SUM(a.weightage), 2) as attainment
                         FROM co_attainment ca
                         JOIN assessments a ON ca.assessment_id = a.id
                         WHERE ca.co_id = ? AND a.course_id = ?");
    $stmt->bind_param("ii", $co_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row && $row['attainment'] !== null) {
        return $row['attainment'];
    }

    // Fallback: calculate weighted average on the fly (course-level, not CO-specific)
    $stmt = $db->prepare("SELECT SUM((sm.marks_obtained / a.max_marks) * 100 * a.weightage) / SUM(a.weightage) as attainment
                         FROM student_marks sm
                         JOIN assessments a ON sm.assessment_id = a.id
                         WHERE a.course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['attainment'] ?? 0, 2);
}

function uploadFile($file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return false;
    }
    $filename = time() . '_' . basename($file['name']);
    $path = __DIR__ . '/../uploads/course_files/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $path)) {
        return ['name' => $filename, 'path' => $path];
    }
    return false;
}

function getSemesterLabel($semester) {
    $semester = intval($semester);
    if ($semester % 2 == 1) {
        return 'Spring';
    } else {
        return 'Autumn';
    }
}
?>
