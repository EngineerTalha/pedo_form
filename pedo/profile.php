<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user data
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if ($full_name && $cnic && $phone) {
        $update_sql = "UPDATE users SET full_name = ?, cnic = ?, phone = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('sssi', $full_name, $cnic, $phone, $user_id);
        
        if ($update_stmt->execute()) {
            $success_message = 'Profile updated successfully!';
            // Refresh user data
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param('i', $user_id);
            $user_stmt->execute();
            $user = $user_stmt->get_result()->fetch_assoc();
            $user_stmt->close();
        } else {
            $error_message = 'Error updating profile: ' . $conn->error;
        }
        $update_stmt->close();
    } else {
        $error_message = 'Please fill all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - PEDO</title>
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
            <li><a href="myapplications.php"><i class="fas fa-list"></i> My Applications</a></li>
            <li><a href="profile.php" class="active"><i class="fas fa-user-edit"></i> Edit Profile</a></li>
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
                <div class="card custom-card">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-edit fa-3x text-success"></i>
                            <h2 class="mt-2">Edit Profile</h2>
                            <p class="text-muted">Update your personal information</p>
                        </div>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>



                        <form method="POST" action="profile.php">
                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-user me-2"></i>Full Name *</label>
                                <input type="text" name="full_name" class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($user['full_name']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                <input type="email" class="form-control form-control-lg bg-light" 
                                       value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="fas fa-id-card me-2"></i>CNIC Number *</label>
                                <input type="text" name="cnic" class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($user['cnic']) ?>" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold"><i class="fas fa-phone me-2"></i>Phone Number *</label>
                                <input type="text" name="phone" class="form-control form-control-lg" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-pedo-primary flex-grow-1">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="dashboard.php" class="btn btn-pedo-outline flex-grow-1">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                </a>
                            </div>
                        </form>
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
<?php $conn->close(); ?>
