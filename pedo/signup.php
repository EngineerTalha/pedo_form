<?php
require_once 'config.php';

header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');

$successMessage = '';
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
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1> Create PEDO Account</h1>
        <p>Register to apply for positions</p>
      </div>

      <?php if ($errorMessage): ?>
        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
      <?php endif; ?>

      <?php if ($successMessage): ?>
        <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
      <?php endif; ?>

      <form id="signupForm" method="post" action="">
        <div class="form-group">
          <label>👤 Full Name</label>
          <input type="text" id="fullName" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
          <label>📧 Email Address</label>
          <input type="email" id="signupEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label>🆔 CNIC Number</label>
          <input type="text" id="cnic" name="cnic" value="<?php echo htmlspecialchars($cnic); ?>" placeholder="12345-1234567-1" required>
        </div>

        <div class="form-group">
          <label>📞 Phone Number</label>
          <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="03XXXXXXXXX" required>
        </div>

        <div class="form-group">
          <label>🔒 Password</label>
          <input type="password" id="signupPassword" name="password" placeholder="Minimum 8 characters" required>
          <small class="hint">Must be at least 8 characters</small>
        </div>

        <div class="form-group">
          <label>🔒 Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter your password" required>
        </div>

        <button type="submit" class="btn-primary3 btn-full">Create Account</button>
      </form>

      <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
        <p><a href="index.php">← Back to Home</a></p>
      </div>
    </div>
  </div>
</body>
</html>
