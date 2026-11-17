<?php
/*
 * jobs.php - Job descriptions page.
 * Part 1 page by Zainab (job cards by Zainab and Marwah, edited by Rayahen Raad).
 * Part 2: every job is loaded from the jobs table, with a search bar
 * that queries the database through search.php.
 */
require_once "functions.inc";
$page_title       = "Jobs";
$page_description = "Explore career opportunities at Evergreen Energy Sustainable Energy Solutions.";
$page_keywords    = "Evergreen Energy, jobs, renewable energy, web developer, UX/UI designer";
$page_author      = "Group G03";
$body_class       = "jobs";
$current_page     = "jobs";
$page_style       = "        .career-intro { background-color: #e8f2e5; padding: 20px; margin-bottom: 25px; border-left: 5px solid #287a3d; }";
include "header.inc";
include "nav.inc";

$conn = db_connect();
$jobs = [];
if ($conn) {
    $result = mysqli_query($conn, "SELECT * FROM jobs ORDER BY job_ref");
    if ($result) {
        $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
    }
    mysqli_close($conn);
}
?>
    <main id="main">

        <section class="career-intro">
            <h1>Build Your Career With Us</h1>
            <p>
                At Evergreen Energy Sustainable Energy Solutions, we believe
                technology and innovation can help create a cleaner future.
                We support sustainable energy solutions and use digital
                technology to share information about renewable energy projects.
            </p>
            <p>
                We are looking for talented people who want to help us build
                accessible websites and inclusive digital experiences that
                inform the public about clean energy and community programs.
            </p>
        </section>

        <aside class="career-aside">
            <h2>Why Join Us?</h2>
            <ul>
                <li>Flexible hybrid work arrangements.</li>
                <li>Five paid volunteering days each year for environmental projects.</li>
                <li>An annual budget for learning, training and certifications.</li>
            </ul>
            <h3>Inclusive Employment</h3>
            <p>
                We encourage applications from Aboriginal and Torres Strait
                Islander peoples. We aim to work respectfully with First Nations
                communities on renewable energy projects on Country and value
                their knowledge and perspectives.
            </p>
        </aside>

        <section class="job-listings">
            <h2>Current Job Opportunities</h2>
            <p>
                Explore our available positions and find an opportunity that
                matches your experience, skills and interests.
            </p>

            <!-- Search bar: queries the jobs table (search.php) -->
            <form class="job-search" action="search.php" method="get" role="search">
                <label for="job-search-q">Search jobs by title, reference, skill or keyword</label>
                <div class="job-search-row">
                    <input type="search" id="job-search-q" name="q" placeholder="e.g. engineer, UX202, SQL">
                    <button type="submit">Search</button>
                </div>
            </form>

<?php if (empty($jobs)) : ?>
            <p class="notice error">Job listings are unavailable right now. Please try again later.</p>
<?php else : ?>
<?php foreach ($jobs as $job) { render_job_card($job); } ?>
<?php endif; ?>
        </section>

        <section class="application-steps">
            <h2>How to Apply</h2>
            <p style="font-weight: bold;">Follow these steps to apply for a position:</p>
            <ol>
                <li>Read the job descriptions and requirements carefully.</li>
                <li>Choose the position that matches your skills and interests.</li>
                <li>Write down the correct five-character reference number.</li>
                <li>Open the application form and enter the job reference.</li>
                <li>Check your details before submitting your application.</li>
            </ol>
            <p><a class="button" href="apply.php">Go to Application Form</a></p>
        </section>

        <p class="page-sources">Sources and acknowledgements: the image credits and HTML and CSS references for this site are listed on the <a href="about.php#ack-heading">About Us page</a>.</p>
    </main>
<?php include "footer.inc"; ?>
