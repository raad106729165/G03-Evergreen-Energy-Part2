<?php
/*
 * about.php - About Us page for the Evergreen Energy website.
 * Part 1 page by Rayahen Raad (team leader).
 * Part 2: converted to PHP with shared includes; member details and
 * contributions (Part 1 and Part 2) are loaded from the about table.
 */
require_once "functions.inc";
$page_title       = "About Us";
$page_description = "Meet Group G03, the team behind the Evergreen Energy careers website: members, roles, contributions and how we worked together.";
$page_keywords    = "Evergreen Energy, about us, Group G03, team, sustainable energy, clean energy careers";
$page_author      = "Rayahen Raad, Group G03";
$body_class       = "about";
$current_page     = "about";
$page_style       = "        .slogan { margin-top: -8px; font-size: 1.1em; font-style: italic; color: #2e7d46; }\n        .leader-row { font-weight: bold; }";

$members = [];
$conn = db_connect();
if ($conn) {
    $result = mysqli_query($conn, "SELECT * FROM about ORDER BY display_order");
    if ($result) {
        $members = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_close($conn);
}

include "header.inc";
include "nav.inc";
?>
    <main id="main">
        <h1>About Us</h1>
        <p class="slogan">Your Career. Our Future. One Clean Energy Future</p>

        <!-- ===== Group introduction ===== -->
        <section class="group-intro" aria-labelledby="group-heading">
            <div class="group-logo">
                <img src="images/logo.png" alt="Evergreen Energy logo: a wind turbine, sun, leaves and water inside a circle" width="200" height="200">
                <p class="logo-caption">Our shared brand, used across every page</p>
            </div>
            <div>
                <h2 id="group-heading">Our Group</h2>
                <p>We are Group G03, five students who came together to build the careers website for <strong>Evergreen Energy</strong>, a clean energy company growing its technology team. Each of us owned one part of the site, and we shared one brand, one stylesheet and one goal: a site that makes applying for a clean energy career simple.</p>
                <!-- Nested list: group details, with the class details inside -->
                <ul class="group-info">
                    <li><strong>Group name:</strong> G03
                        <ul>
                            <li><strong>Team leader:</strong> Rayahen Raad</li>
                            <li><strong>Unit:</strong> COS10026 Web Technology Project</li>
                            <li><strong>Class:</strong>
                                <ul>
                                    <li><strong>Days:</strong> Sunday and Tuesday</li>
                                    <li><strong>Time:</strong> 9:00 am to 11:00 am</li>
                                    <li><strong>Tutor:</strong> Dr Hamada Al Absi</li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </section>

        <!-- ===== Project at a glance ===== -->
        <section aria-labelledby="glance-heading">
            <h2 id="glance-heading">Project at a Glance</h2>
            <ul class="team-stats">
                <li><strong>5</strong> team members</li>
                <li><strong>4</strong> public pages</li>
                <li><strong>4</strong> database tables</li>
                <li><strong>1</strong> shared stylesheet</li>
                <li><strong>2</strong> GitHub repositories</li>
            </ul>
        </section>

        <!-- ===== Members and contributions (Part 2: loaded from the about table) ===== -->
        <section aria-labelledby="members-heading">
            <h2 id="members-heading">Members and Contributions</h2>
<?php if (empty($members)) : ?>
            <p class="notice error">Member details are unavailable right now.</p>
<?php else : ?>
            <div class="table-wrapper">
            <table class="members-table">
                <caption>Group G03 members, their roles and what each person built in Part 1 and Part 2</caption>
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Student ID</th>
                        <th scope="col">Team role</th>
                        <th scope="col">Part 1 contribution</th>
                        <th scope="col">Part 2 contribution</th>
                    </tr>
                </thead>
                <tbody>
<?php foreach ($members as $mem) : ?>
                    <tr<?php echo $mem["is_leader"] ? ' class="leader-row"' : ""; ?>>
                        <th scope="row"><?php echo e($mem["full_name"]); ?></th>
                        <td><span class="student-id"><?php echo e($mem["student_id"]); ?></span></td>
                        <td><?php echo e($mem["team_role"]); ?></td>
                        <td><?php echo e($mem["part1_contribution"]); ?></td>
                        <td><?php echo e($mem["part2_contribution"]); ?></td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <h3>What Each of Us Built, in Our Own Words</h3>
            <dl class="contributions">
<?php foreach ($members as $mem) : ?>
                <dt><?php echo e($mem["short_name"]); ?><?php if ($mem["is_leader"]) : ?> <span class="member-role">(team leader)</span><?php endif; ?></dt>
                <dd><?php echo e($mem["summary"]); ?></dd>
<?php if (!empty($mem["quote_original"])) : ?>
                <dd class="member-quote">
                    <p class="quote-original" lang="<?php echo e($mem["quote_lang"]); ?>" dir="rtl"><?php echo nl2br(e($mem["quote_original"])); ?></p>
<?php if (!empty($mem["quote_roman"])) : ?>
                    <p class="quote-roman" lang="<?php echo e($mem["quote_lang"]); ?>-Latn"><em><?php echo e($mem["quote_roman"]); ?></em></p>
<?php endif; ?>
                    <p class="quote-translation">English translation: <q><?php echo e($mem["quote_translation"]); ?></q></p>
                </dd>
<?php endif; ?>
<?php endforeach; ?>
            </dl>
<?php endif; ?>
        </section>

        <!-- ===== Fun facts ===== -->
        <section aria-labelledby="facts-heading">
            <h2 id="facts-heading">Fun Facts</h2>
            <!-- Fun facts written for the team to check and edit -->
            <div class="table-wrapper">
            <table class="fun-facts">
                <caption>A few things you might not know about Group G03</caption>
                <thead>
                    <tr>
                        <th scope="col">Member</th>
                        <th scope="col">Dream job</th>
                        <th scope="col">Favourite coding snack</th>
                        <th scope="col">Team superpower</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th scope="row">Rayahen</th>
                        <td>Chief Technology Officer at a clean energy start-up</td>
                        <td>Coffee and dark chocolate</td>
                        <td>Merging five branches into main with (almost) no conflicts</td>
                    </tr>
                    <tr>
                        <th scope="row">Redha</th>
                        <td>Creative director for a climate campaign</td>
                        <td>Salted popcorn</td>
                        <td>Turning a blank page into a hero banner</td>
                    </tr>
                    <tr>
                        <th scope="row">Marwa</th>
                        <td>Lead brand designer</td>
                        <td>Fresh strawberries</td>
                        <td>Making four pages look like one website</td>
                    </tr>
                    <tr>
                        <th scope="row">Hiba</th>
                        <td>UX researcher</td>
                        <td>Chocolate chip cookies</td>
                        <td>Writing validation patterns that catch every typo</td>
                    </tr>
                    <tr>
                        <th scope="row">Zainab</th>
                        <td>Head of talent at a tech company</td>
                        <td>Mixed nuts</td>
                        <td>Writing job ads people actually want to read</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </section>

        <!-- ===== Meet the team (company roles for the Evergreen Energy story) ===== -->
        <section aria-labelledby="team-heading">
            <h2 id="team-heading">Meet the Team</h2>
            <figure class="group-photo">
                <img src="images/group-photo.jpg" alt="Our Project Team: a collaborative team building a meaningful web experience. The five members of Group G03 stand together in front of a green Evergreen Energy background" width="1400" height="638">
                <figcaption>Group G03, the team behind the Evergreen Energy website</figcaption>
            </figure>
            <div class="team-grid">
            <div class="team-card leader">
                <span class="avatar">R</span>
                <h3>Rayahen, Technology Lead</h3>
                <p>Rayahen drives Evergreen Energy's digital strategy and the growth of its technology team. With a background in software engineering and project management, Rayahen coordinates the company's digital platforms, from client monitoring tools to this website, and keeps technology aligned with the company's clean energy goals.</p>
            </div>
            <div class="team-card">
                <span class="avatar">R</span>
                <h3>Redha, Communications Lead</h3>
                <p>Redha shapes how Evergreen Energy tells its story. Having worked alongside the engineering teams since the company's early projects, Redha knows the work inside out and turns technical achievements into clear, engaging public content.</p>
            </div>
            <div class="team-card">
                <span class="avatar">M</span>
                <h3>Marwa, Design Lead</h3>
                <p>Marwa guides the visual identity of Evergreen Energy across print and digital. Working closely with Redha, Marwa makes sure every project update, campaign, and web page reflects the company accurately and looks the part.</p>
            </div>
            <div class="team-card">
                <span class="avatar">Z</span>
                <h3>Zainab, Talent Lead</h3>
                <p>Zainab heads hiring across the company, with a current focus on building the new technology team. Zainab works with each department to define roles and find people whose skills fit both the job and the mission.</p>
            </div>
            <div class="team-card">
                <span class="avatar">H</span>
                <h3>Hiba, People Operations Lead</h3>
                <p>Hiba manages the recruitment journey from application to onboarding. Hiba designed the company's application system to make applying simple and fair, and makes sure every new hire has a smooth start.</p>
            </div>
            </div>
        </section>

        <!-- ===== Teamwork ===== -->
        <section aria-labelledby="teamwork-heading">
            <h2 id="teamwork-heading">How We Worked Together</h2>
            <ol class="workflow">
                <li><strong>Plan:</strong> we agreed on the company, brand colours, logo and page list, and gave each member one page to own.</li>
                <li><strong>Build:</strong> each member worked on their own branch in our shared GitHub repository, so nobody overwrote anyone else's work.</li>
                <li><strong>Share:</strong> Marwa's header and footer and one shared stylesheet keep every page looking like one website.</li>
                <li><strong>Merge:</strong> finished pages were merged into the main branch, then checked together for consistency and validation.</li>
            </ol>
        </section>

        <!-- ===== Mission ===== -->
        <section aria-labelledby="mission-heading">
            <h2 id="mission-heading">Our Mission</h2>
            <p class="mission">Evergreen Energy delivers clean, affordable energy and builds the technical talent to support it.</p>
        </section>

        <!-- ===== Acknowledgements ===== -->
        <section aria-labelledby="ack-heading">
            <h2 id="ack-heading">Sources and Acknowledgements</h2>
            <ul>
                <li>Evergreen Energy is a fictional company created for COS10026 at Swinburne University of Technology.</li>
                <li>Page markup and CSS checked with the <a href="https://validator.w3.org/">W3C Markup Validation Service</a> and the <a href="https://jigsaw.w3.org/css-validator/">W3C CSS Validation Service</a>.</li>
                <li>Logo: generated with Claude (Anthropic).</li>
                <li>Generative AI: Claude (Anthropic) was used in Part 2 to help draft the PHP structure, validation and database code. Every member reviewed, tested and edited the code for their own part.</li>
            </ul>

            <!-- Image references (APA 7th) -->
            <h3>Images</h3>
            <ul class="references">
                <li>DC Studio. (n.d.). <em>Photovoltaics plant coworkers brainstorming ways to improve solar cells efficiency. Solar panels factory research and development department staff members working on clean energy solutions, shot A</em> [Photograph]. Vecteezy. <a href="https://www.vecteezy.com/photo/79419485-photovoltaics-plant-coworkers-brainstorming-ways-to-improve-solar-cells-efficiency-solar-panels-factory-research-and-development-department-staff-members-working-on-clean-energy-solutions-shot-a">https://www.vecteezy.com/photo/79419485</a> (Home page, "Who are we?")</li>
                <li>Europosters. (n.d.). <em>Solar energy panel, photovoltaic cell and wind turbine farm power generator in nature landscape for production of renewable green energy is friendly industry</em> [Poster]. <a href="https://www.europosters.ie/solar-energy-panel-photovoltaic-cell-and-wind-turbine-farm-power-generator-in-nature-landscape-for-production-of-renewable-green-energy-is-friendly-industry-f278385140">https://www.europosters.ie/solar-energy-panel-photovoltaic-cell-and-wind-turbine-farm-power-generator-in-nature-landscape-for-production-of-renewable-green-energy-is-friendly-industry-f278385140</a> (Home page banner)</li>
                <li>Lonely Planet. (n.d.). <em>Sydney</em>. <a href="https://www.lonelyplanet.com/destinations/australia/new-south-wales/sydney">https://www.lonelyplanet.com/destinations/australia/new-south-wales/sydney</a> (Home page, Sydney office)</li>
                <li>Munday, B. (2024, October 30). <em>Melbourne's skyline: Where to get the best views of the city</em>. Time Out Melbourne. <a href="https://www.timeout.com/melbourne/things-to-do/where-to-see-melbournes-city-skyline">https://www.timeout.com/melbourne/things-to-do/where-to-see-melbournes-city-skyline</a> (Home page, Melbourne office)</li>
                <li>skynesher. (2024). <em>Team of architects working on plants at construction site</em> [Photograph]. iStock. <a href="https://www.istockphoto.com/photo/team-of-architects-working-on-plants-at-construction-site-gm2152308895-573328472">https://www.istockphoto.com/photo/team-of-architects-working-on-plants-at-construction-site-gm2152308895-573328472</a> (Home page, "What Do We Do?")</li>
                <li>The Telegraph. (n.d.). <em>Brisbane guide</em>. <a href="https://www.telegraph.co.uk/travel/plan-your-australian-holiday/queensland/brisbane-guide/">https://www.telegraph.co.uk/travel/plan-your-australian-holiday/queensland/brisbane-guide/</a> (Home page, Brisbane office)</li>
            </ul>

            <!-- Websites used for HTML and CSS beyond the class content (APA 7th) -->
            <h3>HTML and CSS References</h3>
            <p>These websites were used for HTML and CSS beyond the class content on every page of this site: the Home, Job Description, Apply and About Us pages, and the shared navigation bar and footer.</p>
            <ul class="references">
                <li>MDN Web Docs. (n.d.). <em>MDN Web Docs</em>. Mozilla. <a href="https://developer.mozilla.org/">https://developer.mozilla.org/</a></li>
                <li>W3Schools. (n.d.). <em>W3Schools online web tutorials</em>. <a href="https://www.w3schools.com/">https://www.w3schools.com/</a></li>
                <li>PHP Group. (n.d.). <em>PHP manual</em>. <a href="https://www.php.net/manual/en/">https://www.php.net/manual/en/</a></li>
            </ul>
        </section>
    </main>
<?php include "footer.inc"; ?>
