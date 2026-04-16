<?php
require_once 'config.php';

header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');

$errorMessage = '';
$full_name = '';
$email = '';
$cnic = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cnic = trim($_POST['cnic'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '' || $email === '' || $cnic === '' || $phone === '' || $password === '' || $confirm_password === '') {
        $errorMessage = 'Please fill all fields.';
    } elseif ($password !== $confirm_password) {
        $errorMessage = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $errorMessage = 'Password must be at least 8 characters.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errorMessage = 'An account with that email already exists.';
            }
            $stmt->close();
        } else {
            $errorMessage = 'Database error: ' . $conn->error;
        }

        if ($errorMessage === '') {
            $stmt = $conn->prepare('INSERT INTO users (full_name, email, cnic, phone, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
            if ($stmt) {
                $stmt->bind_param('sssss', $full_name, $email, $cnic, $phone, $password);
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    header('Location: login.php');
                    exit;
                } else {
                    $errorMessage = 'Error creating account: ' . $stmt->error;
                    $stmt->close();
                }
            } else {
                $errorMessage = 'Database error: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDO | Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-user-plus fa-3x text-success mb-3"></i>
                <h1>Create PEDO Account</h1>
                <p class="text-muted">Register to apply for positions</p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                    <input type="text" name="full_name" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($full_name); ?>" 
                           placeholder="Enter your full name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-id-card me-2"></i>CNIC Number</label>
                    <input type="text" name="cnic" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($cnic); ?>" 
                           placeholder="12345-1234567-1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-phone me-2"></i>Phone Number</label>
                    <input type="tel" name="phone" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($phone); ?>" 
                           placeholder="03XXXXXXXXX" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" 
                           placeholder="Minimum 8 characters" required>
                    <small class="text-muted">Must be at least 8 characters</small>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-lock me-2"></i>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control form-control-lg" 
                           placeholder="Re-enter your password" required>
                </div>

                <button type="submit" class="btn btn-pedo-primary w-100 btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1">Already have an account? <a href="login.php" class="text-decoration-none">Login here</a></p>
                <p class="mb-0"><a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Home</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
