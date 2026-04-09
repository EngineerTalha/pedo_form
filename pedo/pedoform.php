<?php
session_start();
session_regenerate_id(true);

header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sun, 19 Nov 1978 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s T'));
header('ETag: "' . md5(time()) . '"');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$session_token = md5($_SESSION['user_id'] . session_id());
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>PEDO Application Data Form | Pakhtunkhwa Energy Development Organization</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div style="text-align: right; padding: 15px; background: #f0f0f0;">
    <a href="logout.php" style="color: #1a5f7a; font-weight: bold; text-decoration: none;">🚪 Logout</a>
  </div>
  <div class="form-container">
    <!-- Your existing header content remains the same -->
    
    <!-- IMPORTANT: Add enctype and action to your form -->
    <form id="pedoApplicationForm" action="submit_form.php" method="POST" enctype="multipart/form-data">
      
      <!-- A. Eligibility Criteria -->
      <div class="section">
        <div class="section-title">A. Eligibility Criteria</div>
        <div class="eligibility-box">
          <p>Is your Qualification, Experience & Age according to the required criteria for the post?</p>
          <div class="radio-group">
            <label><input type="radio" name="eligibility" value="Yes" required> Yes</label>
            <label><input type="radio" name="eligibility" value="No"> No</label>
          </div>
          <div class="note-text">If reply is "Yes" only then proceed further. Otherwise you are not eligible to apply.</div>
        </div>

        <!-- Photo Section with Live Preview -->
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
            <!-- IMPORTANT: Added name="photo" for file upload -->
            <input type="file" name="photo" accept="image/*" id="photoUpload" style="margin-top: 8px;">
            <small style="display:block; margin-top:6px;">(JPG/PNG, blue background preferred)</small>
          </div>
        </div>
      </div>

      <!-- C. Academic Qualification -->
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
                <td><input type="text" name="matric_degree" placeholder="Degree title"></td>
                <td><input type="text" name="matric_subject" placeholder="Major subject"></td>
                <td><input type="text" name="matric_year" placeholder="Year"></td>
                <td><input type="text" name="matric_obtained" placeholder="Obtained"></td>
                <td><input type="text" name="matric_total" placeholder="Total"></td>
                <td><input type="text" name="matric_division" placeholder="Division"></td>
                <td><input type="text" name="matric_board" placeholder="Board"></td>
              </tr>
              <tr>
                <td>Intermediate / D.A.E (12/13 Years)</td>
                <td><input type="text" name="intermediate_degree"></td>
                <td><input type="text" name="intermediate_subject"></td>
                <td><input type="text" name="intermediate_year"></td>
                <td><input type="text" name="intermediate_obtained"></td>
                <td><input type="text" name="intermediate_total"></td>
                <td><input type="text" name="intermediate_division"></td>
                <td><input type="text" name="intermediate_board"></td>
              </tr>
              <tr>
                <td>Bachelor (14 Years)</td>
                <td><input type="text" name="bachelor_degree"></td>
                <td><input type="text" name="bachelor_subject"></td>
                <td><input type="text" name="bachelor_year"></td>
                <td><input type="text" name="bachelor_obtained"></td>
                <td><input type="text" name="bachelor_total"></td>
                <td><input type="text" name="bachelor_division"></td>
                <td><input type="text" name="bachelor_board"></td>
              </tr>
              <tr>
                <td>Bachelor (Hons) / Master (16 Years)</td>
                <td><input type="text" name="bachelor_hons_degree"></td>
                <td><input type="text" name="bachelor_hons_subject"></td>
                <td><input type="text" name="bachelor_hons_year"></td>
                <td><input type="text" name="bachelor_hons_obtained"></td>
                <td><input type="text" name="bachelor_hons_total"></td>
                <td><input type="text" name="bachelor_hons_division"></td>
                <td><input type="text" name="bachelor_hons_board"></td>
              </tr>
              <tr>
                <td>MS / M.Phil (18 Years)</td>
                <td><input type="text" name="ms_degree"></td>
                <td><input type="text" name="ms_subject"></td>
                <td><input type="text" name="ms_year"></td>
                <td><input type="text" name="ms_obtained"></td>
                <td><input type="text" name="ms_total"></td>
                <td><input type="text" name="ms_division"></td>
                <td><input type="text" name="ms_board"></td>
              </tr>
              <tr>
                <td>Ph.D</td>
                <td><input type="text" name="phd_degree"></td>
                <td><input type="text" name="phd_subject"></td>
                <td><input type="text" name="phd_year"></td>
                <td><input type="text" name="phd_obtained"></td>
                <td><input type="text" name="phd_total"></td>
                <td><input type="text" name="phd_division"></td>
                <td><input type="text" name="phd_board"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- E. Employment Record -->
      <div class="section">
        <div class="section-title">E. Employment Record (Latest first)</div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr><th>Sr#</th><th>Organization/Employer</th><th>Job Title</th><th>From (Month/Year)</th><th>To (Month/Year)</th><th>Total Years</th><th>Total Months</th></tr>
            </thead>
            <tbody>
              <?php for($i=0; $i<5; $i++): ?>
              <tr>
                <td><?php echo $i+1; ?></td>
                <td><input type="text" name="employment_org[]" placeholder="Employer"></td>
                <td><input type="text" name="employment_title[]" placeholder="Job title"></td>
                <td><input type="month" name="employment_from[]"></td>
                <td><input type="month" name="employment_to[]"></td>
                <td><input type="text" name="employment_years[]" placeholder="Years"></td>
                <td><input type="text" name="employment_months[]" placeholder="Months"></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
        <div class="inline-row">
          <div class="field-group"><label>09. Total Job Experience (as on closing date):</label><input type="text" name="total_experience" placeholder="Years / Months"></div>
          <div class="field-group"><label>10. Total Relevant Job Experience:</label><input type="text" name="relevant_experience" placeholder="Relevant experience"></div>
          <div class="field-group"><label>11. Total Specific Job Experience (if required):</label><input type="text" name="specific_experience" placeholder="Specific experience"></div>
        </div>
      </div>

      <!-- G. Declaration / Undertaking -->
      <div class="section">
        <div class="section-title">G. Declaration / Undertaking By The Applicant</div>
        <div class="declaration-box">
          <p>I, <input type="text" name="full_name" placeholder="Your Full Name" style="width: auto; display: inline-block; width: 240px;">, d/s/w of do hereby solemnly declare and affirm that I have read and understood the instructions and conditions for applying to the post. All the given information is true and correct. Any untrue, false or forged, misrepresentation of information may lead to the cancellation of my candidature for the subject position at any stage and even after the appointment.</p>
        </div>

        <div class="sign-area">
          <div class="field-group"><label> Date:</label><input type="date" name="declaration_date"></div>
          <div class="field-group"><label> Thumb Impression (if applicable):</label><input type="file" name="thumb_impression" placeholder="Left/Right thumb impression or N/A"></div>
          <div class="field-group"><label> Candidate's Signature:</label><input type="file" name="signature" placeholder="Sign here"></div>
        </div>
      </div>

      <div class="btn-container">
        <button type="submit" style="background: #1a5f7a;">✓ Submit Application</button>
        <button type="reset" style="background: #5f6c7a; margin-left: 12px;">⟳ Reset Form</button>
      </div>
      <footer>
        PEDO — Government of Khyber Pakhtunkhwa | Energy & Power Department<br>
        Developed by Engr.Mohib Wadood | For any queries: <a href="mailto:mohibwadood.se@gmail.com">mohibwadood.se@gmail.com</a>
      </footer>
    </form>
  </div>

  <script>
    // Verify session is still active on page load
    window.addEventListener('load', function() {
      const checkSession = async () => {
        try {
          const response = await fetch('check_session.php');
          if (!response.ok || response.status === 401) {
            window.location.href = 'login.php';
          }
        } catch (error) {
          console.error('Session check failed:', error);
        }
      };
      checkSession();
    });

    // Disable browser back/forward cache (bfcache)
    window.addEventListener('pagehide', function(event) {
      if (event.persisted) {
        console.log('Page may be cached in bfcache');
      }
    });
  </script>

  <script>
    // Photo preview script remains the same
    const photoUpload = document.getElementById('photoUpload');
    const photoFrame = document.getElementById('photoFrame');
    const photoIcon = document.getElementById('photoIcon');
    const photoText = document.getElementById('photoText');

    photoUpload.addEventListener('change', function(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const existingImg = photoFrame.querySelector('img');
            if (existingImg) existingImg.remove();
            photoIcon.style.display = 'none';
            photoText.style.display = 'none';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.alt = 'Passport Photo';
            photoFrame.appendChild(img);
          };
          reader.readAsDataURL(file);
        } else {
          alert('Please select an image file (JPG or PNG)');
          photoUpload.value = '';
        }
      }
    });
  </script>
</body>
</html>