<?php
require_once 'config.php';

header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

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
                    header('Location: dashboard.php');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-lock fa-3x text-success mb-3"></i>
                <h1>Login to PEDO Portal</h1>
                <p class="text-muted">Access your application dashboard</p>
            </div>

            <?php if ($errorMessage): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($errorMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope me-2"></i>Email Address</label>
                    <input type="email" name="email" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" 
                           placeholder="Enter your password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <a href="#" class="text-decoration-none">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-pedo-primary w-100 btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1">Don't have an account? <a href="signup.php" class="text-decoration-none">Sign up here</a></p>
                <p class="mb-0"><a href="index.php" class="text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Home</a></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
