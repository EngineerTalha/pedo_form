<?php
// submit_form.php
require_once 'config.php';
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Handle file uploads
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_path = $upload_dir . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    $thumb_impression_path = '';
    if (isset($_FILES['thumb_impression']) && $_FILES['thumb_impression']['error'] == 0) {
        $file_extension = pathinfo($_FILES['thumb_impression']['name'], PATHINFO_EXTENSION);
        $thumb_impression_path = $upload_dir . time() . '_' . rand(1000, 9999) . '_thumb.' . $file_extension;
        move_uploaded_file($_FILES['thumb_impression']['tmp_name'], $thumb_impression_path);
    }

    $signature_path = '';
    if (isset($_FILES['signature']) && $_FILES['signature']['error'] == 0) {
        $file_extension = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
        $signature_path = $upload_dir . time() . '_' . rand(1000, 9999) . '_signature.' . $file_extension;
        move_uploaded_file($_FILES['signature']['tmp_name'], $signature_path);
    }
    
    // Get eligibility
    $eligibility = isset($_POST['eligibility']) ? $_POST['eligibility'] : '';
    
    // Academic Qualifications - Matric
    $matric_degree = $_POST['matric_degree'] ?? '';
    $matric_subject = $_POST['matric_subject'] ?? '';
    $matric_year = $_POST['matric_year'] ?? '';
    $matric_obtained = $_POST['matric_obtained'] ?? '';
    $matric_total = $_POST['matric_total'] ?? '';
    $matric_division = $_POST['matric_division'] ?? '';
    $matric_board = $_POST['matric_board'] ?? '';
    
    // Intermediate
    $intermediate_degree = $_POST['intermediate_degree'] ?? '';
    $intermediate_subject = $_POST['intermediate_subject'] ?? '';
    $intermediate_year = $_POST['intermediate_year'] ?? '';
    $intermediate_obtained = $_POST['intermediate_obtained'] ?? '';
    $intermediate_total = $_POST['intermediate_total'] ?? '';
    $intermediate_division = $_POST['intermediate_division'] ?? '';
    $intermediate_board = $_POST['intermediate_board'] ?? '';
    
    // Bachelor
    $bachelor_degree = $_POST['bachelor_degree'] ?? '';
    $bachelor_subject = $_POST['bachelor_subject'] ?? '';
    $bachelor_year = $_POST['bachelor_year'] ?? '';
    $bachelor_obtained = $_POST['bachelor_obtained'] ?? '';
    $bachelor_total = $_POST['bachelor_total'] ?? '';
    $bachelor_division = $_POST['bachelor_division'] ?? '';
    $bachelor_board = $_POST['bachelor_board'] ?? '';
    
    // Bachelor Hons/Master
    $bachelor_hons_degree = $_POST['bachelor_hons_degree'] ?? '';
    $bachelor_hons_subject = $_POST['bachelor_hons_subject'] ?? '';
    $bachelor_hons_year = $_POST['bachelor_hons_year'] ?? '';
    $bachelor_hons_obtained = $_POST['bachelor_hons_obtained'] ?? '';
    $bachelor_hons_total = $_POST['bachelor_hons_total'] ?? '';
    $bachelor_hons_division = $_POST['bachelor_hons_division'] ?? '';
    $bachelor_hons_board = $_POST['bachelor_hons_board'] ?? '';
    
    // MS/M.Phil
    $ms_degree = $_POST['ms_degree'] ?? '';
    $ms_subject = $_POST['ms_subject'] ?? '';
    $ms_year = $_POST['ms_year'] ?? '';
    $ms_obtained = $_POST['ms_obtained'] ?? '';
    $ms_total = $_POST['ms_total'] ?? '';
    $ms_division = $_POST['ms_division'] ?? '';
    $ms_board = $_POST['ms_board'] ?? '';
    
    // PhD
    $phd_degree = $_POST['phd_degree'] ?? '';
    $phd_subject = $_POST['phd_subject'] ?? '';
    $phd_year = $_POST['phd_year'] ?? '';
    $phd_obtained = $_POST['phd_obtained'] ?? '';
    $phd_total = $_POST['phd_total'] ?? '';
    $phd_division = $_POST['phd_division'] ?? '';
    $phd_board = $_POST['phd_board'] ?? '';
    
    // Experience
    $total_experience = $_POST['total_experience'] ?? '';
    $relevant_experience = $_POST['relevant_experience'] ?? '';
    $specific_experience = $_POST['specific_experience'] ?? '';
    
    // Declaration
    $full_name = $_POST['full_name'] ?? '';
    $declaration_date = $_POST['declaration_date'] ?? '';
    
    // Insert into applications table
    $sql = "INSERT INTO applications (
        eligibility, photo_path, thumb_impression, signature,
        matric_degree, matric_subject, matric_year, matric_obtained, matric_total, matric_division, matric_board,
        intermediate_degree, intermediate_subject, intermediate_year, intermediate_obtained, intermediate_total, intermediate_division, intermediate_board,
        bachelor_degree, bachelor_subject, bachelor_year, bachelor_obtained, bachelor_total, bachelor_division, bachelor_board,
        bachelor_hons_degree, bachelor_hons_subject, bachelor_hons_year, bachelor_hons_obtained, bachelor_hons_total, bachelor_hons_division, bachelor_hons_board,
        ms_degree, ms_subject, ms_year, ms_obtained, ms_total, ms_division, ms_board,
        phd_degree, phd_subject, phd_year, phd_obtained, phd_total, phd_division, phd_board,
        total_experience, relevant_experience, specific_experience,
        full_name, declaration_date
    ) VALUES (
        '$eligibility', '$photo_path', '$thumb_impression_path', '$signature_path',
        '$matric_degree', '$matric_subject', '$matric_year', '$matric_obtained', '$matric_total', '$matric_division', '$matric_board',
        '$intermediate_degree', '$intermediate_subject', '$intermediate_year', '$intermediate_obtained', '$intermediate_total', '$intermediate_division', '$intermediate_board',
        '$bachelor_degree', '$bachelor_subject', '$bachelor_year', '$bachelor_obtained', '$bachelor_total', '$bachelor_division', '$bachelor_board',
        '$bachelor_hons_degree', '$bachelor_hons_subject', '$bachelor_hons_year', '$bachelor_hons_obtained', '$bachelor_hons_total', '$bachelor_hons_division', '$bachelor_hons_board',
        '$ms_degree', '$ms_subject', '$ms_year', '$ms_obtained', '$ms_total', '$ms_division', '$ms_board',
        '$phd_degree', '$phd_subject', '$phd_year', '$phd_obtained', '$phd_total', '$phd_division', '$phd_board',
        '$total_experience', '$relevant_experience', '$specific_experience',
        '$full_name', '$declaration_date'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $application_id = $conn->insert_id;
        
        // Insert employment records
        if (isset($_POST['employment_org'])) {
            for ($i = 0; $i < count($_POST['employment_org']); $i++) {
                if (!empty($_POST['employment_org'][$i])) {
                    $emp_sql = "INSERT INTO employment_records (
                        application_id, sr_no, organization, job_title, from_date, to_date, total_years, total_months
                    ) VALUES (
                        '$application_id', " . ($i + 1) . ", 
                        '{$_POST['employment_org'][$i]}', 
                        '{$_POST['employment_title'][$i]}', 
                        '{$_POST['employment_from'][$i]}', 
                        '{$_POST['employment_to'][$i]}', 
                        '{$_POST['employment_years'][$i]}', 
                        '{$_POST['employment_months'][$i]}'
                    )";
                    $conn->query($emp_sql);
                }
            }
        }
        
        // Success message
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Submission Successful</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .success { color: green; font-size: 24px; margin-bottom: 20px; }
                .message { font-size: 18px; margin-bottom: 20px; }
                .btn { padding: 10px 20px; background: #1a5f7a; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='success'>✓ Application Submitted Successfully!</div>
            <div class='message'>Your application has been recorded. Application ID: #$application_id</div>
            <a href='pedoform.php' class='btn'>Submit Another Application</a>
        </body>
        </html>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
} else {
    header('Location: pedoform.php');
    exit();
}
?>