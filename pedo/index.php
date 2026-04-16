<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PEDO | Energy & Power Department</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="landing-container">
        <div class="landing-card">
            <img src="pedo.png" alt="PEDO Logo" class="landing-logo" 
                 onerror="this.style.background='#1a5f7a';this.style.color='white';this.style.display='flex';this.style.alignItems='center';this.style.justifyContent='center';this.innerHTML='PEDO'">
            
            <div class="landing-header">
                <h1>Pakhtunkhwa Energy Development Organization</h1>
                <h2>Energy & Power Department</h2>
                <p class="text-muted">Government of Khyber Pakhtunkhwa</p>
            </div>
            
            <div class="d-flex gap-3 justify-content-center flex-wrap my-4">
                <a href="login.php" class="btn btn-pedo-primary btn-lg px-4">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Account
                </a>
                <a href="signup.php" class="btn btn-pedo-outline btn-lg px-4">
                    <i class="fas fa-user-plus me-2"></i>Create New Account
                </a>
            </div>
            
            <div class="card bg-light border-0 mt-4">
                <div class="card-body text-start">
                    <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-list me-2 text-success"></i>Before You Apply:</h6>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Ensure you meet eligibility criteria</li>
                        <li><i class="fas fa-check-circle"></i> Prepare scanned copies of documents</li>
                        <li><i class="fas fa-check-circle"></i> Keep passport-size photo ready (blue background)</li>
                        <li><i class="fas fa-check-circle"></i> Have CNIC, educational certificates handy</li>
                    </ul>
                </div>
            </div>
            
            <div class="text-center mt-4 pt-3 border-top">
                <p class="mb-1">
                    <a href="#" class="text-decoration-none me-3"><i class="fas fa-headset me-1"></i>Contact Support</a>
                    <a href="#" class="text-decoration-none"><i class="fas fa-file-pdf me-1"></i>View Guidelines</a>
                </p>
                <p class="text-muted small mt-2 mb-0">© <?= date('Y') ?> PEDO. All rights reserved.</p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
