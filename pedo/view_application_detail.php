<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get application ID from URL
$application_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($application_id === 0) {
    header('Location: myapplications.php');
    exit;
}

// Fetch application data (read-only)
$sql = "SELECT a.id AS application_id, a.*, u.full_name 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.id = ? AND a.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $application_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$application) {
    $_SESSION['error_message'] = 'Application not found or access denied.';
    header('Location: myapplications.php');
    exit;
}

// Use application_id from alias
$application['id'] = $application['application_id'];

// Fetch employment records
$emp_sql = "SELECT * FROM employment_records WHERE application_id = ? ORDER BY sr_no";
$emp_stmt = $conn->prepare($emp_sql);
$emp_stmt->bind_param('i', $application_id);
$emp_stmt->execute();
$employment_records = $emp_stmt->get_result();
$emp_stmt->close();

// Fetch user for sidebar
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
    <title>Application Details #<?= $application_id ?> - PEDO</title>
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
            <li><a href="myapplications.php" class="active"><i class="fas fa-list"></i> My Applications</a></li>
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
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><i class="fas fa-file-alt me-2"></i>Application Details #<?= $application_id ?></h3>
                    <p class="text-muted mb-0 small">Submitted on <?= date('F d, Y', strtotime($application['submission_date'])) ?></p>
                </div>
                <span class="badge bg-success fs-6 px-3 py-2">✓ Submitted</span>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This is a read-only view of your submitted application. To make changes, please submit a new application from the dashboard.
                </div>

                <!-- Personal Information -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </div>
                    <div class="row">
                        <?php if ($application['photo_path'] && file_exists($application['photo_path'])): ?>
                        <div class="col-12 mb-3">
                            <div class="photo-frame" style="width: 150px;">
                                <img src="<?= $application['photo_path'] ?>" alt="Applicant Photo" class="img-fluid">
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Full Name</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['full_name']) ?></p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Father's Name</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['father_name']) ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">CNIC</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['cnic']) ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Date of Birth</label>
                            <p class="fw-bold"><?= $application['dob'] ? date('M d, Y', strtotime($application['dob'])) : 'N/A' ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Gender</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['gender']) ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Email</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['email']) ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Phone</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['phone']) ?></p>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="text-muted small">Nationality</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['nationality']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Job Information -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-briefcase me-2"></i>Job Information
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Post Applied For</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['post_applied_for']) ?></p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Department</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['department']) ?></p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">District</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['district']) ?></p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="text-muted small">Preferred Location</label>
                            <p class="fw-bold"><?= htmlspecialchars($application['preferred_job_location']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Employment Record -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-history me-2"></i>Employment Record
                    </div>
                    <?php if ($employment_records->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Organization</th>
                                    <th>Job Title</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Years</th>
                                    <th>Months</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($emp = $employment_records->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $emp['sr_no'] ?></td>
                                    <td><?= htmlspecialchars($emp['organization']) ?></td>
                                    <td><?= htmlspecialchars($emp['job_title']) ?></td>
                                    <td><?= $emp['from_date'] ?></td>
                                    <td><?= $emp['to_date'] ?></td>
                                    <td><?= $emp['total_years'] ?></td>
                                    <td><?= $emp['total_months'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No employment records found.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <a href="myapplications.php" class="btn btn-pedo-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Applications
                    </a>
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
