<?php
/*
 * nav.inc - shared site header and main navigation.
 * Designed by Marwah, edited by Rayahen Raad (Part 1). PHP include for Part 2.
 * The page sets $current_page ("index", "jobs", "apply", "about", "manage")
 * so the matching link is highlighted.
 */
$current_page = $current_page ?? "";
$nav_links = [
    "index" => ["index.php", "Home"],
    "jobs"  => ["jobs.php", "Job Description"],
    "apply" => ["apply.php", "Apply"],
    "about" => ["about.php", "About Us"],
];
// Managers see a link to the HR area once logged in.
if (!empty($_SESSION["manager"])) {
    $nav_links["manage"] = ["manage.php", "Manage EOIs"];
}
?>
    <header id="site-header">
        <nav class="nav-container" aria-label="Main navigation">
            <a href="index.php" class="nav-brand">
                <span class="nav-logo">
                    <img src="images/logo.png" alt="" width="400" height="400">
                </span>
                <span class="brand-text">
                    <span class="brand-name">Evergreen Energy</span>
                    <span class="brand-tagline">Sustainable Energy Solutions</span>
                </span>
            </a>
            <ul class="nav-links">
<?php foreach ($nav_links as $key => $link) : ?>
<?php if ($key === $current_page) : ?>
                <li><a href="<?php echo $link[0]; ?>" class="active" aria-current="page"><?php echo $link[1]; ?></a></li>
<?php else : ?>
                <li><a href="<?php echo $link[0]; ?>"><?php echo $link[1]; ?></a></li>
<?php endif; ?>
<?php endforeach; ?>
            </ul>
        </nav>
    </header>
