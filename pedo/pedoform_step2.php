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
        if (isset($_POST['save_next'])) {
            set_form_message('Section D and E saved.');
        }
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
  <title>PEDO Application Form — Section D & E</title>
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
      <p>Step 2 of 4: Section D and E</p>
    </div>

    <?php show_form_message(); ?>

    <form method="POST" action="pedoform_step2.php" enctype="multipart/form-data">
      <div class="section">
        <div class="section-title">D. Job & Additional Details</div>
        <div class="inline-row">
          <div class="field-group"><label>Post Applied For</label><input type="text" name="post_applied_for" value="<?= get_form_value('post_applied_for') ?>" placeholder="Post applied for"></div>
          <div class="field-group"><label>Department / Section</label><input type="text" name="department" value="<?= get_form_value('department') ?>" placeholder="Department or section"></div>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>District</label><input type="text" name="district" value="<?= get_form_value('district') ?>" placeholder="District"></div>
          <div class="field-group"><label>Preferred Job Location</label><input type="text" name="preferred_job_location" value="<?= get_form_value('preferred_job_location') ?>" placeholder="Preferred location"></div>
        </div>
        <div class="field-group"><label>Source of Information</label><input type="text" name="source_information" value="<?= get_form_value('source_information') ?>" placeholder="Advertisement / Website / Referral"></div>
        <div class="field-group"><label>Currently Employed?</label>
          <select name="currently_employed">
            <option value="">Select</option>
            <option value="Yes" <?= get_form_value('currently_employed') === 'Yes' ? 'selected' : '' ?>>Yes</option>
            <option value="No" <?= get_form_value('currently_employed') === 'No' ? 'selected' : '' ?>>No</option>
          </select>
        </div>
      </div>

      <div class="section">
        <div class="section-title">E. Employment Record (Latest first)</div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>Sr#</th><th>Organization/Employer</th><th>Job Title</th><th>From (Month/Year)</th><th>To (Month/Year)</th><th>Total Years</th><th>Total Months</th></tr>
            </thead>
            <tbody>
              <?php for ($i = 0; $i < 5; $i++): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><input type="text" name="employment_org[]" value="<?= get_form_array_value('employment_org', $i) ?>" placeholder="Employer"></td>
                <td><input type="text" name="employment_title[]" value="<?= get_form_array_value('employment_title', $i) ?>" placeholder="Job title"></td>
                <td><input type="month" name="employment_from[]" value="<?= get_form_array_value('employment_from', $i) ?>"></td>
                <td><input type="month" name="employment_to[]" value="<?= get_form_array_value('employment_to', $i) ?>"></td>
                <td><input type="text" name="employment_years[]" value="<?= get_form_array_value('employment_years', $i) ?>" placeholder="Years"></td>
                <td><input type="text" name="employment_months[]" value="<?= get_form_array_value('employment_months', $i) ?>" placeholder="Months"></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>Total Job Experience (as on closing date)</label><input type="text" name="total_experience" value="<?= get_form_value('total_experience') ?>" placeholder="Years / Months"></div>
          <div class="field-group"><label>Total Relevant Job Experience</label><input type="text" name="relevant_experience" value="<?= get_form_value('relevant_experience') ?>" placeholder="Relevant experience"></div>
          <div class="field-group"><label>Total Specific Job Experience (if required)</label><input type="text" name="specific_experience" value="<?= get_form_value('specific_experience') ?>" placeholder="Specific experience"></div>
        </div>
      </div>

      <div class="btn-container">
        <button type="submit" name="back" value="1" class="btn-secondary">← Back</button>
        <button type="submit" name="save_next" value="1" class="btn-secondary">Save & Next</button>
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
