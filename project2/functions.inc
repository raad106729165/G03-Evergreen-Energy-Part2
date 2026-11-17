<?php
/*
 * functions.inc - shared PHP helpers used by every page.
 * Group G03, COS10026 Web Technology Project, Part 2.
 * Direct browser access to .inc files is blocked by .htaccess.
 */

require_once "settings.php";

// Start one session per request, with safer cookie settings.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "httponly" => true,
        "samesite" => "Strict",
    ]);
    session_start();
}

/* ---------- Database ---------- */

// Opens a connection using the values in settings.php. Returns false on failure.
function db_connect()
{
    global $host, $user, $pwd, $sql_db;
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @mysqli_connect($host, $user, $pwd, $sql_db);
    if ($conn) {
        mysqli_set_charset($conn, "utf8mb4");
    }
    return $conn;
}

// Creates the eoi table if it does not exist yet (required by the brief).
function ensure_eoi_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS eoi (
        EOInumber      INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        job_ref        CHAR(5)      NOT NULL,
        first_name     VARCHAR(20)  NOT NULL,
        last_name      VARCHAR(20)  NOT NULL,
        dob            DATE         NOT NULL,
        gender         ENUM('female','male','undisclosed') NOT NULL,
        street         VARCHAR(40)  NOT NULL,
        suburb         VARCHAR(40)  NOT NULL,
        state          ENUM('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
        postcode       CHAR(4)      NOT NULL,
        email          VARCHAR(100) NOT NULL,
        phone          VARCHAR(12)  NOT NULL,
        skill_html5         TINYINT(1) NOT NULL DEFAULT 0,
        skill_css3          TINYINT(1) NOT NULL DEFAULT 0,
        skill_accessibility TINYINT(1) NOT NULL DEFAULT 0,
        skill_git           TINYINT(1) NOT NULL DEFAULT 0,
        skill_teamwork      TINYINT(1) NOT NULL DEFAULT 0,
        other_skills   TEXT NULL,
        status         ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
        submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_job_ref (job_ref),
        INDEX idx_name (last_name, first_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    return mysqli_query($conn, $sql);
}

/* ---------- Input and output ---------- */

// Cleans one input value: trim, strip slashes, escape HTML special characters.
function sanitise_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
    return $data;
}

// Escapes a value for safe output inside HTML. Existing entities are not
// encoded twice, because form data is already escaped before it is stored.
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8", false);
}

// Turns a newline-separated TEXT field into an array of list items.
function lines_to_array($text)
{
    $items = preg_split('/\r\n|\r|\n/', (string) $text);
    return array_values(array_filter(array_map("trim", $items), "strlen"));
}

/* ---------- Security helpers ---------- */

// Returns the CSRF token for this session, creating it on first use.
function csrf_token()
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

// Checks a submitted CSRF token against the session copy.
function csrf_valid($token)
{
    return isset($_SESSION["csrf_token"]) && is_string($token)
        && hash_equals($_SESSION["csrf_token"], $token);
}

// Sends non-logged-in visitors to the login page; also expires idle sessions.
function require_login()
{
    $timeout = 15 * 60; // 15 minutes of inactivity
    if (empty($_SESSION["manager"])) {
        header("Location: login.php");
        exit();
    }
    if (isset($_SESSION["last_activity"]) && time() - $_SESSION["last_activity"] > $timeout) {
        session_unset();
        session_destroy();
        header("Location: login.php?timeout=1");
        exit();
    }
    $_SESSION["last_activity"] = time();
}

/* ---------- Rendering ---------- */

// Prints one job card from a row of the jobs table.
function render_job_card($job)
{
    $sections = [
        "responsibilities" => ["Key Responsibilities", "ol"],
        "essential_reqs"   => ["Essential Requirements", "ul"],
        "preferable_reqs"  => ["Preferable Requirements", "ul"],
    ];
    echo "<article class=\"job-card\" id=\"job-" . e($job["job_ref"]) . "\">\n";
    echo "<h3>" . e($job["title"]) . "</h3>\n";
    echo "<section>\n<h4>Position Details</h4>\n<dl class=\"job-details\">\n";
    echo "<dt>Reference Number:</dt><dd>" . e($job["job_ref"]) . "</dd>\n";
    echo "<dt>Salary:</dt><dd>" . e($job["salary"]) . "</dd>\n";
    echo "<dt>Reporting Line:</dt><dd>" . e($job["reporting_line"]) . "</dd>\n";
    echo "</dl>\n</section>\n";
    echo "<section>\n<h4>About the Role</h4>\n<p>" . e($job["about_role"]) . "</p>\n</section>\n";
    foreach ($sections as $field => $meta) {
        echo "<section>\n<h4>" . $meta[0] . "</h4>\n<" . $meta[1] . ">\n";
        foreach (lines_to_array($job[$field]) as $item) {
            echo "<li>" . e($item) . "</li>\n";
        }
        echo "</" . $meta[1] . ">\n</section>\n";
    }
    echo "<p class=\"apply-link\"><a class=\"button\" href=\"apply.php?ref=" . urlencode($job["job_ref"]) . "\">Apply for "
        . e($job["title"]) . "</a></p>\n";
    echo "</article>\n";
}
