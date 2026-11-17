<?php
/*
 * apply.php - Job application (Expression of Interest) form.
 * Part 1 page by Hiba. Part 2: converted to PHP, posts to process_eoi.php.
 * All browser validation is switched off (novalidate, no required/pattern
 * attributes): every check happens on the server in process_eoi.php.
 * If the server finds errors, the form is shown again with the
 * applicant's answers and a list of what to fix.
 */
require_once "functions.inc";
$page_title       = "Apply";
$page_description = "Apply for a job at Evergreen Energy. Complete our online application form.";
$page_keywords    = "job application, careers, apply, Evergreen Energy, renewable energy";
$page_author      = "Hiba Imran, Group G03";
$body_class       = "apply";
$current_page     = "apply";
$page_style       = "        .form-intro { background-color: #eaf2ec; border-left: 6px solid #2f5d3f; padding: 10px; }";

// Errors and previous answers come back from process_eoi.php through the session.
$errors = $_SESSION["eoi_errors"] ?? [];
$old    = $_SESSION["eoi_old"] ?? [];
unset($_SESSION["eoi_errors"], $_SESSION["eoi_old"]);

// Job reference list is read from the jobs table.
$job_options = [];
$conn = db_connect();
if ($conn) {
    $result = mysqli_query($conn, "SELECT job_ref, title FROM jobs ORDER BY job_ref");
    if ($result) {
        $job_options = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_close($conn);
}

// A job card link (apply.php?ref=WD101) preselects that position.
$selected_ref = $old["jobref"] ?? (isset($_GET["ref"]) ? sanitise_input($_GET["ref"]) : "");

// Small helpers for re-filling the form.
function old_value($old, $key)
{
    return isset($old[$key]) ? e($old[$key]) : "";
}
function checked_if($condition)
{
    return $condition ? " checked" : "";
}
function selected_if($condition)
{
    return $condition ? " selected" : "";
}

$states = ["VIC", "NSW", "QLD", "NT", "WA", "SA", "TAS", "ACT"];
$skills = [
    "html5"         => "HTML5",
    "css3"          => "CSS3",
    "accessibility" => "Accessibility",
    "git"           => "Git/GitHub",
    "teamwork"      => "Teamwork",
];
$old_skills = $old["skills"] ?? [];

include "header.inc";
include "nav.inc";
?>
    <main id="main">
        <h1>Join Our Team</h1>

        <section class="apply-panel" aria-labelledby="panel-heading">
            <div class="panel-header">
                <h2 id="panel-heading">Job Application</h2>
                <p>Scroll through each section, then submit.</p>
            </div>

            <form id="application-form" action="process_eoi.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">

                <div class="panel-scroll">

<?php if (!empty($errors)) : ?>
                    <div class="form-errors" role="alert">
                        <h3>Please fix the following and submit again:</h3>
                        <ul>
<?php foreach ($errors as $error) : ?>
                            <li><?php echo e($error); ?></li>
<?php endforeach; ?>
                        </ul>
                    </div>
<?php endif; ?>

                    <p class="form-intro" style="font-weight: bold;">
                        All fields are required except "Other skills". Choose the position you are
                        applying for. You can read the details on the <a href="jobs.php">Jobs page</a>.
                    </p>

                    <div class="sections">

                        <fieldset class="wide">
                            <legend>Position</legend>
                            <div class="field">
                                <label for="jobref">Job reference number (select a position)</label>
                                <select id="jobref" name="jobref">
                                    <option value="">Please select a position</option>
<?php foreach ($job_options as $opt) : ?>
                                    <option value="<?php echo e($opt["job_ref"]); ?>"<?php echo selected_if($selected_ref === $opt["job_ref"]); ?>><?php echo e($opt["job_ref"] . " - " . $opt["title"]); ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend>Personal details</legend>
                            <div class="field">
                                <label for="firstname">First name (letters only, max 20)</label>
                                <input type="text" id="firstname" name="firstname" value="<?php echo old_value($old, "firstname"); ?>">
                            </div>
                            <div class="field">
                                <label for="lastname">Last name (letters only, max 20)</label>
                                <input type="text" id="lastname" name="lastname" value="<?php echo old_value($old, "lastname"); ?>">
                            </div>
                            <div class="field">
                                <label for="dob">Date of birth (you must be 15 to 80 years old)</label>
                                <input type="date" id="dob" name="dob" value="<?php echo old_value($old, "dob"); ?>">
                            </div>
                            <fieldset class="radio-group">
                                <legend>Gender</legend>
                                <label><input type="radio" name="gender" value="female"<?php echo checked_if(($old["gender"] ?? "") === "female"); ?>> Female</label>
                                <label><input type="radio" name="gender" value="male"<?php echo checked_if(($old["gender"] ?? "") === "male"); ?>> Male</label>
                                <label><input type="radio" name="gender" value="undisclosed"<?php echo checked_if(($old["gender"] ?? "") === "undisclosed"); ?>> Prefer not to say</label>
                            </fieldset>
                        </fieldset>

                        <fieldset>
                            <legend>Address and contact</legend>
                            <div class="field">
                                <label for="street">Street address (max 40 characters)</label>
                                <input type="text" id="street" name="street" value="<?php echo old_value($old, "street"); ?>">
                            </div>
                            <div class="field">
                                <label for="suburb">Suburb/Town (max 40 characters)</label>
                                <input type="text" id="suburb" name="suburb" value="<?php echo old_value($old, "suburb"); ?>">
                            </div>
                            <div class="field">
                                <label for="state">State</label>
                                <select id="state" name="state">
                                    <option value="">Please select</option>
<?php foreach ($states as $st) : ?>
                                    <option value="<?php echo $st; ?>"<?php echo selected_if(($old["state"] ?? "") === $st); ?>><?php echo $st; ?></option>
<?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field">
                                <label for="postcode">Postcode (4 digits, must match your state)</label>
                                <input type="text" id="postcode" name="postcode" value="<?php echo old_value($old, "postcode"); ?>">
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input type="text" id="email" name="email" value="<?php echo old_value($old, "email"); ?>">
                            </div>
                            <div class="field">
                                <label for="phone">Phone number (8 to 12 digits, spaces allowed)</label>
                                <input type="text" id="phone" name="phone" value="<?php echo old_value($old, "phone"); ?>">
                            </div>
                        </fieldset>

                        <fieldset class="wide skills">
                            <legend>Skills</legend>
                            <p>Tick all the skills that apply (at least one).</p>
<?php foreach ($skills as $value => $label) : ?>
                            <label><input type="checkbox" name="skills[]" value="<?php echo $value; ?>"<?php echo checked_if(in_array($value, $old_skills, true)); ?>> <?php echo $label; ?></label>
<?php endforeach; ?>
                            <div class="field">
                                <label for="otherskills">Other skills</label>
                                <textarea id="otherskills" name="otherskills" rows="5" cols="40"
                                          placeholder="Tell us about any other relevant skills"><?php echo old_value($old, "otherskills"); ?></textarea>
                            </div>
                        </fieldset>
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit">Submit application</button>
                    <button type="reset">Clear form</button>
                </div>
            </form>
        </section>

        <section class="inclusion" aria-labelledby="inclusion-heading">
            <h2 id="inclusion-heading">Our commitment to inclusion</h2>
            <p>Evergreen Energy strongly encourages Aboriginal and Torres Strait Islander peoples to apply
                for every role we advertise. Many of our solar, wind and storage projects are built on Country,
                and the knowledge, perspectives and leadership of First Nations people make our work stronger.
                If you would like support with your application or any adjustments during the recruitment
                process, please contact us at <a href="mailto:info@evergreenenergy.com">info@evergreenenergy.com</a>.</p>
        </section>
        <section class="privacy-note" aria-labelledby="privacy-heading">
            <h2 id="privacy-heading">Your privacy</h2>
            <p>We only use your details to assess your application. They are stored securely and are seen only by our HR team.</p>
        </section>
        <p class="page-sources">Sources and acknowledgements: the image credits and HTML and CSS references for this site are listed on the <a href="about.php#ack-heading">About Us page</a>.</p>
    </main>
<?php include "footer.inc"; ?>
