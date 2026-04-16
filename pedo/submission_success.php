<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if there was a successful submission
$just_submitted = isset($_SESSION['submission_success']) && $_SESSION['submission_success'];
$application_id = $_SESSION['application_id'] ?? null;

// Clear the session flags so this page can be revisited
unset($_SESSION['submission_success']);
unset($_SESSION['application_id']);

// Fetch user for sidebar
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
    <title>Submission Successful - PEDO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <li><a href="pedoform.php"><i class="fas fa-file-alt"></i> New Application</a></li>
            <li><a href="myapplications.php" class="btn btn-pedo-primary"></li>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card custom-card text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-4x text-success"></i>
                        </div>
                        <h2 class="text-success mb-3">Application Submitted Successfully!</h2>
                        
                        <?php if ($just_submitted && $application_id): ?>
                        <p class="lead mb-3">Thank you for submitting your application.</p>
                        <p class="text-muted mb-4">Your application has been recorded in our system.</p>
                        
                        <div class="alert alert-info">
                            <label class="fw-bold">Application ID:</label>
                            <h3 class="text-primary mb-0">#<?= htmlspecialchars($application_id) ?></h3>
                        </div>
                        <?php else: ?>
                        <p class="lead mb-4">Your application has been recorded in our system.</p>
                        <?php endif; ?>
                        
                        <div class="d-flex gap-3 justify-content-center mt-4">
                            <a href="myapplications.php" class="btn btn-pedo-primary">
                                <i class="fas fa-list me-2"></i>View My Applications
                            </a>
                            <a href="dashboard.php" class="btn btn-pedo-outline">
                                <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                            </a>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Submitted applications are read-only. To make changes, please submit a new application from the dashboard.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }
        
        sidebarToggle?.addEventListener('click', toggleSidebar);
        overlay?.addEventListener('click', toggleSidebar);
        
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) toggleSidebar();
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
</body>
</html>
