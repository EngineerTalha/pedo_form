<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['application_data'])) {
    header('Location: pedoform.php');
    exit;
}

// Generate a unique submission token to prevent double submissions
if (empty($_SESSION['submission_token'])) {
    $_SESSION['submission_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_form_data($_POST);
    if (isset($_POST['back'])) {
        header('Location: pedoform_step3.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PEDO Application Form — Final Review</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div style="text-align: right; padding: 15px; background: #f0f0f0;">
    <a href="logout.php" style="color: #1a5f7a; font-weight: bold; text-decoration: none;">🚪 Logout</a>
  </div>
  <div class="form-container">
    <div class="gov-header">
      <div class="gov-header-title-row">
        <img src="pedo.png" alt="PEDO Logo" class="gov-header-logo">
        <h1>PEDO Application Form</h1>
        <img src="KP_logo.png" alt="KP Logo" class="gov-header-logo">
      </div>
      <p>Step 4 of 4: Section F — Final Review & Submission</p>
    </div>

    <?php show_form_message(); ?>

    <form method="POST" action="pedoform_review.php" enctype="multipart/form-data">
      <input type="hidden" name="submission_token" value="<?= $_SESSION['submission_token'] ?>">
      <div class="section">
        <div class="section-title">F. Final Review & Submission</div>
        <div class="field-group"><label>Ready to Join</label>
          <select name="ready_to_join">
            <option value="">Select readiness</option>
            <option value="Immediately" <?= get_form_value('ready_to_join') === 'Immediately' ? 'selected' : '' ?>>Immediately</option>
            <option value="Within 1 Month" <?= get_form_value('ready_to_join') === 'Within 1 Month' ? 'selected' : '' ?>>Within 1 Month</option>
            <option value="After Notice Period" <?= get_form_value('ready_to_join') === 'After Notice Period' ? 'selected' : '' ?>>After Notice Period</option>
          </select>
        </div>
        <div class="field-group"><label>Additional Information</label><textarea name="additional_information" rows="4" placeholder="Any other information you wish to provide"><?= get_form_value('additional_information') ?></textarea></div>
        <div class="checkbox-row">
          <input type="checkbox" id="agreeTerms" name="agree_terms" value="1" <?= get_form_value('agree_terms') === '1' ? 'checked' : '' ?> />
          <label for="agreeTerms">I certify that all information provided in this application is true and correct.</label>
        </div>
        <div class="note-text" style="margin-top: 16px;">Save & Next will preserve the current review data and continue. Submit Application will send everything to the server.</div>
      </div>

      <div class="btn-container">
        <button type="submit" name="back" value="1" class="btn-secondary">← Back</button>
        <button type="submit" formaction="submit_form.php" name="next" value="1">Save &amp; Next</button>
        <button type="submit" formaction="submit_form.php" name="submit_application" value="1">Submit Application</button>
      </div>
    </form>
  </div>
  <script>
    (function() {
      if (window.history && history.pushState) {
        history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
          history.pushState(null, null, window.location.href);
        });
      }
    })();
  </script>
</body>
</html>
