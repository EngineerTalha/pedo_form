<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle payment status update if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_payment'])) {
    $app_id = intval($_POST['application_id']);
    $payment_status = $_POST['payment_status'];
    
    // Add payment_status column if not exists
    $conn->query("ALTER TABLE applications ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'pending'");
    $conn->query("ALTER TABLE applications ADD COLUMN IF NOT EXISTS payment_date TIMESTAMP NULL");
    $conn->query("ALTER TABLE applications ADD COLUMN IF NOT EXISTS payment_amount DECIMAL(10,2) DEFAULT 0");
    
    $update_sql = "UPDATE applications SET payment_status = ?, payment_date = NOW() WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sii', $payment_status, $app_id, $user_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Payment status updated successfully!";
    } else {
        $error_message = "Error updating payment status.";
    }
    $update_stmt->close();
}

// Fetch user's applications
$sql = "SELECT a.*, 
        CASE 
            WHEN a.payment_status IS NULL THEN 'pending'
            ELSE a.payment_status 
        END as payment_status
        FROM applications a 
        WHERE a.user_id = ? 
        ORDER BY a.submission_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

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
    <title>My Applications - PEDO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #ffc107; color: #856404; }
        .status-paid { background: #28a745; color: white; }
        .status-failed { background: #dc3545; color: white; }
        .status-submitted { background: #17a2b8; color: white; }
        .payment-btn {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
            border: none;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        }
        .view-btn {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .print-btn {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .application-row:hover {
            background-color: #f8f9fa;
            transition: all 0.3s;
        }
        .modal-payment {
            border-radius: 15px;
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
                    <h3 class="mb-0"><i class="fas fa-list me-2"></i>My Applications</h3>
                    <p class="text-muted mb-0 small">View all your submitted applications and manage payments</p>
                </div>
                <a href="dashboard.php" class="btn btn-pedo-outline">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i><?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>App ID</th>
                                    <th>Post Applied For</th>
                                    <th>Department</th>
                                    <th>Submission Date</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <?php
                                    $app_status = $row['status'] ?? 'pending';
                                    $payment_status = $row['payment_status'] ?? 'pending';
                                    $badge_class = match($app_status) {
                                        'shortlisted' => 'success',
                                        'rejected' => 'danger',
                                        'approved' => 'primary',
                                        default => 'warning'
                                    };
                                    $payment_badge_class = match($payment_status) {
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <tr class="application-row">
                                        <td><strong>#<?= $row['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($row['post_applied_for'] ?: 'N/A') ?></td>
                                        <td><?= htmlspecialchars($row['department'] ?: 'N/A') ?></td>
                                        <td><?= date('d M Y, h:i A', strtotime($row['submission_date'])) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $badge_class ?>">
                                                <?= ucfirst($app_status) ?>
                                            </span>
                                        </span>
                                        <td>
                                            <span class="status-badge status-<?= $payment_badge_class ?>">
                                                <i class="fas <?= $payment_status == 'paid' ? 'fa-check-circle' : 'fa-clock' ?> me-1"></i>
                                                <?= ucfirst($payment_status) ?>
                                            </span>
                                        </span>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="view_application_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm view-btn me-1">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <button type="button" class="btn btn-sm print-btn me-1" onclick="printSlip(<?= $row['id'] ?>, '<?= addslashes($row['full_name']) ?>', '<?= addslashes($row['post_applied_for']) ?>')">
                                                    <i class="fas fa-print"></i> Print Slip
                                                </button>
                                                <?php if ($payment_status != 'paid'): ?>
                                                    <button type="button" class="btn btn-sm payment-btn" data-bs-toggle="modal" data-bs-target="#paymentModal" 
                                                            data-id="<?= $row['id'] ?>" 
                                                            data-name="<?= htmlspecialchars($row['full_name']) ?>"
                                                            data-post="<?= htmlspecialchars($row['post_applied_for']) ?>">
                                                        <i class="fas fa-credit-card"></i> Pay Now
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" disabled>
                                                        <i class="fas fa-check"></i> Paid
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </span>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Applications Found</h5>
                        <p class="text-muted mb-3">You haven't submitted any applications yet.</p>
                        <a href="pedoform.php" class="btn btn-pedo-primary">
                            <i class="fas fa-plus me-2"></i>Submit New Application
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-payment">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title" id="paymentModalLabel">
                        <i class="fas fa-credit-card me-2"></i>Application Fee Payment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                        <h4>Application Fee: PKR 1,000/-</h4>
                        <p class="text-muted">Pay your application fee to complete the submission process</p>
                    </div>
                    
                    <form method="POST" action="myapplications.php" id="paymentForm">
                        <input type="hidden" name="application_id" id="payment_app_id">
                        <input type="hidden" name="payment_status" value="paid">
                        <input type="hidden" name="update_payment" value="1">
                        
                        <div class="mb-3">
                            <label class="form-label">Application ID</label>
                            <input type="text" class="form-control" id="display_app_id" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Applicant Name</label>
                            <input type="text" class="form-control" id="display_app_name" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Post Applied For</label>
                            <input type="text" class="form-control" id="display_app_post" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="text" class="form-control" value="PKR 1,000/-" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" required>
                                <option value="">Select payment method</option>
                                <option value="credit_card">Credit / Debit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="jazzcash">JazzCash</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>This is a demo payment. In production, integrate with a real payment gateway.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-pedo-primary" onclick="processPayment()">
                        <i class="fas fa-check-circle me-2"></i>Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
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
        
        // Payment Modal - Set data
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const appId = button.getAttribute('data-id');
                const appName = button.getAttribute('data-name');
                const appPost = button.getAttribute('data-post');
                
                document.getElementById('payment_app_id').value = appId;
                document.getElementById('display_app_id').value = '#' + appId;
                document.getElementById('display_app_name').value = appName;
                document.getElementById('display_app_post').value = appPost;
            });
        }
        
        // Process Payment
        function processPayment() {
            if (confirm('Confirm payment of PKR 1,000/- for this application?')) {
                document.getElementById('paymentForm').submit();
            }
        }
        
        // Print Slip
        function printSlip(appId, applicantName, postApplied) {
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Application Slip - PEDO</title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                    <style>
                        body { padding: 40px; font-family: Arial, sans-serif; }
                        .slip-container { max-width: 600px; margin: 0 auto; border: 2px solid #1a5f7a; border-radius: 15px; padding: 30px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .header img { width: 80px; margin-bottom: 15px; }
                        .header h2 { color: #1a5f7a; margin: 0; }
                        .divider { border-top: 2px dashed #ccc; margin: 20px 0; }
                        .info-row { margin-bottom: 12px; }
                        .info-label { font-weight: bold; width: 150px; display: inline-block; }
                        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; }
                        @media print {
                            body { padding: 0; }
                            .no-print { display: none; }
                        }
                    </style>
                </head>
                <body>
                    <div class="slip-container">
                        <div class="header">
                            <img src="pedo.png" alt="PEDO Logo" onerror="this.style.display='none'">
                            <h2>PEDO Application Slip</h2>
                            <p>Pakhtunkhwa Energy Development Organization</p>
                        </div>
                        <div class="divider"></div>
                        <div class="info-row">
                            <span class="info-label">Application ID:</span>
                            <span>#${appId}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Applicant Name:</span>
                            <span>${applicantName}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Post Applied For:</span>
                            <span>${postApplied}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Submission Date:</span>
                            <span>${new Date().toLocaleDateString()}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Application Fee:</span>
                            <span>PKR 1,000/-</span>
                        </div>
                        <div class="divider"></div>
                        <div class="footer">
                            <p>This is a system generated slip. Please keep it for record.</p>
                            <p>© ${new Date().getFullYear()} PEDO. All rights reserved.</p>
                        </div>
                    </div>
                    <div class="text-center mt-4 no-print">
                        <button onclick="window.print()" class="btn btn-primary">Print</button>
                        <button onclick="window.close()" class="btn btn-secondary">Close</button>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
        }
    </script>
</body>
</html>
