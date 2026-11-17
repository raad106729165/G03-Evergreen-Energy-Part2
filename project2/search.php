<?php
/*
 * search.php - searches the jobs table and shows the matching job cards.
 * Group G03, COS10026 Part 2. Used by the search bars on index.php and jobs.php.
 */
require_once "functions.inc";
$page_title       = "Job Search";
$page_description = "Search Evergreen Energy job listings.";
$page_keywords    = "Evergreen Energy, job search, careers";
$body_class       = "jobs";
$current_page     = "jobs";
include "header.inc";
include "nav.inc";

$query  = isset($_GET["q"]) ? sanitise_input($_GET["q"]) : "";
$query  = mb_substr($query, 0, 50);
$jobs   = [];
$failed = false;

if ($query !== "") {
    $conn = db_connect();
    if ($conn) {
        // Prepared statement: the search text is never joined into the SQL string.
        $like = "%" . html_entity_decode($query, ENT_QUOTES, "UTF-8") . "%";
        $sql = "SELECT * FROM jobs
                WHERE job_ref LIKE ? OR title LIKE ? OR about_role LIKE ?
                   OR responsibilities LIKE ? OR essential_reqs LIKE ?
                   OR preferable_reqs LIKE ? OR reporting_line LIKE ?
                ORDER BY job_ref";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssssss", $like, $like, $like, $like, $like, $like, $like);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        } else {
            $failed = true;
        }
        mysqli_close($conn);
    } else {
        $failed = true;
    }
}
?>
    <main id="main">
        <section class="job-listings">
            <h1>Search Our Jobs</h1>

            <form class="job-search" action="search.php" method="get" role="search">
                <label for="job-search-q">Search jobs by title, reference, skill or keyword</label>
                <div class="job-search-row">
                    <input type="search" id="job-search-q" name="q" value="<?php echo e($query); ?>" placeholder="e.g. engineer, UX202, SQL">
                    <button type="submit">Search</button>
                </div>
            </form>

<?php if ($query === "") : ?>
            <p>Type a word above to search, or <a href="jobs.php">browse all jobs</a>.</p>
<?php elseif ($failed) : ?>
            <p class="notice error">Search is unavailable right now. Please try again later.</p>
<?php elseif (empty($jobs)) : ?>
            <p class="notice">No jobs matched "<?php echo e($query); ?>". <a href="jobs.php">Browse all jobs</a>.</p>
<?php else : ?>
            <p class="notice"><?php echo count($jobs); ?> job<?php echo count($jobs) === 1 ? "" : "s"; ?> matched "<?php echo e($query); ?>". <a href="jobs.php">Show all jobs</a>.</p>
<?php foreach ($jobs as $job) { render_job_card($job); } ?>
<?php endif; ?>
        </section>
    </main>
<?php include "footer.inc"; ?>
