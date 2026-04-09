<?php
require_once 'config.php';
session_start();

$errorMessage = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errorMessage = 'Please fill all fields.';
    } else {
        $stmt = $conn->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id, $db_password);
                $stmt->fetch();
                if ($password === $db_password) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['email'] = $email;
                    $stmt->close();
                    header('Location: pedoform.php');
                    exit;
                }
            }
            $stmt->close();
        }

        $errorMessage = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PEDO | Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <h1>🔐 Login to PEDO Portal</h1>
        <p>Access your application dashboard</p>
      </div>

      <?php if ($errorMessage): ?>
        <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
      <?php endif; ?>

      <form id="loginForm" method="post" action="">
        <div class="form-group">
          <label>📧 Email Address</label>
          <input type="email" id="loginEmail" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label>🔒 Password</label>
          <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
        </div>

        <div class="form-options">
          <label class="checkbox-label">
            <input type="checkbox"> Remember Me
          </label>
          <a href="#" class="forgot-link">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-primary btn-full">Login</button>
      </form>

      <div class="auth-footer">
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
        <p><a href="index.php">← Back to Home</a></p>
      </div>
    </div>
  </div>
</body>
</html>
