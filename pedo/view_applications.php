<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['application_data'])) {
    header('Location: pedoform.php');
    exit;
}

if (empty($_SESSION['submission_token'])) {
    $_SESSION['submission_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_form_data($_POST);
    if (isset($_POST['back'])) {
        header('Location: pedoform_step3.php');
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDO Application Form — Step 4 of 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <img src="pedo.png" alt="PEDO" onerror="this.style.display='none'">
            <span>PEDO Portal</span>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="pedoform.php" class="active"><i class="fas fa-file-alt"></i> New Application</a></li>
            <li><a href="myapplications.php"><i class="fas fa-list"></i> My Applications</a></li>
            <li><a href="profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> Help & Support</a></li>
        </ul>
        <div class="sidebar-footer">
            <div class="user-mini">
                <div class="user-avatar"><?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? 'User') ?></div>
                    <div class="user-role">Applicant</div>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="form-container">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-item">
                    <div class="step-circle completed">1</div>
                    <div class="step-label completed">Personal Info</div>
                </div>
                <div class="step-item">
                    <div class="step-circle completed">2</div>
                    <div class="step-label completed">Job Details</div>
                </div>
                <div class="step-item">
                    <div class="step-circle completed">3</div>
                    <div class="step-label completed">Declaration</div>
                </div>
                <div class="step-item">
                    <div class="step-circle active">4</div>
                    <div class="step-label active">Review</div>
                </div>
            </div>

            <!-- Header -->
            <div class="gov-header">
                <div class="gov-header-title-row">
                    <img src="pedo.png" alt="PEDO Logo" class="gov-header-logo" onerror="this.style.display='none'">
                    <h1>PEDO Application Form</h1>
                    <img src="KP_logo.png" alt="KP Logo" class="gov-header-logo" onerror="this.style.display='none'">
                </div>
                <p>Step 4 of 4: Final Review & Submission</p>
            </div>

            <?php show_form_message(); ?>

            <form method="POST" action="submit_form.php">
                <input type="hidden" name="submission_token" value="<?= $_SESSION['submission_token'] ?>">
                
                <!-- Section F: Final Review -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-check-circle me-2"></i> F. Final Review & Submission
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label><i class="fas fa-calendar-check me-2"></i>Ready to Join</label>
                            <select name="ready_to_join" class="form-select">
                                <option value="">Select readiness</option>
                                <option value="Immediately" <?= get_form_value('ready_to_join') === 'Immediately' ? 'selected' : '' ?>>Immediately</option>
                                <option value="Within 1 Month" <?= get_form_value('ready_to_join') === 'Within 1 Month' ? 'selected' : '' ?>>Within 1 Month</option>
                                <option value="After Notice Period" <?= get_form_value('ready_to_join') === 'After Notice Period' ? 'selected' : '' ?>>After Notice Period</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <label><i class="fas fa-comment me-2"></i>Additional Information</label>
                            <textarea name="additional_information" class="form-control" rows="4" placeholder="Any other information you wish to provide..."><?= get_form_value('additional_information') ?></textarea>
                        </div>
                        
                        <div class="col-12">
                            <div class="checkbox-row">
                                <input type="checkbox" id="agreeTerms" name="agree_terms" value="1" <?= get_form_value('agree_terms') === '1' ? 'checked' : '' ?>>
                                <label for="agreeTerms" class="mb-0">
                                    <i class="fas fa-gavel me-2"></i>
                                    I certify that all information provided in this application is true and correct.
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="btn-container">
                    <button type="submit" name="back" value="1" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" name="submit_application" value="1" class="btn-submit">
                        <i class="fas fa-paper-plane me-2"></i> Submit Application
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Sidebar Toggle Functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            if(overlay) overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
        
        if(sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }
        if(overlay) {
            overlay.addEventListener('click', toggleSidebar);
        }
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                if(overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
