<?php
require_once 'config.php';
require_once 'pedoform_helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    save_temp_uploaded_file('photo', 'photo_path', 'photo');
    save_form_data($_POST);
    if (isset($_POST['save_next'])) {
        if (isset($_POST['save_next'])) {
            set_form_message('Section A, B and C saved.');
        }
        header('Location: pedoform_step2.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PEDO Application Form — Sections A, B & C</title>
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
      <p>Step 1 of 4: Section A, B & C</p>
    </div>

    <?php show_form_message(); ?>

    <form method="POST" action="pedoform.php" enctype="multipart/form-data">
      <div class="section">
        <div class="section-title">A. Eligibility Criteria</div>
        <div class="eligibility-box">
          <p>Is your Qualification, Experience & Age according to the required criteria for the post?</p>
          <div class="radio-group">
            <label><input type="radio" name="eligibility" value="Yes" <?= get_form_value('eligibility') === 'Yes' ? 'checked' : '' ?> required> Yes</label>
            <label><input type="radio" name="eligibility" value="No" <?= get_form_value('eligibility') === 'No' ? 'checked' : '' ?>> No</label>
          </div>
          <div class="note-text">If reply is "Yes" only then proceed further. Otherwise you are not eligible to apply.</div>
        </div>

        <div class="photo-row">
          <div class="photo-instruction">
            <strong>Passport Size Photograph</strong><br>
            Recent (not older than 6 months), blue background, size 1x1.5 inch.<br>
            Paste / upload recent color photograph with gum.
          </div>
          <div class="photo-placeholder">
            <div class="photo-frame" id="photoFrame">
              <span id="photoIcon">📷</span><br>
              <span id="photoText">Photo Area</span>
            </div>
            <input type="file" name="photo" accept="image/*" id="photoUpload" style="margin-top: 8px;">
            <small style="display:block; margin-top:6px;">(JPG/PNG, blue background preferred)</small>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-title">B. Personal Information</div>
        <div class="inline-row">
          <div class="field-group"><label>Full Name</label><input type="text" id="fullNameInput" name="full_name" placeholder="Your full name" value="<?= get_form_value('full_name') ?>" required></div>
          <div class="field-group"><label>Father's Name</label><input type="text" name="father_name" placeholder="Father's name" value="<?= get_form_value('father_name') ?>"></div>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>CNIC</label><input type="text" name="cnic" placeholder="12345-6789012-3" value="<?= get_form_value('cnic') ?>"></div>
          <div class="field-group"><label>Date of Birth</label><input type="date" name="dob" value="<?= get_form_value('dob') ?>"></div>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>Gender</label>
            <select name="gender">
              <option value="">Select gender</option>
              <option value="Male" <?= get_form_value('gender') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= get_form_value('gender') === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= get_form_value('gender') === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
          </div>
          <div class="field-group"><label>Nationality</label><input type="text" name="nationality" placeholder="Nationality" value="<?= get_form_value('nationality') ?>"></div>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>Phone / Mobile</label><input type="text" name="phone" placeholder="Phone number" value="<?= get_form_value('phone') ?>"></div>
          <div class="field-group"><label>Email Address</label><input type="email" name="email" placeholder="Email address" value="<?= get_form_value('email') ?>"></div>
        </div>
        <div class="field-group"><label>Present Address</label><textarea name="present_address" rows="3" placeholder="Present address"><?= get_form_value('present_address') ?></textarea></div>
        <div class="field-group"><label>Permanent Address</label><textarea name="permanent_address" rows="3" placeholder="Permanent address"><?= get_form_value('permanent_address') ?></textarea></div>
      </div>

      <div class="section">
        <div class="section-title">C. Academic Qualifications</div>
        <div class="note-text" style="margin-bottom: 18px;">
          Write exact degree name & major subject as in certificate/transcript.<br>
          Convert grades into marks (O/A Level etc).<br>
          Result declaration date = Year of passing.
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>Degree Level</th><th>Degree/Sanad Title</th><th>Major Subject</th><th>Passing Year</th><th>Obtained Marks/CGPA</th><th>Total Marks/CGPA</th><th>Division</th><th>Board/University</th></tr>
            </thead>
            <tbody>
              <tr>
                <td>Matric (10 Years)</td>
                <td><input type="text" name="matric_degree" placeholder="Degree title" value="<?= get_form_value('matric_degree') ?>"></td>
                <td><input type="text" name="matric_subject" placeholder="Major subject" value="<?= get_form_value('matric_subject') ?>"></td>
                <td><input type="text" name="matric_year" placeholder="Year" value="<?= get_form_value('matric_year') ?>"></td>
                <td><input type="text" name="matric_obtained" placeholder="Obtained" value="<?= get_form_value('matric_obtained') ?>"></td>
                <td><input type="text" name="matric_total" placeholder="Total" value="<?= get_form_value('matric_total') ?>"></td>
                <td><input type="text" name="matric_division" placeholder="Division" value="<?= get_form_value('matric_division') ?>"></td>
                <td><input type="text" name="matric_board" placeholder="Board" value="<?= get_form_value('matric_board') ?>"></td>
              </tr>
              <tr>
                <td>Intermediate / D.A.E (12/13 Years)</td>
                <td><input type="text" name="intermediate_degree" value="<?= get_form_value('intermediate_degree') ?>"></td>
                <td><input type="text" name="intermediate_subject" value="<?= get_form_value('intermediate_subject') ?>"></td>
                <td><input type="text" name="intermediate_year" value="<?= get_form_value('intermediate_year') ?>"></td>
                <td><input type="text" name="intermediate_obtained" value="<?= get_form_value('intermediate_obtained') ?>"></td>
                <td><input type="text" name="intermediate_total" value="<?= get_form_value('intermediate_total') ?>"></td>
                <td><input type="text" name="intermediate_division" value="<?= get_form_value('intermediate_division') ?>"></td>
                <td><input type="text" name="intermediate_board" value="<?= get_form_value('intermediate_board') ?>"></td>
              </tr>
              <tr>
                <td>Bachelor (14 Years)</td>
                <td><input type="text" name="bachelor_degree" value="<?= get_form_value('bachelor_degree') ?>"></td>
                <td><input type="text" name="bachelor_subject" value="<?= get_form_value('bachelor_subject') ?>"></td>
                <td><input type="text" name="bachelor_year" value="<?= get_form_value('bachelor_year') ?>"></td>
                <td><input type="text" name="bachelor_obtained" value="<?= get_form_value('bachelor_obtained') ?>"></td>
                <td><input type="text" name="bachelor_total" value="<?= get_form_value('bachelor_total') ?>"></td>
                <td><input type="text" name="bachelor_division" value="<?= get_form_value('bachelor_division') ?>"></td>
                <td><input type="text" name="bachelor_board" value="<?= get_form_value('bachelor_board') ?>"></td>
              </tr>
              <tr>
                <td>Bachelor (Hons) / Master (16 Years)</td>
                <td><input type="text" name="bachelor_hons_degree" value="<?= get_form_value('bachelor_hons_degree') ?>"></td>
                <td><input type="text" name="bachelor_hons_subject" value="<?= get_form_value('bachelor_hons_subject') ?>"></td>
                <td><input type="text" name="bachelor_hons_year" value="<?= get_form_value('bachelor_hons_year') ?>"></td>
                <td><input type="text" name="bachelor_hons_obtained" value="<?= get_form_value('bachelor_hons_obtained') ?>"></td>
                <td><input type="text" name="bachelor_hons_total" value="<?= get_form_value('bachelor_hons_total') ?>"></td>
                <td><input type="text" name="bachelor_hons_division" value="<?= get_form_value('bachelor_hons_division') ?>"></td>
                <td><input type="text" name="bachelor_hons_board" value="<?= get_form_value('bachelor_hons_board') ?>"></td>
              </tr>
              <tr>
                <td>MS / M.Phil (18 Years)</td>
                <td><input type="text" name="ms_degree" value="<?= get_form_value('ms_degree') ?>"></td>
                <td><input type="text" name="ms_subject" value="<?= get_form_value('ms_subject') ?>"></td>
                <td><input type="text" name="ms_year" value="<?= get_form_value('ms_year') ?>"></td>
                <td><input type="text" name="ms_obtained" value="<?= get_form_value('ms_obtained') ?>"></td>
                <td><input type="text" name="ms_total" value="<?= get_form_value('ms_total') ?>"></td>
                <td><input type="text" name="ms_division" value="<?= get_form_value('ms_division') ?>"></td>
                <td><input type="text" name="ms_board" value="<?= get_form_value('ms_board') ?>"></td>
              </tr>
              <tr>
                <td>Ph.D</td>
                <td><input type="text" name="phd_degree" value="<?= get_form_value('phd_degree') ?>"></td>
                <td><input type="text" name="phd_subject" value="<?= get_form_value('phd_subject') ?>"></td>
                <td><input type="text" name="phd_year" value="<?= get_form_value('phd_year') ?>"></td>
                <td><input type="text" name="phd_obtained" value="<?= get_form_value('phd_obtained') ?>"></td>
                <td><input type="text" name="phd_total" value="<?= get_form_value('phd_total') ?>"></td>
                <td><input type="text" name="phd_division" value="<?= get_form_value('phd_division') ?>"></td>
                <td><input type="text" name="phd_board" value="<?= get_form_value('phd_board') ?>"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="btn-container">
        <button type="submit" name="save_next" value="1" class="btn-secondary">Save & Next</button>
      </div>
    </form>
  </div>

  <script>
    // Handle photo preview on file selection
    const photoUpload = document.getElementById('photoUpload');
    const photoFrame = document.getElementById('photoFrame');
    
    photoUpload.addEventListener('change', function(event) {
      const file = event.target.files[0];
      
      // Validate file exists
      if (!file) {
        return;
      }
      
      // Validate file is an image
      if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file');
        photoUpload.value = '';
        return;
      }
      
      // Validate file size (max 5MB)
      const maxSize = 5 * 1024 * 1024; // 5MB
      if (file.size > maxSize) {
        alert('Image size must be less than 5MB');
        photoUpload.value = '';
        return;
      }
      
      // Read and display the image
      const reader = new FileReader();
      reader.onload = function(e) {
        photoFrame.innerHTML = '<img src="' + e.target.result + '" style="width:100%; height:100%; object-fit:contain; border-radius:8px;">';
      };
      reader.readAsDataURL(file);
    });

    // Display existing photo on page load if it exists
    window.addEventListener('load', function() {
      const existingPhotoPath = '<?= get_form_value('photo_path') ?>';
      if (existingPhotoPath) {
        const img = document.createElement('img');
        img.src = existingPhotoPath;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'contain';
        img.style.borderRadius = '8px';
        photoFrame.innerHTML = '';
        photoFrame.appendChild(img);
      }
    });
  </script>
</body>
</html>
