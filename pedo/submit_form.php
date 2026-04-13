<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedoform_review.php');
    exit;
}

// Determine whether the applications table has a user_id column
$use_user_id_column = false;
$columns_check = $conn->query("SHOW COLUMNS FROM applications LIKE 'user_id'");
if ($columns_check && $columns_check->num_rows > 0) {
    $use_user_id_column = true;
} else {
    // Add the column automatically if it doesn't exist, to correctly associate applications with users
    $alter_result = $conn->query("ALTER TABLE applications ADD COLUMN user_id INT(11) NULL AFTER id");
    if ($alter_result) {
        $use_user_id_column = true;
    }
}

// Check if user already has a submitted application
$existing_application_id = null;
if ($use_user_id_column) {
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT id FROM applications WHERE user_id = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt) {
        $check_stmt->bind_param('i', $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        $check_stmt->bind_result($existing_application_id);
        $check_stmt->fetch();

        if ($check_stmt->num_rows > 0) {
            $check_stmt->close();
            $_SESSION['duplicate_submission'] = true;
            $_SESSION['submission_success'] = true;
            $_SESSION['application_id'] = $existing_application_id;
            header('Location: submission_success.php');
            exit;
        }
        $check_stmt->close();
    }
} else {
    $user_email = $_SESSION['email'] ?? '';
    if ($user_email !== '') {
        $check_sql = "SELECT id FROM applications WHERE email = ? LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        if ($check_stmt) {
            $check_stmt->bind_param('s', $user_email);
            $check_stmt->execute();
            $check_stmt->store_result();
            $check_stmt->bind_result($existing_application_id);
            $check_stmt->fetch();

            if ($check_stmt->num_rows > 0) {
                $check_stmt->close();
                $_SESSION['duplicate_submission'] = true;
                $_SESSION['submission_success'] = true;
                $_SESSION['application_id'] = $existing_application_id;
                header('Location: submission_success.php');
                exit;
            }
            $check_stmt->close();
        }
    }
}

// Validate and check submission token to prevent duplicate submissions
$submitted_token = $_POST['submission_token'] ?? '';
$session_token = $_SESSION['submission_token'] ?? '';
$used_tokens = $_SESSION['used_submission_tokens'] ?? [];

// Check if token is missing or doesn't match
if (empty($submitted_token) || $submitted_token !== $session_token) {
    die('Invalid submission token. Please go back and try again.');
}

// Check if this token has already been used (prevents replay attacks)
if (in_array($submitted_token, $used_tokens, true)) {
    // Preserve duplicate submission state and redirect to the success page
    $_SESSION['duplicate_submission'] = true;
    $_SESSION['submission_success'] = true;
    if (!empty($_SESSION['application_id'])) {
        $_SESSION['application_id'] = $_SESSION['application_id'];
    }
    header('Location: submission_success.php');
    exit;
}

$application_data = $_SESSION['application_data'] ?? [];
$data = array_merge($application_data, $_POST);

function safe_value($data, $key, $default = '') {
    return isset($data[$key]) && !is_array($data[$key]) ? $data[$key] : $default;
}

function persist_file_path($path, $uploadDir, $prefix) {
    if (empty($path)) {
        return '';
    }
    if (strpos($path, 'uploads/temp/') !== 0) {
        return $path;
    }
    $source = __DIR__ . '/' . $path;
    if (!file_exists($source)) {
        return '';
    }
    $extension = pathinfo($source, PATHINFO_EXTENSION);
    $targetName = sprintf('%s_%s.%s', $prefix, time() . '_' . rand(1000, 9999), $extension);
    $target = $uploadDir . $targetName;
    if (rename($source, $target)) {
        return $target;
    }
    return '';
}

$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$photo_path = '';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo_path = $upload_dir . time() . '_' . rand(1000, 9999) . '.' . $extension;
    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
} elseif (!empty($application_data['photo_path'])) {
    $photo_path = persist_file_path($application_data['photo_path'], $upload_dir, 'photo');
}

$thumb_impression_path = '';
if (isset($_FILES['thumb_impression']) && $_FILES['thumb_impression']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['thumb_impression']['name'], PATHINFO_EXTENSION);
    $thumb_impression_path = $upload_dir . time() . '_' . rand(1000, 9999) . '_thumb.' . $extension;
    move_uploaded_file($_FILES['thumb_impression']['tmp_name'], $thumb_impression_path);
} elseif (!empty($application_data['thumb_impression_path'])) {
    $thumb_impression_path = persist_file_path($application_data['thumb_impression_path'], $upload_dir, 'thumb');
}

$signature_path = '';
if (isset($_FILES['signature']) && $_FILES['signature']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
    $signature_path = $upload_dir . time() . '_' . rand(1000, 9999) . '_signature.' . $extension;
    move_uploaded_file($_FILES['signature']['tmp_name'], $signature_path);
} elseif (!empty($application_data['signature_path'])) {
    $signature_path = persist_file_path($application_data['signature_path'], $upload_dir, 'signature');
}

$eligibility = safe_value($data, 'eligibility');
$full_name = safe_value($data, 'full_name');
$father_name = safe_value($data, 'father_name');
$cnic = safe_value($data, 'cnic');
$dob = safe_value($data, 'dob');
$gender = safe_value($data, 'gender');
$nationality = safe_value($data, 'nationality');
$phone = safe_value($data, 'phone');
$email = safe_value($data, 'email');
$user_email = $_SESSION['email'] ?? '';
if (!empty($user_email)) {
    $email = $user_email;
}
$present_address = safe_value($data, 'present_address');
$permanent_address = safe_value($data, 'permanent_address');
$post_applied_for = safe_value($data, 'post_applied_for');
$department = safe_value($data, 'department');
$district = safe_value($data, 'district');
$preferred_job_location = safe_value($data, 'preferred_job_location');
$source_information = safe_value($data, 'source_information');
$currently_employed = safe_value($data, 'currently_employed');
$matric_degree = safe_value($data, 'matric_degree');
$matric_subject = safe_value($data, 'matric_subject');
$matric_year = safe_value($data, 'matric_year');
$matric_obtained = safe_value($data, 'matric_obtained');
$matric_total = safe_value($data, 'matric_total');
$matric_division = safe_value($data, 'matric_division');
$matric_board = safe_value($data, 'matric_board');
$intermediate_degree = safe_value($data, 'intermediate_degree');
$intermediate_subject = safe_value($data, 'intermediate_subject');
$intermediate_year = safe_value($data, 'intermediate_year');
$intermediate_obtained = safe_value($data, 'intermediate_obtained');
$intermediate_total = safe_value($data, 'intermediate_total');
$intermediate_division = safe_value($data, 'intermediate_division');
$intermediate_board = safe_value($data, 'intermediate_board');
$bachelor_degree = safe_value($data, 'bachelor_degree');
$bachelor_subject = safe_value($data, 'bachelor_subject');
$bachelor_year = safe_value($data, 'bachelor_year');
$bachelor_obtained = safe_value($data, 'bachelor_obtained');
$bachelor_total = safe_value($data, 'bachelor_total');
$bachelor_division = safe_value($data, 'bachelor_division');
$bachelor_board = safe_value($data, 'bachelor_board');
$bachelor_hons_degree = safe_value($data, 'bachelor_hons_degree');
$bachelor_hons_subject = safe_value($data, 'bachelor_hons_subject');
$bachelor_hons_year = safe_value($data, 'bachelor_hons_year');
$bachelor_hons_obtained = safe_value($data, 'bachelor_hons_obtained');
$bachelor_hons_total = safe_value($data, 'bachelor_hons_total');
$bachelor_hons_division = safe_value($data, 'bachelor_hons_division');
$bachelor_hons_board = safe_value($data, 'bachelor_hons_board');
$ms_degree = safe_value($data, 'ms_degree');
$ms_subject = safe_value($data, 'ms_subject');
$ms_year = safe_value($data, 'ms_year');
$ms_obtained = safe_value($data, 'ms_obtained');
$ms_total = safe_value($data, 'ms_total');
$ms_division = safe_value($data, 'ms_division');
$ms_board = safe_value($data, 'ms_board');
$phd_degree = safe_value($data, 'phd_degree');
$phd_subject = safe_value($data, 'phd_subject');
$phd_year = safe_value($data, 'phd_year');
$phd_obtained = safe_value($data, 'phd_obtained');
$phd_total = safe_value($data, 'phd_total');
$phd_division = safe_value($data, 'phd_division');
$phd_board = safe_value($data, 'phd_board');
$total_experience = safe_value($data, 'total_experience');
$relevant_experience = safe_value($data, 'relevant_experience');
$specific_experience = safe_value($data, 'specific_experience');
$ready_to_join = safe_value($data, 'ready_to_join');
$additional_information = safe_value($data, 'additional_information');
$agree_terms = isset($data['agree_terms']) && ($data['agree_terms'] === '1' || $data['agree_terms'] === 1) ? 1 : 0;
$declaration_date = safe_value($data, 'declaration_date');

$insert_columns = "eligibility, photo_path, thumb_impression, signature,
        full_name, father_name, cnic, dob, gender, nationality, phone, email, present_address, permanent_address,
        post_applied_for, department, district, preferred_job_location, source_information, currently_employed,
        matric_degree, matric_subject, matric_year, matric_obtained, matric_total, matric_division, matric_board,
        intermediate_degree, intermediate_subject, intermediate_year, intermediate_obtained, intermediate_total, intermediate_division, intermediate_board,
        bachelor_degree, bachelor_subject, bachelor_year, bachelor_obtained, bachelor_total, bachelor_division, bachelor_board,
        bachelor_hons_degree, bachelor_hons_subject, bachelor_hons_year, bachelor_hons_obtained, bachelor_hons_total, bachelor_hons_division, bachelor_hons_board,
        ms_degree, ms_subject, ms_year, ms_obtained, ms_total, ms_division, ms_board,
        phd_degree, phd_subject, phd_year, phd_obtained, phd_total, phd_division, phd_board,
        total_experience, relevant_experience, specific_experience,
        declaration_date, ready_to_join, additional_information, agree_terms";

$insert_values = "'" . $conn->real_escape_string($eligibility) . "', '" . $conn->real_escape_string($photo_path) . "', '" . $conn->real_escape_string($thumb_impression_path) . "', '" . $conn->real_escape_string($signature_path) . "',
        '" . $conn->real_escape_string($full_name) . "', '" . $conn->real_escape_string($father_name) . "', '" . $conn->real_escape_string($cnic) . "', '" . $conn->real_escape_string($dob) . "', '" . $conn->real_escape_string($gender) . "', '" . $conn->real_escape_string($nationality) . "', '" . $conn->real_escape_string($phone) . "', '" . $conn->real_escape_string($email) . "', '" . $conn->real_escape_string($present_address) . "', '" . $conn->real_escape_string($permanent_address) . "',
        '" . $conn->real_escape_string($post_applied_for) . "', '" . $conn->real_escape_string($department) . "', '" . $conn->real_escape_string($district) . "', '" . $conn->real_escape_string($preferred_job_location) . "', '" . $conn->real_escape_string($source_information) . "', '" . $conn->real_escape_string($currently_employed) . "',
        '" . $conn->real_escape_string($matric_degree) . "', '" . $conn->real_escape_string($matric_subject) . "', '" . $conn->real_escape_string($matric_year) . "', '" . $conn->real_escape_string($matric_obtained) . "', '" . $conn->real_escape_string($matric_total) . "', '" . $conn->real_escape_string($matric_division) . "', '" . $conn->real_escape_string($matric_board) . "',
        '" . $conn->real_escape_string($intermediate_degree) . "', '" . $conn->real_escape_string($intermediate_subject) . "', '" . $conn->real_escape_string($intermediate_year) . "', '" . $conn->real_escape_string($intermediate_obtained) . "', '" . $conn->real_escape_string($intermediate_total) . "', '" . $conn->real_escape_string($intermediate_division) . "', '" . $conn->real_escape_string($intermediate_board) . "',
        '" . $conn->real_escape_string($bachelor_degree) . "', '" . $conn->real_escape_string($bachelor_subject) . "', '" . $conn->real_escape_string($bachelor_year) . "', '" . $conn->real_escape_string($bachelor_obtained) . "', '" . $conn->real_escape_string($bachelor_total) . "', '" . $conn->real_escape_string($bachelor_division) . "', '" . $conn->real_escape_string($bachelor_board) . "',
        '" . $conn->real_escape_string($bachelor_hons_degree) . "', '" . $conn->real_escape_string($bachelor_hons_subject) . "', '" . $conn->real_escape_string($bachelor_hons_year) . "', '" . $conn->real_escape_string($bachelor_hons_obtained) . "', '" . $conn->real_escape_string($bachelor_hons_total) . "', '" . $conn->real_escape_string($bachelor_hons_division) . "', '" . $conn->real_escape_string($bachelor_hons_board) . "',
        '" . $conn->real_escape_string($ms_degree) . "', '" . $conn->real_escape_string($ms_subject) . "', '" . $conn->real_escape_string($ms_year) . "', '" . $conn->real_escape_string($ms_obtained) . "', '" . $conn->real_escape_string($ms_total) . "', '" . $conn->real_escape_string($ms_division) . "', '" . $conn->real_escape_string($ms_board) . "',
        '" . $conn->real_escape_string($phd_degree) . "', '" . $conn->real_escape_string($phd_subject) . "', '" . $conn->real_escape_string($phd_year) . "', '" . $conn->real_escape_string($phd_obtained) . "', '" . $conn->real_escape_string($phd_total) . "', '" . $conn->real_escape_string($phd_division) . "', '" . $conn->real_escape_string($phd_board) . "',
        '" . $conn->real_escape_string($total_experience) . "', '" . $conn->real_escape_string($relevant_experience) . "', '" . $conn->real_escape_string($specific_experience) . "',
        '" . $conn->real_escape_string($declaration_date) . "', '" . $conn->real_escape_string($ready_to_join) . "', '" . $conn->real_escape_string($additional_information) . "', '$agree_terms'";

if ($use_user_id_column) {
    $insert_columns = "user_id, " . $insert_columns;
    $insert_values = "'" . $conn->real_escape_string($user_id) . "', " . $insert_values;
}

$sql = "INSERT INTO applications (" . $insert_columns . ") VALUES (" . $insert_values . ")";

if ($conn->query($sql) === TRUE) {
    $application_id = $conn->insert_id;

    $employment_org = $data['employment_org'] ?? [];
    $employment_title = $data['employment_title'] ?? [];
    $employment_from = $data['employment_from'] ?? [];
    $employment_to = $data['employment_to'] ?? [];
    $employment_years = $data['employment_years'] ?? [];
    $employment_months = $data['employment_months'] ?? [];

    for ($i = 0; $i < count($employment_org); $i++) {
        $org = $conn->real_escape_string($employment_org[$i] ?? '');
        if ($org === '') {
            continue;
        }
        $title = $conn->real_escape_string($employment_title[$i] ?? '');
        $from = $conn->real_escape_string($employment_from[$i] ?? '');
        $to = $conn->real_escape_string($employment_to[$i] ?? '');
        $years = $conn->real_escape_string($employment_years[$i] ?? '');
        $months = $conn->real_escape_string($employment_months[$i] ?? '');
        $emp_sql = "INSERT INTO employment_records (application_id, sr_no, organization, job_title, from_date, to_date, total_years, total_months) VALUES ('$application_id', " . ($i + 1) . ", '$org', '$title', '$from', '$to', '$years', '$months')";
        $conn->query($emp_sql);
    }

    // Mark token as used to prevent replay attacks
    if (!is_array($_SESSION['used_submission_tokens'])) {
        $_SESSION['used_submission_tokens'] = [];
    }
    $_SESSION['used_submission_tokens'][] = $submitted_token;

    // Store success data and redirect
    $_SESSION['submission_success'] = true;
    $_SESSION['application_id'] = $application_id;
    
    clear_form_data();
    header('Location: submission_success.php');
    exit;
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
