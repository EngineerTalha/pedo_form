<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check for duplicate submission
$is_duplicate = isset($_SESSION['duplicate_submission']) && $_SESSION['duplicate_submission'];
unset($_SESSION['duplicate_submission']);

// Check if there was a successful submission
if (!isset($_SESSION['submission_success']) || !$_SESSION['submission_success']) {
    header('Location: pedoform_review.php');
    exit;
}

$application_id = $_SESSION['application_id'] ?? null;

// Clear the submission success flag so this page can't be revisited
unset($_SESSION['submission_success']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submission Successful</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      text-align: center;
      padding: 50px;
      background: #f5f5f5;
    }
    .success-container {
      background: white;
      border-radius: 8px;
      padding: 40px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      max-width: 600px;
      margin: 0 auto;
    }
    .success-icon {
      font-size: 48px;
      color: #4CAF50;
      margin-bottom: 20px;
    }
    .success-title {
      color: #4CAF50;
      font-size: 28px;
      margin-bottom: 15px;
      font-weight: bold;
    }
    .success-message {
      font-size: 18px;
      color: #333;
      margin-bottom: 25px;
      line-height: 1.6;
    }
    .application-id {
      background: #f0f0f0;
      padding: 15px;
      border-left: 4px solid #1a5f7a;
      margin-bottom: 30px;
      text-align: left;
      border-radius: 4px;
    }
    .application-id label {
      font-weight: bold;
      color: #1a5f7a;
      display: block;
      margin-bottom: 5px;
    }
    .application-id span {
      font-size: 24px;
      color: #1a5f7a;
      font-family: 'Courier New', monospace;
    }
    .btn-container {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
    }
    .btn {
      padding: 12px 30px;
      background: #1a5f7a;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-size: 16px;
      transition: background 0.3s;
    }
    .btn:hover {
      background: #0d3a4a;
    }
    .btn-secondary {
      background: #666;
    }
    .btn-secondary:hover {
      background: #444;
    }
    .note {
      margin-top: 20px;
      padding: 15px;
      background: #fff3cd;
      color: #856404;
      border-radius: 4px;
      font-size: 14px;
    }
  </style>
  <?php if ($is_duplicate): ?>
  <script>
    window.addEventListener('DOMContentLoaded', function() {
      alert('Application already submitted');
    });
  </script>
  <?php endif; ?>
</head>
<body>
  <div style="text-align: right; padding: 15px; background: #f0f0f0; margin: -50px -50px 30px -50px;">
    <a href="logout.php" style="color: #1a5f7a; font-weight: bold; text-decoration: none;">🚪 Logout</a>
  </div>

  <div class="success-container">
    <div class="success-icon">✓</div>
    <div class="success-title"><?php echo $is_duplicate ? 'Application Already Submitted' : 'Application Submitted Successfully!'; ?></div>
    
    <div class="success-message">
      <?php if ($is_duplicate): ?>
        <p>It looks like you have already submitted an application.</p>
        <p>Duplicate submissions are not allowed.</p>
      <?php else: ?>
        <p>Thank you for submitting your application.</p>
        <p>Your application has been recorded in our system.</p>
      <?php endif; ?>
    </div>

    <?php if ($application_id): ?>
      <div class="application-id">
        <label>Application ID:</label>
        <span>#<?php echo htmlspecialchars($application_id, ENT_QUOTES); ?></span>
      </div>
    <?php endif; ?>

    <div class="note">
      ℹ️ Please save your Application ID for your records. You can use it to track your application status.
    </div>

    <div class="btn-container">
      <a href="view_applications.php" class="btn">View My Applications</a>
      <?php if (!$is_duplicate): ?>
        <a href="pedoform.php" class="btn btn-secondary">Submit Another Application</a>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
