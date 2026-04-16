<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user profile
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// Fetch applications count
$app_sql = "SELECT COUNT(*) as total FROM applications WHERE user_id = ?";
$app_stmt = $conn->prepare($app_sql);
$app_stmt->bind_param('i', $user_id);
$app_stmt->execute();
$app_count = $app_stmt->get_result()->fetch_assoc()['total'];
$app_stmt->close();

// Fetch latest applications
$latest_sql = "SELECT id, post_applied_for, department, submission_date FROM applications WHERE user_id = ? ORDER BY submission_date DESC LIMIT 5";
$latest_stmt = $conn->prepare($latest_sql);
$latest_stmt->bind_param('i', $user_id);
$latest_stmt->execute();
$latest_apps = $latest_stmt->get_result();
$latest_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PEDO Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Fix for stat cards at 820x1180px screen size */
        @media (min-width: 768px) and (max-width: 992px) {
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
            
            .stat-card {
                padding: 15px;
                min-width: 0;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }
            
            .stat-info h3 {
                font-size: 1.5rem;
            }
            
            .stat-info p {
                font-size: 0.75rem;
            }
        }
        
        /* For 820px specific width */
        @media (min-width: 800px) and (max-width: 850px) {
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 12px;
                gap: 10px;
            }
            
            .stat-icon {
                width: 45px;
                height: 45px;
                font-size: 1.2rem;
            }
            
            .stat-info h3 {
                font-size: 1.3rem;
            }
            
            .stat-info p {
                font-size: 0.7rem;
            }
        }
        
        /* For 1180px height specific */
        @media (min-height: 1100px) and (max-height: 1200px) {
            .main-content {
                padding: 25px 30px;
            }
            
            .stat-card {
                margin-bottom: 0;
            }
        }
        
        /* Ensure stat cards are uniform */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #e9ecef;
            width: 100%;
        }
        
        /* Responsive breakpoints */
        @media (max-width: 1200px) {
            .stats-grid {
                gap: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 12px;
            }
            
            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.1rem;
            }
            
            .stat-info h3 {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 400px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="pedoform.php"><i class="fas fa-file-alt"></i> New Application</a></li>
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
        <div class="page-header d-flex justify-content-between align-items-center flex-wrap">
            <div class="page-title">
                <h1 class="mb-1">Welcome back, <?= htmlspecialchars(explode(' ', $user['full_name'] ?? 'User')[0]) ?>! 👋</h1>
                <p class="text-muted mb-0">Here's what's happening with your applications today.</p>
            </div>
            <div>
                <a href="pedoform.php" class="btn btn-pedo-primary">
                    <i class="fas fa-plus me-2"></i>New Application
                </a>
            </div>
        </div>
        
        <!-- Stats Cards - Using CSS Grid for uniform layout -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $app_count ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $app_count ?></h3>
                    <p>Submitted</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3>0</h3>
                    <p>Under Review</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-info">
                    <h3>—</h3>
                    <p>Shortlisted</p>
                </div>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-history me-2"></i>Recent Applications</h3>
                        <a href="myapplications.php" class="btn btn-sm btn-pedo-outline">View All <i class="fas fa-arrow-right ms-1"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <?php if ($latest_apps->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table custom-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Post Applied For</th>
                                        <th>Department</th>
                                        <th>Submission Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($app = $latest_apps->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong>#<?= $app['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($app['post_applied_for'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($app['department'] ?: 'N/A') ?></td>
                                        <td><?= date('M d, Y', strtotime($app['submission_date'])) ?></td>
                                        <td>
                                            <a href="view_application_detail.php?id=<?= $app['id'] ?>" class="btn btn-sm btn-pedo-outline">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No applications yet</h5>
                            <p class="text-muted mb-3">Start by submitting your first application</p>
                            <a href="pedoform.php" class="btn btn-pedo-primary">
                                <i class="fas fa-plus me-2"></i>Apply Now
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="pedoform.php" class="quick-action">
                                <i class="fas fa-file-alt"></i>
                                <span>New Application</span>
                            </a>
                            <a href="myapplications.php" class="quick-action">
                                <i class="fas fa-list"></i>
                                <span>View Applications</span>
                            </a>
                            <a href="profile.php" class="quick-action">
                                <i class="fas fa-user-edit"></i>
                                <span>Update Profile</span>
                            </a>
                            <a href="#" class="quick-action">
                                <i class="fas fa-download"></i>
                                <span>Download Guidelines</span>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-user me-2"></i>Profile Summary</h3>
                        <a href="profile.php" class="btn btn-sm btn-link text-decoration-none">Edit <i class="fas fa-edit ms-1"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="profile-summary">
                            <div class="profile-item">
                                <label><i class="fas fa-envelope me-2"></i>Email</label>
                                <span><?= htmlspecialchars($user['email'] ?? '') ?></span>
                            </div>
                            <div class="profile-item">
                                <label><i class="fas fa-id-card me-2"></i>CNIC</label>
                                <span><?= htmlspecialchars($user['cnic'] ?? '') ?></span>
                            </div>
                            <div class="profile-item">
                                <label><i class="fas fa-phone me-2"></i>Phone</label>
                                <span><?= htmlspecialchars($user['phone'] ?? '') ?></span>
                            </div>
                            <div class="profile-item">
                                <label><i class="fas fa-calendar me-2"></i>Member Since</label>
                                <span><?= date('M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                            </div>
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
<?php $conn->close(); ?>