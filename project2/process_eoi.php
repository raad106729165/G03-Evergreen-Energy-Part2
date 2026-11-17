<?php
/*
 * process_eoi.php - validates an application and stores it in the eoi table.
 * Group G03, COS10026 Part 2.
 *
 * - Cannot be opened directly: anything other than a POST from apply.php
 *   is redirected back to the form.
 * - Every field is trimmed, stripped of slashes and HTML-escaped, then
 *   checked against the rules from the Part 1 form.
 * - Creates the eoi table if it does not exist.
 * - Uses a prepared statement for the INSERT.
 * - On success shows the new EOInumber; on failure sends the applicant
 *   back to apply.php with their answers and a list of errors.
 */
require_once "functions.inc";

// 1. Block direct URL access.
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST["jobref"], $_POST["firstname"], $_POST["lastname"])) {
    header("Location: apply.php");
    exit();
}

// 2. Reject forged cross-site submissions.
if (!csrf_valid($_POST["csrf_token"] ?? "")) {
    $_SESSION["eoi_errors"] = ["Your session expired. Please submit the form again."];
    header("Location: apply.php");
    exit();
}

// 3. Sanitise every field.
$jobref      = strtoupper(sanitise_input($_POST["jobref"]));
$firstname   = sanitise_input($_POST["firstname"]);
$lastname    = sanitise_input($_POST["lastname"]);
$dob         = sanitise_input($_POST["dob"] ?? "");
$gender      = sanitise_input($_POST["gender"] ?? "");
$street      = sanitise_input($_POST["street"] ?? "");
$suburb      = sanitise_input($_POST["suburb"] ?? "");
$state       = strtoupper(sanitise_input($_POST["state"] ?? ""));
$postcode    = sanitise_input($_POST["postcode"] ?? "");
$email       = sanitise_input($_POST["email"] ?? "");
$phone       = str_replace(" ", "", sanitise_input($_POST["phone"] ?? ""));
$otherskills = sanitise_input($_POST["otherskills"] ?? "");
$skills_in   = (isset($_POST["skills"]) && is_array($_POST["skills"])) ? $_POST["skills"] : [];
$skills      = array_map("sanitise_input", $skills_in);

$allowed_skills  = ["html5", "css3", "accessibility", "git", "teamwork"];
$allowed_genders = ["female", "male", "undisclosed"];
// First digit(s) each state's postcodes start with.
$state_postcodes = [
    "VIC" => ["3", "8"], "NSW" => ["1", "2"], "QLD" => ["4", "9"], "NT" => ["0"],
    "WA"  => ["6"],      "SA"  => ["5"],      "TAS" => ["7"],      "ACT" => ["0", "2"],
];

$errors = [];
$conn   = db_connect();
if (!$conn) {
    $errors[] = "We could not reach our database. Please try again later.";
}

// 4. Validate.
// Job reference: 5 letters/digits and must exist in the jobs table.
if ($jobref === "") {
    $errors[] = "Please select a job reference number.";
} elseif (!preg_match('/^[A-Z0-9]{5}$/', $jobref)) {
    $errors[] = "The job reference number must be exactly 5 letters or digits.";
} elseif ($conn) {
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM jobs WHERE job_ref = ?");
    mysqli_stmt_bind_param($stmt, "s", $jobref);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) === 0) {
        $errors[] = "That job reference number does not match any current job.";
    }
    mysqli_stmt_close($stmt);
}

if ($firstname === "") {
    $errors[] = "Please enter your first name.";
} elseif (!preg_match('/^[A-Za-z]{1,20}$/', $firstname)) {
    $errors[] = "First name must be letters only, up to 20 characters.";
}

if ($lastname === "") {
    $errors[] = "Please enter your last name.";
} elseif (!preg_match('/^[A-Za-z]{1,20}$/', $lastname)) {
    $errors[] = "Last name must be letters only, up to 20 characters.";
}

// Date of birth: accepts yyyy-mm-dd (date picker) or dd/mm/yyyy, age 15 to 80.
$dob_sql = "";
if ($dob === "") {
    $errors[] = "Please enter your date of birth.";
} else {
    $date = DateTime::createFromFormat("!Y-m-d", $dob) ?: DateTime::createFromFormat("!d/m/Y", $dob);
    $date_errors = DateTime::getLastErrors();
    if (!$date || ($date_errors && ($date_errors["warning_count"] || $date_errors["error_count"]))) {
        $errors[] = "Date of birth must be a real date (dd/mm/yyyy).";
    } else {
        $age = $date->diff(new DateTime("today"))->y;
        if ($date > new DateTime("today") || $age < 15 || $age > 80) {
            $errors[] = "Applicants must be between 15 and 80 years old.";
        } else {
            $dob_sql = $date->format("Y-m-d");
        }
    }
}

if (!in_array($gender, $allowed_genders, true)) {
    $errors[] = "Please choose a gender option.";
}

if ($street === "") {
    $errors[] = "Please enter your street address.";
} elseif (mb_strlen($street) > 40) {
    $errors[] = "Street address must be 40 characters or fewer.";
}

if ($suburb === "") {
    $errors[] = "Please enter your suburb or town.";
} elseif (mb_strlen($suburb) > 40) {
    $errors[] = "Suburb/town must be 40 characters or fewer.";
}

if (!array_key_exists($state, $state_postcodes)) {
    $errors[] = "Please choose a valid state.";
}

if ($postcode === "") {
    $errors[] = "Please enter your postcode.";
} elseif (!preg_match('/^\d{4}$/', $postcode)) {
    $errors[] = "Postcode must be exactly 4 digits.";
} elseif (array_key_exists($state, $state_postcodes) && !in_array($postcode[0], $state_postcodes[$state], true)) {
    $errors[] = "Postcode " . $postcode . " does not match the state " . $state . ".";
}

if ($email === "") {
    $errors[] = "Please enter your email address.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    $errors[] = "Please enter a valid email address (for example name@example.com).";
}

if ($phone === "") {
    $errors[] = "Please enter your phone number.";
} elseif (!preg_match('/^\d{8,12}$/', $phone)) {
    $errors[] = "Phone number must be 8 to 12 digits.";
}

$skills = array_values(array_intersect($skills, $allowed_skills));
if (count($skills) === 0) {
    $errors[] = "Please tick at least one skill.";
}

if (mb_strlen($otherskills) > 1000) {
    $errors[] = "Other skills must be 1000 characters or fewer.";
}

// 5. Errors: send the applicant back to the form with their answers.
if (!empty($errors)) {
    if ($conn) {
        mysqli_close($conn);
    }
    $_SESSION["eoi_errors"] = $errors;
    $_SESSION["eoi_old"] = [
        "jobref" => $jobref, "firstname" => $firstname, "lastname" => $lastname,
        "dob" => $dob, "gender" => $gender, "street" => $street, "suburb" => $suburb,
        "state" => $state, "postcode" => $postcode, "email" => $email,
        "phone" => $phone, "skills" => $skills, "otherskills" => $otherskills,
    ];
    header("Location: apply.php");
    exit();
}

// 6. Create the table if needed, then insert with a prepared statement.
$eoi_number = null;
if (ensure_eoi_table($conn)) {
    $sql = "INSERT INTO eoi (job_ref, first_name, last_name, dob, gender, street, suburb,
                state, postcode, email, phone, skill_html5, skill_css3,
                skill_accessibility, skill_git, skill_teamwork, other_skills)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    $s_html  = (int) in_array("html5", $skills, true);
    $s_css   = (int) in_array("css3", $skills, true);
    $s_acc   = (int) in_array("accessibility", $skills, true);
    $s_git   = (int) in_array("git", $skills, true);
    $s_team  = (int) in_array("teamwork", $skills, true);
    $other   = ($otherskills === "") ? null : $otherskills;
    mysqli_stmt_bind_param($stmt, "sssssssssssiiiiis",
        $jobref, $firstname, $lastname, $dob_sql, $gender, $street, $suburb,
        $state, $postcode, $email, $phone, $s_html, $s_css, $s_acc, $s_git, $s_team, $other);
    if (mysqli_stmt_execute($stmt)) {
        $eoi_number = mysqli_insert_id($conn);
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($conn);

// New token so the same form cannot be re-submitted by refreshing.
unset($_SESSION["csrf_token"]);

// 7. Confirmation page.
$page_title   = "Application Received";
$body_class   = "apply";
$current_page = "apply";
include "header.inc";
include "nav.inc";
?>
    <main id="main">
<?php if ($eoi_number) : ?>
        <section class="confirmation" aria-labelledby="confirm-heading">
            <h1 id="confirm-heading">Thank you, <?php echo e($firstname); ?>!</h1>
            <p>Your application for job <strong><?php echo e($jobref); ?></strong> has been received.</p>
            <p class="eoi-number">Your Expression of Interest number is <strong><?php echo (int) $eoi_number; ?></strong>.</p>
            <p>Please keep this number. Our HR team will contact you at <?php echo e($email); ?>.</p>
            <p><a class="button" href="jobs.php">Back to jobs</a> <a class="button" href="index.php">Home</a></p>
        </section>
<?php else : ?>
        <section class="confirmation" aria-labelledby="confirm-heading">
            <h1 id="confirm-heading">Something went wrong</h1>
            <p class="notice error">Your application could not be saved. Please <a href="apply.php">try again</a> later.</p>
        </section>
<?php endif; ?>
    </main>
<?php include "footer.inc"; ?>
