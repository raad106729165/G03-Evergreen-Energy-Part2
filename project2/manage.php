<?php
/*
 * manage.php - HR manager queries on the eoi table.
 * Group G03, COS10026 Part 2. Only reachable after logging in (login.php).
 *
 * Queries offered:
 *   1. List all EOIs
 *   2. List EOIs for a job reference
 *   3. List EOIs by applicant first name, last name or both
 *   4. Delete all EOIs for a job reference
 *   5. Change the status of an EOI
 *   6. Choose the sort field (and direction) for any list
 *
 * Reads use GET (so lists can be bookmarked and re-sorted); changes use
 * POST with a CSRF token, then redirect back (Post/Redirect/Get).
 * Every query is a prepared statement; the sort column comes from a
 * fixed whitelist, never straight from the URL.
 */
require_once "functions.inc";
require_login();

$sort_fields = [
    "EOInumber"    => "EOI number",
    "job_ref"      => "Job reference",
    "first_name"   => "First name",
    "last_name"    => "Last name",
    "state"        => "State",
    "status"       => "Status",
    "submitted_at" => "Date submitted",
];
$statuses = ["New", "Current", "Final"];

$conn = db_connect();
if ($conn) {
    ensure_eoi_table($conn);
}

/* ---------- Changes (POST) ---------- */
if ($_SERVER["REQUEST_METHOD"] === "POST" && $conn) {
    $action = $_POST["action"] ?? "";
    $return = $_POST["return"] ?? "manage.php";
    // Only allow redirects back to this page.
    if (strpos($return, "manage.php") !== 0) {
        $return = "manage.php";
    }

    if (!csrf_valid($_POST["csrf_token"] ?? "")) {
        $_SESSION["flash"] = ["error", "Your session expired. Please try again."];
    } elseif ($action === "delete") {
        $ref = strtoupper(sanitise_input($_POST["jobref"] ?? ""));
        if (!preg_match('/^[A-Z0-9]{5}$/', $ref)) {
            $_SESSION["flash"] = ["error", "Choose a valid job reference to delete."];
        } elseif (($_POST["confirm"] ?? "") !== "yes") {
            $_SESSION["flash"] = ["error", "Tick the confirmation box to delete EOIs for " . $ref . "."];
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM eoi WHERE job_ref = ?");
            mysqli_stmt_bind_param($stmt, "s", $ref);
            mysqli_stmt_execute($stmt);
            $count = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION["flash"] = ["success", $count . " EOI(s) for job " . $ref . " deleted."];
        }
    } elseif ($action === "status") {
        $eoi    = filter_var($_POST["eoi_number"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $status = sanitise_input($_POST["status"] ?? "");
        if ($eoi === false) {
            $_SESSION["flash"] = ["error", "Enter a valid EOI number."];
        } elseif (!in_array($status, $statuses, true)) {
            $_SESSION["flash"] = ["error", "Choose a valid status."];
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE eoi SET status = ? WHERE EOInumber = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $eoi);
            mysqli_stmt_execute($stmt);
            $changed = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            if ($changed > 0) {
                $_SESSION["flash"] = ["success", "EOI " . $eoi . " is now " . $status . "."];
            } else {
                $check = mysqli_prepare($conn, "SELECT 1 FROM eoi WHERE EOInumber = ?");
                mysqli_stmt_bind_param($check, "i", $eoi);
                mysqli_stmt_execute($check);
                mysqli_stmt_store_result($check);
                $exists = mysqli_stmt_num_rows($check) > 0;
                mysqli_stmt_close($check);
                $_SESSION["flash"] = $exists
                    ? ["success", "EOI " . $eoi . " already had the status " . $status . "."]
                    : ["error", "No EOI with number " . $eoi . " was found."];
            }
        }
    }
    mysqli_close($conn);
    header("Location: " . $return);
    exit();
}

/* ---------- Lists (GET) ---------- */
$view  = $_GET["view"] ?? "";
$sort  = array_key_exists($_GET["sort"] ?? "", $sort_fields) ? $_GET["sort"] : "EOInumber";
$dir   = (($_GET["dir"] ?? "") === "desc") ? "DESC" : "ASC";
$q_ref   = strtoupper(sanitise_input($_GET["jobref"] ?? ""));
$q_first = sanitise_input($_GET["firstname"] ?? "");
$q_last  = sanitise_input($_GET["lastname"] ?? "");

$rows      = null;
$heading   = "";
$list_error = "";

if ($conn && $view !== "") {
    $where  = "";
    $types  = "";
    $params = [];
    if ($view === "all") {
        $heading = "All EOIs";
    } elseif ($view === "ref") {
        if (!preg_match('/^[A-Z0-9]{5}$/', $q_ref)) {
            $list_error = "Choose a job reference to list.";
        } else {
            $where = "WHERE job_ref = ?";
            $types = "s";
            $params = [$q_ref];
            $heading = "EOIs for job " . $q_ref;
        }
    } elseif ($view === "name") {
        $conds = [];
        if ($q_first !== "") {
            $conds[] = "first_name LIKE ?";
            $types .= "s";
            $params[] = $q_first . "%";
        }
        if ($q_last !== "") {
            $conds[] = "last_name LIKE ?";
            $types .= "s";
            $params[] = $q_last . "%";
        }
        if (empty($conds)) {
            $list_error = "Enter a first name, a last name, or both.";
        } else {
            $where = "WHERE " . implode(" AND ", $conds);
            $heading = "EOIs for applicant " . trim($q_first . " " . $q_last);
        }
    }

    if ($list_error === "" && $heading !== "") {
        // $sort and $dir come from whitelists above, so they are safe in the SQL text.
        $sql  = "SELECT * FROM eoi " . $where . " ORDER BY " . $sort . " " . $dir . ", EOInumber ASC";
        $stmt = mysqli_prepare($conn, $sql);
        if ($types !== "") {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
    }
}

// Job references for the drop-down lists.
$job_refs = [];
if ($conn) {
    $result = mysqli_query($conn, "SELECT job_ref, title FROM jobs ORDER BY job_ref");
    if ($result) {
        $job_refs = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_close($conn);
}

$flash = $_SESSION["flash"] ?? null;
unset($_SESSION["flash"]);
$return_url = "manage.php" . (empty($_SERVER["QUERY_STRING"]) ? "" : "?" . $_SERVER["QUERY_STRING"]);

// Prints the sort-field and direction selects used by every list form.
function sort_controls($sort_fields, $sort, $dir, $id)
{
    echo '<div class="field"><label for="sort-' . $id . '">Sort by</label><select id="sort-' . $id . '" name="sort">';
    foreach ($sort_fields as $value => $label) {
        echo '<option value="' . $value . '"' . ($value === $sort ? " selected" : "") . ">" . $label . "</option>";
    }
    echo '</select></div>';
    echo '<div class="field"><label for="dir-' . $id . '">Order</label><select id="dir-' . $id . '" name="dir">';
    echo '<option value="asc"' . ($dir === "ASC" ? " selected" : "") . '>Ascending</option>';
    echo '<option value="desc"' . ($dir === "DESC" ? " selected" : "") . '>Descending</option>';
    echo '</select></div>';
}

$page_title   = "Manage EOIs";
$page_description = "HR manager area for Expressions of Interest.";
$body_class   = "admin";
$current_page = "manage";
include "header.inc";
include "nav.inc";
?>
    <main id="main">
        <div class="manage-top">
            <h1>Manage Expressions of Interest</h1>
            <p>Logged in as <strong><?php echo e($_SESSION["manager"]); ?></strong>. <a class="button" href="logout.php">Log out</a></p>
        </div>

<?php if (!$conn) : ?>
        <p class="notice error">The database is unavailable. Check settings.php and that MySQL is running.</p>
<?php endif; ?>
<?php if ($flash) : ?>
        <p class="notice <?php echo e($flash[0]); ?>" role="status"><?php echo e($flash[1]); ?></p>
<?php endif; ?>

        <div class="manage-grid">
            <!-- 1. List all -->
            <form class="manage-card" action="manage.php" method="get">
                <h2>List all EOIs</h2>
                <input type="hidden" name="view" value="all">
<?php sort_controls($sort_fields, $sort, $dir, "all"); ?>
                <button type="submit" class="button">List all</button>
            </form>

            <!-- 2. By job reference -->
            <form class="manage-card" action="manage.php" method="get">
                <h2>List by job reference</h2>
                <input type="hidden" name="view" value="ref">
                <div class="field">
                    <label for="list-ref">Job reference</label>
                    <select id="list-ref" name="jobref">
                        <option value="">Choose a job</option>
<?php foreach ($job_refs as $j) : ?>
                        <option value="<?php echo e($j["job_ref"]); ?>"<?php echo $q_ref === $j["job_ref"] ? " selected" : ""; ?>><?php echo e($j["job_ref"] . " - " . $j["title"]); ?></option>
<?php endforeach; ?>
                    </select>
                </div>
<?php sort_controls($sort_fields, $sort, $dir, "ref"); ?>
                <button type="submit" class="button">List</button>
            </form>

            <!-- 3. By applicant name -->
            <form class="manage-card" action="manage.php" method="get">
                <h2>List by applicant name</h2>
                <input type="hidden" name="view" value="name">
                <div class="field">
                    <label for="list-first">First name</label>
                    <input type="text" id="list-first" name="firstname" value="<?php echo e($q_first); ?>">
                </div>
                <div class="field">
                    <label for="list-last">Last name</label>
                    <input type="text" id="list-last" name="lastname" value="<?php echo e($q_last); ?>">
                </div>
<?php sort_controls($sort_fields, $sort, $dir, "name"); ?>
                <button type="submit" class="button">Search</button>
            </form>

            <!-- 4. Delete by job reference -->
            <form class="manage-card danger" action="manage.php" method="post">
                <h2>Delete EOIs by job reference</h2>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="return" value="<?php echo e($return_url); ?>">
                <div class="field">
                    <label for="del-ref">Job reference</label>
                    <select id="del-ref" name="jobref">
                        <option value="">Choose a job</option>
<?php foreach ($job_refs as $j) : ?>
                        <option value="<?php echo e($j["job_ref"]); ?>"><?php echo e($j["job_ref"] . " - " . $j["title"]); ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <label class="confirm"><input type="checkbox" name="confirm" value="yes"> I understand every EOI for this job will be permanently deleted.</label>
                <button type="submit" class="button">Delete EOIs</button>
            </form>

            <!-- 5. Change status -->
            <form class="manage-card" action="manage.php" method="post">
                <h2>Change an EOI status</h2>
                <input type="hidden" name="action" value="status">
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <input type="hidden" name="return" value="<?php echo e($return_url); ?>">
                <div class="field">
                    <label for="st-eoi">EOI number</label>
                    <input type="text" id="st-eoi" name="eoi_number" inputmode="numeric">
                </div>
                <div class="field">
                    <label for="st-status">New status</label>
                    <select id="st-status" name="status">
<?php foreach ($statuses as $s) : ?>
                        <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
<?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="button">Update status</button>
            </form>
        </div>

<?php if ($list_error !== "") : ?>
        <p class="notice error"><?php echo e($list_error); ?></p>
<?php endif; ?>

<?php if (is_array($rows)) : ?>
        <section class="results" aria-labelledby="results-heading">
            <h2 id="results-heading"><?php echo e($heading); ?> (<?php echo count($rows); ?>)</h2>
            <p class="sort-note">Sorted by <?php echo e($sort_fields[$sort]); ?>, <?php echo $dir === "ASC" ? "ascending" : "descending"; ?>.</p>
<?php if (empty($rows)) : ?>
            <p class="notice">No EOIs found.</p>
<?php else : ?>
            <div class="table-wrapper" tabindex="0" role="region" aria-labelledby="results-heading">
            <table class="eoi-table">
                <thead>
                    <tr>
                        <th scope="col">EOI</th>
                        <th scope="col">Job</th>
                        <th scope="col">Name</th>
                        <th scope="col">Date of birth</th>
                        <th scope="col">Gender</th>
                        <th scope="col">Address</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Skills</th>
                        <th scope="col">Other skills</th>
                        <th scope="col">Submitted</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($rows as $r) :
    $skill_list = [];
    foreach (["html5" => "HTML5", "css3" => "CSS3", "accessibility" => "Accessibility", "git" => "Git", "teamwork" => "Teamwork"] as $k => $label) {
        if ((int) $r["skill_" . $k] === 1) {
            $skill_list[] = $label;
        }
    }
?>
                    <tr>
                        <th scope="row"><?php echo (int) $r["EOInumber"]; ?></th>
                        <td><?php echo e($r["job_ref"]); ?></td>
                        <td><?php echo e($r["first_name"] . " " . $r["last_name"]); ?></td>
                        <td><?php echo e(date("d/m/Y", strtotime($r["dob"]))); ?></td>
                        <td><?php echo e(ucfirst($r["gender"])); ?></td>
                        <td><?php echo e($r["street"] . ", " . $r["suburb"] . " " . $r["state"] . " " . $r["postcode"]); ?></td>
                        <td><a href="mailto:<?php echo e($r["email"]); ?>"><?php echo e($r["email"]); ?></a></td>
                        <td><?php echo e($r["phone"]); ?></td>
                        <td><?php echo e(implode(", ", $skill_list)); ?></td>
                        <td><?php echo $r["other_skills"] === null ? "-" : nl2br(e($r["other_skills"])); ?></td>
                        <td><?php echo e(date("d/m/Y H:i", strtotime($r["submitted_at"]))); ?></td>
                        <td>
                            <form class="inline-status" action="manage.php" method="post">
                                <input type="hidden" name="action" value="status">
                                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                                <input type="hidden" name="return" value="<?php echo e($return_url); ?>">
                                <input type="hidden" name="eoi_number" value="<?php echo (int) $r["EOInumber"]; ?>">
                                <label class="visually-hidden" for="status-<?php echo (int) $r["EOInumber"]; ?>">Status for EOI <?php echo (int) $r["EOInumber"]; ?></label>
                                <select id="status-<?php echo (int) $r["EOInumber"]; ?>" name="status">
<?php foreach ($statuses as $s) : ?>
                                    <option value="<?php echo $s; ?>"<?php echo $r["status"] === $s ? " selected" : ""; ?>><?php echo $s; ?></option>
<?php endforeach; ?>
                                </select>
                                <button type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
            </div>
<?php endif; ?>
        </section>
<?php endif; ?>
    </main>
<?php include "footer.inc"; ?>
