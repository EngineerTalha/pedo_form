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
    if (isset($_POST['next']) || isset($_POST['save_next'])) {
        if (isset($_POST['save_next'])) {
            set_form_message('Section G saved.');
        }
        header('Location: pedoform_review.php');
        exit;
    }
}

$declarationName = get_form_value('full_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PEDO Application Form — Section G</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div style="text-align: right; padding: 15px; background: #f0f0f0;">
    <a href="logout.php" style="color: #1a5f7a; font-weight: bold; text-decoration: none;">🚪 Logout</a>
  </div>
  <div class="form-container">
    <div class="gov-header">
      <h1>PEDO Application Form</h1>
      <p>Step 3 of 4: Section G</p>
    </div>

    <?php show_form_message(); ?>

    <form method="POST" action="pedoform_step3.php" enctype="multipart/form-data">
      <div class="section">
        <div class="section-title">G. Declaration / Undertaking By The Applicant</div>
        <div class="declaration-box">
          <p>I, <strong><?= $declarationName ?: '______________________' ?></strong>, d/s/w of do hereby solemnly declare and affirm that I have read and understood the instructions and conditions for applying to the post. All the given information is true and correct. Any untrue, false or forged, misrepresentation of information may lead to the cancellation of my candidature for the subject position at any stage and even after the appointment.</p>
        </div>

        <div class="sign-area">
          <div class="field-group"><label>Date</label><input type="date" name="declaration_date" value="<?= get_form_value('declaration_date') ?>"></div>
          <div class="field-group"><label>Thumb Impression (if applicable)</label><input type="file" name="thumb_impression"></div>
          <div class="field-group"><label>Candidate's Signature</label><input type="file" name="signature"></div>
        </div>
      </div>

      <div class="btn-container">
        <button type="submit" name="back" value="1" class="btn-secondary">← Back</button>
        <button type="submit" name="save_next" value="1" class="btn-secondary">Save & Next</button>
        <button type="submit" name="next" value="1">Next</button>
      </div>
    </form>
  </div>
</body>
</html>
