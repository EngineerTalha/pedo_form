<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_temp_uploaded_file('thumb_impression', 'thumb_impression_path', 'thumb');
    save_temp_uploaded_file('signature', 'signature_path', 'signature');
    save_form_data($_POST);
    if (isset($_POST['back'])) {
        header('Location: pedoform_step2.php');
        exit;
    }
    if (isset($_POST['save_next'])) {
        set_form_message('Section G saved.');
        header('Location: pedoform_review.php');
        exit;
    }
}
$declarationName = get_form_value('full_name') ?: '______________________';

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
    <title>PEDO Application Form — Step 3 of 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Sidebar styles matching pedoform_step2.php */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            z-index: 1030;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo img {
            width: 70px;
            margin-bottom: 10px;
        }

        .sidebar-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            display: block;
            margin-top: 8px;
            color: white;
        }

        .sidebar-nav {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            gap: 12px;
            transition: all 0.3s;
        }

        .sidebar-nav li a:hover,
        .sidebar-nav li a.active {
            background: rgba(255,255,255,0.15);
            border-left: 4px solid #ffc107;
            color: white;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .user-mini {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            color: #667eea;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(220, 53, 69, 0.8);
            padding: 10px 15px;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #dc3545;
            color: white;
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1040;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-size: 1.3rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            color: white;
            cursor: pointer;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1029;
        }

        .sidebar-overlay.active {
            display: block;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: flex !important;
            }
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
                padding: 80px 15px 20px !important;
            }
        }

        @media (min-width: 769px) {
            .sidebar-toggle {
                display: none !important;
            }
            .sidebar {
                transform: translateX(0) !important;
            }
        }

        .main-content {
            margin-left: 280px;
            padding: 30px 35px;
            min-height: 100vh;
            background: #f0f2f5;
            transition: margin-left 0.3s ease;
        }

        .form-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .gov-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 40px;
            color: white;
            text-align: center;
        }

        .gov-header-title-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 10px;
        }

        .gov-header-logo {
            width: 70px;
            height: auto;
            filter: brightness(0) invert(1);
        }

        .gov-header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .gov-header p {
            margin: 0;
            opacity: 0.9;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 25px 40px;
            border-bottom: 1px solid #e9ecef;
        }

        .step-item {
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 45px;
            height: 45px;
            background: #e0e0e0;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1rem;
            color: #666;
        }

        .step-circle.completed {
            background: #229ee6;
            color: white;
        }

        .step-circle.active {
            background: #1e6b3e;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 0 4px rgba(30,107,62,0.2);
        }

        .step-label {
            font-size: 0.75rem;
            color: #6c757d;
            font-weight: 500;
        }

        .step-label.completed {
            color: #229ee6;
        }

        .step-label.active {
            color: #1e6b3e;
            font-weight: bold;
        }

        .section {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 25px 30px;
            margin: 25px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 12px;
            border-bottom: 3px solid #1e6b3e;
            display: inline-block;
        }

        .declaration-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #1e6b3e;
            line-height: 1.6;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            padding: 10px 12px;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: #1e6b3e;
            box-shadow: 0 0 0 3px rgba(30,107,62,0.1);
            outline: none;
        }

        label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9rem;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn-back {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-next {
            background: linear-gradient(135deg, #1e6b3e 0%, #15552f 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-back:hover, .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .step-indicator {
                padding: 15px;
                flex-wrap: wrap;
            }
            .step-item {
                min-width: 70px;
            }
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.85rem;
            }
            .step-label {
                font-size: 0.65rem;
            }
            .section {
                margin: 15px;
                padding: 20px;
            }
            .btn-container {
                flex-direction: column;
            }
            .btn-back, .btn-next {
                width: 100%;
                text-align: center;
            }
            .gov-header {
                padding: 20px;
            }
            .gov-header h1 {
                font-size: 1.2rem;
            }
            .gov-header-logo {
                width: 50px;
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
                    <div class="step-circle active">3</div>
                    <div class="step-label active">Declaration</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">4</div>
                    <div class="step-label">Review</div>
                </div>
            </div>

            <!-- Header -->
            <div class="gov-header">
                <div class="gov-header-title-row">
                    <img src="pedo.png" alt="PEDO Logo" class="gov-header-logo" onerror="this.style.display='none'">
                    <h1>PEDO Application Form</h1>
                    <img src="KP_logo.png" alt="KP Logo" class="gov-header-logo" onerror="this.style.display='none'">
                </div>
                <p>Step 3 of 4: Declaration</p>
            </div>

            <?php show_form_message(); ?>

            <form method="POST" action="pedoform_step3.php" enctype="multipart/form-data">
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-gavel me-2"></i> G. Declaration / Undertaking
                    </div>
                    <div class="declaration-box">
                        <p>I, <strong><?= htmlspecialchars($declarationName) ?></strong>, do hereby solemnly declare that all information provided is true and correct. Any false information may lead to cancellation of my candidature.</p>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-calendar-alt me-2"></i>Date</label>
                            <input type="date" name="declaration_date" class="form-control" value="<?= get_form_value('declaration_date') ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-fingerprint me-2"></i>Thumb Impression</label>
                            <input type="file" name="thumb_impression" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-signature me-2"></i>Signature</label>
                            <input type="file" name="signature" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="btn-container">
                    <button type="submit" name="back" value="1" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="submit" name="save_next" value="1" class="btn-next">
                        Save & Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Sidebar Toggle Functionality - Same as pedoform_step2.php
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
        
        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                if(overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
