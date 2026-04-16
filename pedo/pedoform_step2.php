<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_form_data($_POST);
    if (isset($_POST['back'])) {
        header('Location: pedoform.php');
        exit;
    }
    if (isset($_POST['save_next'])) {
        set_form_message('Section D and E saved successfully.');
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

// Load saved employment records from session
$employment_orgs = get_form_value('employment_org', []);
$employment_titles = get_form_value('employment_title', []);
$employment_from = get_form_value('employment_from', []);
$employment_to = get_form_value('employment_to', []);
$employment_years = get_form_value('employment_years', []);
$employment_months = get_form_value('employment_months', []);

// Ensure we have at least one row
if (empty($employment_orgs) || !is_array($employment_orgs)) {
    $employment_orgs = [''];
    $employment_titles = [''];
    $employment_from = [''];
    $employment_to = [''];
    $employment_years = [''];
    $employment_months = [''];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDO Application Form — Step 2 of 4</title>
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
                    <div class="step-circle active">2</div>
                    <div class="step-label active">Job Details</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">3</div>
                    <div class="step-label">Declaration</div>
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
                <p>Step 2 of 4: Job & Employment Details</p>
            </div>

            <?php show_form_message(); ?>

            <form method="POST" action="pedoform_step2.php">
                <!-- Section D: Job Details -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-briefcase me-2"></i> D. Job & Additional Details
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-file-signature me-2"></i>Post Applied For *</label>
                            <input type="text" name="post_applied_for" class="form-control" 
                                   value="<?= get_form_value('post_applied_for') ?>" 
                                   placeholder="Enter post name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-building me-2"></i>Department / Section</label>
                            <input type="text" name="department" class="form-control" 
                                   value="<?= get_form_value('department') ?>" 
                                   placeholder="Department name">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-map-marker-alt me-2"></i>District</label>
                            <input type="text" name="district" class="form-control" 
                                   value="<?= get_form_value('district') ?>" 
                                   placeholder="Your district">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-location-dot me-2"></i>Preferred Job Location</label>
                            <input type="text" name="preferred_job_location" class="form-control" 
                                   value="<?= get_form_value('preferred_job_location') ?>" 
                                   placeholder="Preferred location">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-info-circle me-2"></i>Source of Information</label>
                            <input type="text" name="source_information" class="form-control" 
                                   value="<?= get_form_value('source_information') ?>" 
                                   placeholder="Advertisement / Website / Referral">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><i class="fas fa-user-check me-2"></i>Currently Employed?</label>
                            <select name="currently_employed" class="form-select">
                                <option value="">Select an option</option>
                                <option value="Yes" <?= get_form_value('currently_employed') == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                <option value="No" <?= get_form_value('currently_employed') == 'No' ? 'selected' : '' ?>>No</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section E: Employment Record -->
                <div class="section">
                    <div class="section-title">
                        <i class="fas fa-history me-2"></i> E. Employment Record (Latest first)
                    </div>
                    <div class="table-wrapper">
                        <table class="table" id="employmentTable">
                            <thead>
                                <tr>
                                    <th style="width:5%">#</th>
                                    <th style="width:20%">Organization/Employer</th>
                                    <th style="width:20%">Job Title</th>
                                    <th style="width:15%">From (Month/Year)</th>
                                    <th style="width:15%">To (Month/Year)</th>
                                    <th style="width:10%">Years</th>
                                    <th style="width:10%">Months</th>
                                    <th style="width:5%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="employmentTableBody">
                                <?php for ($i = 0; $i < count($employment_orgs); $i++): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?= $i+1 ?></td>
                                    <td><input type="text" name="employment_org[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_orgs[$i] ?? '') ?>" placeholder="Employer name"></td>
                                    <td><input type="text" name="employment_title[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_titles[$i] ?? '') ?>" placeholder="Job title"></td>
                                    <td><input type="month" name="employment_from[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_from[$i] ?? '') ?>"></td>
                                    <td><input type="month" name="employment_to[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_to[$i] ?? '') ?>"></td>
                                    <td><input type="text" name="employment_years[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_years[$i] ?? '') ?>" placeholder="Years"></td>
                                    <td><input type="text" name="employment_months[]" class="form-control form-control-sm" value="<?= htmlspecialchars($employment_months[$i] ?? '') ?>" placeholder="Months"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn-remove-row" onclick="removeRow(this)">
                                            <i class="fas fa-trash" style="color: #dc3545;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Add Row Button -->
                    <div class="text-center">
                        <button type="button" class="btn-add-row" onclick="addNewRow()">
                            <i class="fas fa-plus me-2"></i>Add Another Employment Record
                        </button>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i> 
                        <small>Fill your employment history starting from the most recent. Click "Add Another Employment Record" to add more rows.</small>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-calendar-alt me-2"></i>Total Job Experience</label>
                            <input type="text" name="total_experience" class="form-control" 
                                   value="<?= get_form_value('total_experience') ?>" 
                                   placeholder="e.g., 5 Years 2 Months">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-chart-line me-2"></i>Total Relevant Experience</label>
                            <input type="text" name="relevant_experience" class="form-control" 
                                   value="<?= get_form_value('relevant_experience') ?>" 
                                   placeholder="Relevant experience">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><i class="fas fa-microscope me-2"></i>Specific Experience (if required)</label>
                            <input type="text" name="specific_experience" class="form-control" 
                                   value="<?= get_form_value('specific_experience') ?>" 
                                   placeholder="Specific field experience">
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
        
        // Function to add new row to employment table
        function addNewRow() {
            const tbody = document.getElementById('employmentTableBody');
            const rowCount = tbody.children.length;
            const newRowNumber = rowCount + 1;
            
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="text-center fw-bold">${newRowNumber}</td>
                <td><input type="text" name="employment_org[]" class="form-control form-control-sm" placeholder="Employer name"></td>
                <td><input type="text" name="employment_title[]" class="form-control form-control-sm" placeholder="Job title"></td>
                <td><input type="month" name="employment_from[]" class="form-control form-control-sm"></td>
                <td><input type="month" name="employment_to[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="employment_years[]" class="form-control form-control-sm" placeholder="Years"></td>
                <td><input type="text" name="employment_months[]" class="form-control form-control-sm" placeholder="Months"></td>
                <td class="text-center">
                    <button type="button" class="btn-remove-row" onclick="removeRow(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            
            tbody.appendChild(newRow);
            updateRowNumbers();
            showToast('New employment record added successfully');
        }
        
        // Function to remove a row
        function removeRow(button) {
            const row = button.closest('tr');
            const tbody = document.getElementById('employmentTableBody');
            
            if (tbody.children.length <= 1) {
                alert('At least one employment record is required');
                return;
            }
            
            if (confirm('Are you sure you want to remove this employment record?')) {
                row.remove();
                updateRowNumbers();
                showToast('Employment record removed successfully');
            }
        }
        
        // Function to update row numbers after removal
        function updateRowNumbers() {
            const rows = document.getElementById('employmentTableBody').children;
            for (let i = 0; i < rows.length; i++) {
                const firstCell = rows[i].cells[0];
                firstCell.textContent = i + 1;
                firstCell.className = 'text-center fw-bold';
            }
        }
        
        // Toast notification function
        function showToast(message) {
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.cssText = 'position: fixed; bottom: 20px; right: 20px; z-index: 9999;';
                document.body.appendChild(toastContainer);
            }
            
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0';
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.style.cssText = 'min-width: 250px; margin-bottom: 10px;';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
            bsToast.show();
            toast.addEventListener('hidden.bs.toast', () => toast.remove());
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
