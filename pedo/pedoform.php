<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Auto-fill from user's latest application if session is empty
$user_id = $_SESSION['user_id'];
if (empty($_SESSION['application_data'])) {
    $latest_sql = "SELECT * FROM applications WHERE user_id = ? ORDER BY id DESC LIMIT 1";
    $latest_stmt = $conn->prepare($latest_sql);
    if ($latest_stmt) {
        $latest_stmt->bind_param('i', $user_id);
        $latest_stmt->execute();
        $latest_result = $latest_stmt->get_result();
        if ($row = $latest_result->fetch_assoc()) {
            $_SESSION['application_data'] = [];
            foreach ($row as $key => $value) {
                if (!in_array($key, ['id', 'user_id', 'submission_date', 'photo_path', 'thumb_impression', 'signature']) && $value !== null && $value !== '') {
                    $_SESSION['application_data'][$key] = $value;
                }
            }
            // Load employment records
            $emp_sql = "SELECT * FROM employment_records WHERE application_id = ? ORDER BY sr_no";
            $emp_stmt = $conn->prepare($emp_sql);
            if ($emp_stmt) {
                $emp_stmt->bind_param('i', $row['id']);
                $emp_stmt->execute();
                $emp_result = $emp_stmt->get_result();
                $employment = [];
                while ($emp_row = $emp_result->fetch_assoc()) {
                    $employment[] = [
                        'organization' => $emp_row['organization'],
                        'job_title' => $emp_row['job_title'],
                        'from_date' => $emp_row['from_date'],
                        'to_date' => $emp_row['to_date'],
                        'total_years' => $emp_row['total_years'],
                        'total_months' => $emp_row['total_months']
                    ];
                }
                if (!empty($employment)) {
                    for ($i = 0; $i < count($employment); $i++) {
                        $_SESSION['application_data']['employment_org'][$i] = $employment[$i]['organization'];
                        $_SESSION['application_data']['employment_title'][$i] = $employment[$i]['job_title'];
                        $_SESSION['application_data']['employment_from'][$i] = $employment[$i]['from_date'];
                        $_SESSION['application_data']['employment_to'][$i] = $employment[$i]['to_date'];
                        $_SESSION['application_data']['employment_years'][$i] = $employment[$i]['total_years'];
                        $_SESSION['application_data']['employment_months'][$i] = $employment[$i]['total_months'];
                    }
                }
                $emp_stmt->close();
            }
        }
        $latest_stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_temp_uploaded_file('photo', 'photo_path', 'photo');
    save_form_data($_POST);
    if (isset($_POST['save_next'])) {
        set_form_message('Section A, B and C saved.');
        header('Location: pedoform_step2.php');
        exit;
    }
}

// Fetch user for sidebar
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

$degreeLevels = [
    '' => 'Select level',
    'Matric (10 Years)' => 'Matric (10 Years)',
    'Intermediate / D.A.E (12/13 Years)' => 'Intermediate / D.A.E (12/13 Years)',
    'Bachelor (14 Years)' => 'Bachelor (14 Years)',
    'Bachelor (Hons) / Master (16 Years)' => 'Bachelor (Hons) / Master (16 Years)',
    'MS / M.Phil (18 Years)' => 'MS / M.Phil (18 Years)',
    'Ph.D' => 'Ph.D',
    'Other' => 'Other'
];

$qualifications = $_SESSION['application_data']['qualifications'] ?? [
    ['degree_level' => '', 'degree' => '', 'subject' => '', 'year' => '', 'obtained' => '', 'total' => '', 'division' => '', 'board' => '']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDO Application Form — Step 1 of 4</title>
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
            <!-- Step Indicator - Responsive labels always visible -->
            <div class="step-indicator">
                <div class="step-item">
                    <div class="step-circle active">1</div>
                    <div class="step-label active">Personal Info</div>
                </div>
                <div class="step-item">
                    <div class="step-circle">2</div>
                    <div class="step-label">Job Details</div>
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
                <p>Step 1 of 4: Personal Information</p>
            </div>

            <?php show_form_message(); ?>

            <form method="POST" action="pedoform.php" enctype="multipart/form-data">
                <!-- Section A: Eligibility -->
                <div class="section">
                    <div class="section-title">A. Eligibility Criteria</div>
                    <div class="eligibility-box">
                        <p>Is your Qualification, Experience & Age according to the required criteria for the post?</p>
                        <div class="radio-group">
                            <label><input type="radio" name="eligibility" value="Yes" <?= get_form_value('eligibility') === 'Yes' ? 'checked' : '' ?> required> Yes</label>
                            <label><input type="radio" name="eligibility" value="No" <?= get_form_value('eligibility') === 'No' ? 'checked' : '' ?>> No</label>
                        </div>
                        <div class="note-text">If reply is "Yes" only then proceed further.</div>
                    </div>

                    <div class="photo-row">
                        <div class="photo-instruction">
                            <strong>Passport Size Photograph</strong><br>
                            Recent (not older than 6 months), blue background.
                        </div>
                        <div class="photo-placeholder">
                            <div class="photo-frame" id="photoFrame">
                                <i class="fas fa-camera fa-2x"></i>
                                <span>Photo Area</span>
                            </div>
                            <input type="file" name="photo" accept="image/*" id="photoUpload" class="form-control">
                        </div>
                    </div>
                </div>

                <!-- Section B: Personal Information -->
                <div class="section">
                    <div class="section-title">B. Personal Information</div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Full Name *</label><input type="text" name="full_name" class="form-control" value="<?= get_form_value('full_name') ?>" required></div>
                        <div class="col-md-6 mb-3"><label>Father's Name</label><input type="text" name="father_name" class="form-control" value="<?= get_form_value('father_name') ?>"></div>
                        <div class="col-md-4 mb-3"><label>CNIC</label><input type="text" name="cnic" class="form-control" value="<?= get_form_value('cnic') ?>"></div>
                        <div class="col-md-4 mb-3"><label>Date of Birth</label><input type="date" name="dob" class="form-control" value="<?= get_form_value('dob') ?>"></div>
                        <div class="col-md-4 mb-3"><label>Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                <option value="Male" <?= get_form_value('gender')=='Male'?'selected':''?>>Male</option>
                                <option value="Female" <?= get_form_value('gender')=='Female'?'selected':''?>>Female</option>
                                <option value="Other" <?= get_form_value('gender')=='Other'?'selected':''?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label>Nationality</label><input type="text" name="nationality" class="form-control" value="<?= get_form_value('nationality') ?>"></div>
                        <div class="col-md-4 mb-3"><label>Zone</label>
                            <select name="zone" class="form-select">
                                <option value="">Select zone</option>
                                <?php for($i=1;$i<=6;$i++){ $z="Zone$i"; echo "<option ". (get_form_value('zone')==$z?'selected':'').">$z</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= get_form_value('phone') ?>"></div>
                        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= get_form_value('email') ?>"></div>
                        <div class="col-12 mb-3"><label>Present Address</label><textarea name="present_address" class="form-control" rows="2"><?= get_form_value('present_address') ?></textarea></div>
                        <div class="col-12 mb-3"><label>Permanent Address</label><textarea name="permanent_address" class="form-control" rows="2"><?= get_form_value('permanent_address') ?></textarea></div>
                    </div>
                </div>

                <!-- Section C: Academic Qualifications with LIFO Algorithm -->
                <div class="section">
                    <div class="section-title">C. Academic Qualifications</div>
                    <div class="note-text mb-3">
                        <i class="fas fa-info-circle"></i> Write exact degree name & major subject as in certificate/transcript.
                        Convert grades into marks (O/A Level etc).
                    </div>
                    
                    <div class="table-wrapper">
                        <table id="qualificationsTable" class="table">
                            <thead>
                                <tr>
                                    <th style="width:12%">Degree Level</th>
                                    <th style="width:15%">Degree/Sanad Title</th>
                                    <th style="width:15%">Major Subject</th>
                                    <th style="width:8%">Passing Year</th>
                                    <th style="width:10%">Obtained Marks</th>
                                    <th style="width:10%">Total Marks</th>
                                    <th style="width:10%">Division</th>
                                    <th style="width:15%">Board/University</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="qualificationsBody">
                                <?php foreach ($qualifications as $idx => $qual): ?>
                                <tr class="qualification-row" data-row-id="<?= $idx ?>">
                                    <td>
                                        <select name="qualifications[<?= $idx ?>][degree_level]" class="form-select" required>
                                            <?php foreach ($degreeLevels as $val => $label): ?>
                                                <option value="<?= htmlspecialchars($val) ?>" <?= get_form_array_value('qualifications', $idx, 'degree_level') === $val ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </span>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][degree]" class="form-control" placeholder="Degree title" value="<?= get_form_array_value('qualifications', $idx, 'degree') ?>"></td>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][subject]" class="form-control" placeholder="Major subject" value="<?= get_form_array_value('qualifications', $idx, 'subject') ?>"></td>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][year]" class="form-control" placeholder="Year" value="<?= get_form_array_value('qualifications', $idx, 'year') ?>"></td>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][obtained]" class="form-control" placeholder="Obtained" value="<?= get_form_array_value('qualifications', $idx, 'obtained') ?>"></td>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][total]" class="form-control" placeholder="Total" value="<?= get_form_array_value('qualifications', $idx, 'total') ?>"></td>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][division]" class="form-control" placeholder="Division" value="<?= get_form_array_value('qualifications', $idx, 'division') ?>"></span>
                                    <td><input type="text" name="qualifications[<?= $idx ?>][board]" class="form-control" placeholder="Board" value="<?= get_form_array_value('qualifications', $idx, 'board') ?>"></span>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger remove-row" data-row-id="<?= $idx ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </span>
                                </span>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button type="button" id="addQualificationBtn" class="btn-pedo-primary">
                            <i class="fas fa-plus me-2"></i>Add Qualification
                        </button>
                    </div>
                </div>

                <div class="btn-container">
                    <button type="submit" name="save_next" value="1" class="btn-next">
                        Save & Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Sidebar Toggle Functionality - Fixed
        (function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (!sidebarToggle || !sidebar) return;
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
            
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
            
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSidebar();
                });
            }
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });
            
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        })();
        
        // Photo Preview
        const photoUpload = document.getElementById('photoUpload');
        const photoFrame = document.getElementById('photoFrame');
        
        if(photoUpload && photoFrame) {
            photoUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if(file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        photoFrame.innerHTML = '<img src="'+ev.target.result+'" style="width:100%;height:100%;object-fit:cover;">';
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Display existing photo if any
            const existingPhoto = '<?= get_form_value('photo_path') ?>';
            if(existingPhoto) {
                photoFrame.innerHTML = '<img src="'+existingPhoto+'" style="width:100%;height:100%;object-fit:cover;">';
            }
        }
        
        // LIFO (Last In First Out) Algorithm for Qualifications
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.getElementById('qualificationsBody');
            const addBtn = document.getElementById('addQualificationBtn');
            let nextRowId = <?= count($qualifications) ?>;
            
            const degreeOptions = `
                <option value="">Select level</option>
                <option value="Matric (10 Years)">Matric (10 Years)</option>
                <option value="Intermediate / D.A.E (12/13 Years)">Intermediate / D.A.E (12/13 Years)</option>
                <option value="Bachelor (14 Years)">Bachelor (14 Years)</option>
                <option value="Bachelor (Hons) / Master (16 Years)">Bachelor (Hons) / Master (16 Years)</option>
                <option value="MS / M.Phil (18 Years)">MS / M.Phil (18 Years)</option>
                <option value="Ph.D">Ph.D</option>
                <option value="Other">Other</option>
            `;
            
            function getAllRows() {
                return Array.from(tbody.querySelectorAll('.qualification-row'));
            }
            
            function isRowDeletable(row) {
                const rows = getAllRows();
                if (rows.length <= 1) return false;
                return row === rows[rows.length - 1];
            }
            
            function updateDeleteButtonsState() {
                const rows = getAllRows();
                
                rows.forEach((row, index) => {
                    const deleteBtn = row.querySelector('.remove-row');
                    if (!deleteBtn) return;
                    
                    const isLastRow = index === rows.length - 1;
                    const isFirstRow = index === 0;
                    
                    if (isFirstRow && rows.length === 1) {
                        deleteBtn.disabled = true;
                        deleteBtn.style.opacity = '0.4';
                        deleteBtn.style.cursor = 'not-allowed';
                        deleteBtn.title = 'At least one qualification is required';
                    } else if (isLastRow && rows.length > 1) {
                        deleteBtn.disabled = false;
                        deleteBtn.style.opacity = '1';
                        deleteBtn.style.cursor = 'pointer';
                        deleteBtn.title = 'Remove this qualification (LIFO)';
                    } else {
                        deleteBtn.disabled = true;
                        deleteBtn.style.opacity = '0.4';
                        deleteBtn.style.cursor = 'not-allowed';
                        deleteBtn.title = 'Only the last added qualification can be removed (LIFO)';
                    }
                });
            }
            
            function addQualificationRow(data = {}) {
                const row = document.createElement('tr');
                row.className = 'qualification-row';
                row.setAttribute('data-row-id', nextRowId);
                row.innerHTML = `
                    <td>
                        <select name="qualifications[${nextRowId}][degree_level]" class="form-select" required>
                            ${degreeOptions}
                        </select>
                    </span>
                    <td><input type="text" name="qualifications[${nextRowId}][degree]" class="form-control" placeholder="Degree title" value="${escapeHtml(data.degree || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][subject]" class="form-control" placeholder="Major subject" value="${escapeHtml(data.subject || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][year]" class="form-control" placeholder="Year" value="${escapeHtml(data.year || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][obtained]" class="form-control" placeholder="Obtained" value="${escapeHtml(data.obtained || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][total]" class="form-control" placeholder="Total" value="${escapeHtml(data.total || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][division]" class="form-control" placeholder="Division" value="${escapeHtml(data.division || '')}"></span>
                    <td><input type="text" name="qualifications[${nextRowId}][board]" class="form-control" placeholder="Board" value="${escapeHtml(data.board || '')}"></span>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-row" data-row-id="${nextRowId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </span>
                `;
                
                if (data.degree_level) {
                    const select = row.querySelector('select[name*="degree_level"]');
                    if (select) select.value = data.degree_level;
                }
                
                tbody.appendChild(row);
                nextRowId++;
                
                const deleteBtn = row.querySelector('.remove-row');
                if (deleteBtn) {
                    attachRemoveHandler(deleteBtn);
                }
                
                updateDeleteButtonsState();
            }
            
            function removeQualificationRow(button) {
                const row = button.closest('tr');
                const rows = getAllRows();
                
                if (!isRowDeletable(row)) {
                    alert('Only the last added qualification can be removed (LIFO)');
                    return;
                }
                
                if (rows.length <= 1) {
                    alert('At least one qualification is required');
                    return;
                }
                
                if (confirm('Are you sure you want to remove this qualification?')) {
                    row.remove();
                    updateDeleteButtonsState();
                }
            }
            
            function attachRemoveHandler(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    removeQualificationRow(this);
                });
            }
            
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    addQualificationRow();
                });
            }
            
            document.querySelectorAll('.remove-row').forEach(btn => {
                attachRemoveHandler(btn);
            });
            
            updateDeleteButtonsState();
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
