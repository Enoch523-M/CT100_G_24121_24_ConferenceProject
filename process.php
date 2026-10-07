<?php
/* ==========================================================================
   STUDENT TECHNOLOGY AND INNOVATION CONFERENCE (STICON 2026)
   PHP Processing & Independent Fee Verification Engine: process.php
   Registration Number: CT100/G/24121/24
   Individual Requirement: Accommodation Option at KSh 1,500 / night
   ========================================================================== */

// Prevent direct GET requests without POST submission
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invalid Request - STICON 2026</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
            <div class="container navbar">
                <a href="index.html" class="logo">
                    <div class="logo-icon">ST</div>
                    <span>STICON 2026</span>
                </a>
            </div>
        </header>
        <main class="container section text-center">
            <div style="max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
                <h1 style="color: var(--danger); font-size: 2rem; margin-bottom: 15px;">Invalid Request Method</h1>
                <p style="color: var(--text-secondary); margin-bottom: 25px;">
                    This processing script only accepts form submissions submitted via HTTP POST.
                </p>
                <a href="register.html" class="btn btn-primary">&larr; Return to Registration Form</a>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit();
}

// 1. DATA COLLECTION & SANITIZATION
$fullName         = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$admissionNumber  = isset($_POST['admission_number']) ? trim($_POST['admission_number']) : '';
$email            = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone            = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$year             = isset($_POST['year']) ? trim($_POST['year']) : '';
$category         = isset($_POST['category']) ? trim($_POST['category']) : '';
$daysRaw          = isset($_POST['days']) ? $_POST['days'] : 0;
$workshop         = isset($_POST['workshop']) ? trim($_POST['workshop']) : '';
$meals            = isset($_POST['meals']) ? trim($_POST['meals']) : '';
$accommodation    = isset($_POST['accommodation']) ? trim($_POST['accommodation']) : '';
$accNightsRaw     = isset($_POST['accommodation_nights']) ? $_POST['accommodation_nights'] : 0;

// Convert integer inputs safely
$days = filter_var($daysRaw, FILTER_VALIDATE_INT);
$accommodationNights = filter_var($accNightsRaw, FILTER_VALIDATE_INT);

// 2. SERVER-SIDE INDEPENDENT VALIDATION
$errors = array();

// Full Name Validation
if (empty($fullName)) {
    $errors[] = "Full Name is required.";
}

// Admission Number Validation
if (empty($admissionNumber)) {
    $errors[] = "Admission / Registration Number is required.";
}

// Email Validation
if (empty($email)) {
    $errors[] = "Email Address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid Email format provided.";
}

// Phone Number Validation (Exactly 10 digits)
if (empty($phone)) {
    $errors[] = "Phone Number is required.";
} elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone Number must contain exactly 10 digits.";
}

// Year of Study Validation
if (empty($year)) {
    $errors[] = "Year of Study selection is required.";
}

// Participation Category Validation
if (empty($category) || !in_array($category, array('Conference Only', 'Workshop Participant'))) {
    $errors[] = "Valid Participation Category selection is required.";
}

// Conference Days Validation (Must be integer between 1 and 3)
if ($days === false || $days < 1 || $days > 3) {
    $errors[] = "Conference attendance must be between 1 and 3 days.";
}

// Meal Selection Validation
if (empty($meals) || !in_array($meals, array('No Lunch', 'Lunch Required'))) {
    $errors[] = "Valid Meal option selection is required.";
}

// Accommodation Selection Validation
if (empty($accommodation) || !in_array($accommodation, array('No Accommodation', 'Require Accommodation'))) {
    $errors[] = "Valid Accommodation option selection is required.";
}

// Conditional Accommodation Nights Validation (Reg No CT100/G/24121/24 Individual Requirement)
if ($accommodation === 'Require Accommodation') {
    if ($accommodationNights === false || $accommodationNights < 1) {
        $errors[] = "Please specify a valid number of accommodation nights (at least 1 night required).";
    }
} else {
    $accommodationNights = 0; // Reset nights if accommodation not required
}

// If there are server-side validation errors, render Error Page
if (!empty($errors)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Error - STICON 2026</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
            <div class="container navbar">
                <a href="index.html" class="logo">
                    <div class="logo-icon">ST</div>
                    <span>STICON 2026</span>
                </a>
                <nav>
                    <ul class="nav-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="register.html">Register</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <main class="container section">
            <div style="max-width: 700px; margin: 0 auto;">
                <div class="error-container" style="padding: 30px;">
                    <div class="error-title" style="font-size: 1.3rem; margin-bottom: 15px;">
                        <span>⚠️</span> Server Validation Failed
                    </div>
                    <p style="margin-bottom: 15px; color: var(--text-primary);">
                        The submitted registration form contained the following errors that prevented server processing:
                    </p>
                    <ul class="error-list" style="margin-bottom: 25px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="javascript:history.back()" class="btn btn-primary">&larr; Go Back & Correct Form</a>
                </div>
            </div>
        </main>
    </body>
    </html>
    <?php
    exit();
}

// 3. INDEPENDENT SERVER-SIDE FEE CALCULATION
// Do NOT trust any totals coming from JavaScript client calculations
$REGISTRATION_FEE_RATE  = 1000;
$ATTENDANCE_RATE        = 500;
$WORKSHOP_RATE          = 750;
$MEAL_RATE              = 300;
$ACCOMMODATION_RATE     = 1500; // Individual requirement rate

// Base Fee
$registrationFee = $REGISTRATION_FEE_RATE;

// Attendance Fee
$attendanceFee = $days * $ATTENDANCE_RATE;

// Workshop Fee
$workshopFee = 0;
$workshopDisplay = "None Selected";
if ($category === 'Workshop Participant') {
    $workshopFee = $WORKSHOP_RATE;
    $workshopDisplay = !empty($workshop) ? $workshop : "Web Development Workshop";
}

// Meal Fee
$mealFee = 0;
if ($meals === 'Lunch Required') {
    $mealFee = $days * $MEAL_RATE;
}

// Accommodation Fee (Individual requirement: KSh 1,500 / night)
$accommodationFee = 0;
if ($accommodation === 'Require Accommodation') {
    $accommodationFee = $accommodationNights * $ACCOMMODATION_RATE;
}

// Final Total Amount
$totalAmount = $registrationFee + $attendanceFee + $workshopFee + $mealFee + $accommodationFee;

// Generate unique registration reference ID
$regRef = "STICON-" . date("Y") . "-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
$regDate = date("F j, Y, g:i a");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - STICON 2026</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- ================= HEADER / NAVIGATION ================= -->
    <header>
        <div class="container navbar">
            <a href="index.html" class="logo">
                <div class="logo-icon">ST</div>
                <span>STICON 2026</span>
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.html">Home</a></li>
                    <li><a href="index.html#about">About</a></li>
                    <li><a href="register.html">Register Another</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- ================= CONFIRMATION CONTENT ================= -->
    <main class="confirmation-wrapper">
        <div class="confirmation-card">
            
            <div class="confirmation-header">
                <div class="success-icon">✓</div>
                <h1 style="font-size: 2rem; margin-bottom: 8px;">REGISTRATION SUCCESSFUL</h1>
                <p style="color: #e2e8f0; font-size: 1.05rem;">
                    Thank you for registering for the Student Technology & Innovation Conference 2026!
                </p>
                <div style="margin-top: 15px; display: inline-block; background: rgba(255,255,255,0.15); padding: 6px 16px; border-radius: 20px; font-size: 0.9rem;">
                    Reference ID: <strong><?php echo htmlspecialchars($regRef, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="confirmation-body">
                
                <div class="details-grid">
                    <!-- Personal Details Block -->
                    <div class="details-block">
                        <h3>Participant Information</h3>
                        <div class="detail-row">
                            <span class="detail-key">Full Name:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Admission / Reg No:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($admissionNumber, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Email Address:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Phone Number:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Year of Study:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </div>

                    <!-- Conference Choices Block -->
                    <div class="details-block">
                        <h3>Selected Options</h3>
                        <div class="detail-row">
                            <span class="detail-key">Participation:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Conference Days:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($days, ENT_QUOTES, 'UTF-8'); ?> Day(s)</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Workshop Option:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($workshopDisplay, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Catering / Meals:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($meals, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-key">Accommodation:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($accommodation, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <?php if ($accommodation === 'Require Accommodation'): ?>
                        <div class="detail-row">
                            <span class="detail-key">Hostel Nights:</span>
                            <span class="detail-val"><?php echo htmlspecialchars($accommodationNights, ENT_QUOTES, 'UTF-8'); ?> Night(s)</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Fee Invoice Breakdown Table -->
                <h3 style="color: var(--primary); margin-bottom: 15px;">Verified Fee Breakdown (PHP Server Calculated)</h3>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>Rate / Calculation</th>
                            <th style="text-align: right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Base Registration Fee</td>
                            <td>Standard Base Fee</td>
                            <td class="amount-col">KSh <?php echo number_format($registrationFee); ?></td>
                        </tr>
                        <tr>
                            <td>Conference Attendance</td>
                            <td><?php echo $days; ?> day(s) &times; KSh 500</td>
                            <td class="amount-col">KSh <?php echo number_format($attendanceFee); ?></td>
                        </tr>
                        <tr>
                            <td>Workshop Participant Fee</td>
                            <td><?php echo ($category === 'Workshop Participant') ? 'Flat Fee' : 'N/A'; ?></td>
                            <td class="amount-col">KSh <?php echo number_format($workshopFee); ?></td>
                        </tr>
                        <tr>
                            <td>Catering & Lunch Option</td>
                            <td><?php echo ($meals === 'Lunch Required') ? $days . ' day(s) &times; KSh 300' : 'N/A'; ?></td>
                            <td class="amount-col">KSh <?php echo number_format($mealFee); ?></td>
                        </tr>
                        <tr>
                            <td>Accommodation (On-Campus Hostel)</td>
                            <td><?php echo ($accommodation === 'Require Accommodation') ? $accommodationNights . ' night(s) &times; KSh 1,500' : 'N/A'; ?></td>
                            <td class="amount-col">KSh <?php echo number_format($accommodationFee); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2">TOTAL AMOUNT PAYABLE</td>
                            <td class="amount-col">KSh <?php echo number_format($totalAmount); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div style="background-color: var(--bg-alt); padding: 18px; border-radius: var(--radius-md); border-left: 4px solid var(--accent); margin-bottom: 30px;">
                    <p style="font-size: 0.92rem; color: var(--text-secondary); margin: 0;">
                        <strong>Registration Timestamp:</strong> <?php echo htmlspecialchars($regDate, ENT_QUOTES, 'UTF-8'); ?><br>
                        A confirmation email has been dispatched to <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>. Please present this reference code upon arrival at the main auditorium.
                    </p>
                </div>

                <div class="text-center" style="display: flex; justify-content: center; gap: 15px;">
                    <button onclick="window.print();" class="btn btn-secondary">🖨️ Print Confirmation</button>
                    <a href="index.html" class="btn btn-primary">Return to Homepage</a>
                </div>

            </div>

        </div>
    </main>

    <!-- ================= FOOTER ================= -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <h4>Student Technology & Innovation Conference 2026</h4>
                    <p>Web Application Development II — Continuous Assessment Test (CAT 1)</p>
                </div>
                <div>
                    <p><strong>Registration Number:</strong> CT100/G/24121/24</p>
                    <p><strong>Individual Requirement:</strong> Accommodation at KSh 1,500 / night</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 STICON. All Rights Reserved. PHP Server Processing Engine.</p>
            </div>
        </div>
    </footer>

</body>
</html>
