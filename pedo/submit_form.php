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

$user_id = $_SESSION['user_id'];

// Validate submission token
$submitted_token = $_POST['submission_token'] ?? '';
$session_token = $_SESSION['submission_token'] ?? '';

if (empty($submitted_token) || $submitted_token !== $session_token) {
    die('Invalid submission token. Please go back and try again.');
}

$application_data = $_SESSION['application_data'] ?? [];
$data = array_merge($application_data, $_POST);

function safe_value($data, $key, $default = '') {
    return isset($data[$key]) && !is_array($data[$key]) ? trim($data[$key]) : $default;
}

// Get form values
$full_name = safe_value($data, 'full_name');
$email = safe_value($data, 'email');
if (empty($email)) {
    $email = $_SESSION['email'] ?? '';
}
$cnic = safe_value($data, 'cnic');
$phone = safe_value($data, 'phone');
$post_applied_for = safe_value($data, 'post_applied_for');
$department = safe_value($data, 'department');
$ready_to_join = safe_value($data, 'ready_to_join');
$additional_information = safe_value($data, 'additional_information');
$agree_terms = isset($data['agree_terms']) && ($data['agree_terms'] === '1' || $data['agree_terms'] === 1) ? 1 : 0;

// Simple insert query with only essential columns
$sql = "INSERT INTO applications (user_id, full_name, email, cnic, phone, post_applied_for, department, ready_to_join, additional_information, agree_terms, submission_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('issssssssi', $user_id, $full_name, $email, $cnic, $phone, $post_applied_for, $department, $ready_to_join, $additional_information, $agree_terms);

if ($stmt->execute()) {
    $application_id = $conn->insert_id;
    
    // Mark token as used
    $_SESSION['submission_success'] = true;
    $_SESSION['application_id'] = $application_id;
    
    clear_form_data();
    header('Location: submission_success.php');
    exit;
} else {
    die("Error saving application: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
