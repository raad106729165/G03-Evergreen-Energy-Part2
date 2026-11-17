-- =====================================================================
-- evergreen_db.sql - database for the Evergreen Energy website
-- Group G03, COS10026 Web Technology Project, Part 2
--
-- Import in phpMyAdmin (Import tab) or run:  mysql -u root < evergreen_db.sql
-- Creates the database, all four tables and the starting data.
-- Manager login for the marker: username admin, password admin
-- =====================================================================

CREATE DATABASE IF NOT EXISTS evergreen_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE evergreen_db;
SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- jobs: one row per job on jobs.php. Fields match the Part 1 job cards.
-- List fields hold one item per line and are shown as <li> items.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
    job_ref          CHAR(5)       NOT NULL PRIMARY KEY,
    title            VARCHAR(100)  NOT NULL,
    salary           VARCHAR(100)  NOT NULL,
    reporting_line   VARCHAR(100)  NOT NULL,
    about_role       TEXT          NOT NULL,
    responsibilities TEXT          NOT NULL,
    essential_reqs   TEXT          NOT NULL,
    preferable_reqs  TEXT          NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO jobs (job_ref, title, salary, reporting_line, about_role,
                  responsibilities, essential_reqs, preferable_reqs) VALUES
('WD101', 'Front-End Web Developer', 'AUD $85,000 to $100,000 per year, plus superannuation', 'Head of Digital Platforms', 'Build and maintain our public websites so people can learn about our clean energy solutions, follow our solar and wind projects, and join community programs. You will turn designs into fast, accessible and responsive web pages.', 'Build responsive web pages from design mock-ups using semantic HTML5 and CSS3.
Keep project information pages up to date, including project locations, capacity and progress.
Make sure pages follow WCAG 2.2 AA guidelines so people with disabilities can use them.
Improve page speed and reduce unnecessary resource use through lightweight code and optimised images.
Use Git and GitHub to manage code and review teammates'' work.
Work with designers and the communications team on public campaigns.', 'Bachelor''s degree in IT, Computer Science or a related field, or equivalent experience.
At least two years of front-end development experience.
Strong skills in HTML5, CSS3 and responsive design.
Working knowledge of web accessibility standards.
Experience with Git version control.', 'Experience with JavaScript frameworks such as React or Vue.
Knowledge of sustainable web design practices.
Experience with a content management system such as WordPress.
Interest in renewable energy or climate action.'),
('UX202', 'UX/UI Designer', 'AUD $90,000 to $110,000 per year, plus superannuation', 'Head of Digital Platforms', 'Design clear, inclusive digital experiences that help the public understand clean energy and take part in community programs. You will research user needs and design layouts for our websites and engagement tools.', 'Run user research and usability testing with community members and customers.
Create wireframes, prototypes and final designs for web pages and online forms.
Design clear visual displays of energy data, including savings calculators and project dashboards.
Maintain the company design system so pages look consistent and on-brand.
Design for accessibility, including colour contrast, readable text and keyboard use.
Hand finished designs over to developers and support them during implementation.', 'Degree in Design, Human-Computer Interaction or a related field.
At least three years of UX/UI design experience.
Skills in Figma or a similar design tool.
A portfolio demonstrating user-centred design work.
Understanding of inclusive and accessible design.', 'Experience designing data visualisations.
Basic knowledge of HTML and CSS.
Experience with community or public-sector engagement projects.
Knowledge of the renewable energy industry.'),
('RE201', 'Renewable Energy Project Engineer', 'AUD $95,000 to $115,000 per year, plus superannuation', 'Director of Operations', 'Lead the design and implementation of solar and wind energy projects across Australia. You will work closely with contractors, local councils, and community groups to deliver sustainable energy solutions that reduce carbon emissions and improve energy efficiency.', 'Design and oversee renewable energy installations including solar farms and wind turbines.
Conduct feasibility studies and prepare technical reports for stakeholders.
Ensure compliance with Australian energy regulations and safety standards.
Collaborate with project managers and engineers to meet deadlines and budgets.
Monitor system performance and recommend improvements.', 'Bachelor’s degree in Electrical, Mechanical, or Renewable Energy Engineering.
Minimum three years of project engineering experience.
Strong knowledge of solar PV and wind energy systems.
Excellent communication and teamwork skills.', 'Experience with energy storage solutions.
Knowledge of sustainable construction practices.
Interest in community-based renewable projects.'),
('DA301', 'Data Analyst – Energy Systems', 'AUD $75,000 to $90,000 per year, plus superannuation', 'Head of Analytics', 'Analyse energy production and consumption data to help optimise renewable energy systems. You will provide insights that guide decision-making, improve efficiency, and support the company’s mission of delivering affordable clean energy solutions.', 'Collect and analyse data from solar, wind, and storage systems.
Develop dashboards and reports for management and stakeholders.
Identify trends and recommend strategies to improve efficiency.
Work with engineers to validate system performance data.
Ensure data accuracy and maintain secure databases.', 'Bachelor’s degree in Data Science, Statistics, or a related field.
Proficiency in SQL and data visualisation tools.
Strong analytical and problem-solving skills.
Ability to communicate insights clearly to non-technical audiences.', 'Experience with Python or R for data analysis.
Knowledge of renewable energy systems.
Interest in sustainability and climate action.');

-- ---------------------------------------------------------------------
-- eoi: Expressions of Interest from apply.php (process_eoi.php also
-- creates this table if it is missing).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS eoi;
CREATE TABLE eoi (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample applications so the manage page has data to query.
INSERT INTO eoi (job_ref, first_name, last_name, dob, gender, street, suburb, state,
                 postcode, email, phone, skill_html5, skill_css3, skill_accessibility,
                 skill_git, skill_teamwork, other_skills, status) VALUES
('WD101', 'Sarah', 'Nguyen', '1998-04-12', 'female', '12 Collins St', 'Melbourne', 'VIC', '3000', 'sarah.nguyen@example.com', '0412345678', 1, 1, 1, 1, 0, 'React and Vue', 'New'),
('UX202', 'James', 'Wilson', '1992-09-30', 'male', '8 George St', 'Sydney', 'NSW', '2000', 'james.wilson@example.com', '0298765432', 1, 1, 1, 0, 1, 'Figma prototyping', 'Current'),
('RE201', 'Aisha', 'Khan', '1989-01-05', 'female', '45 Queen St', 'Brisbane', 'QLD', '4000', 'aisha.khan@example.com', '0733334444', 0, 0, 0, 1, 1, 'Solar PV design', 'New'),
('DA301', 'Liam', 'Smith', '2000-07-21', 'undisclosed', '3 Hay St', 'Perth', 'WA', '6000', 'liam.smith@example.com', '0811112222', 0, 0, 0, 1, 1, 'Python, SQL, Power BI', 'Final');

-- ---------------------------------------------------------------------
-- users: HR managers who may open manage.php.
-- Passwords are stored as PHP password_hash() values, never plain text.
-- failed_attempts and locked_until support the 3-attempt lockout.
-- The hash below is for the password: admin
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    failed_attempts  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    locked_until     DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (username, password_hash) VALUES
('admin', '$2y$10$5auA3r4GXgkDuwXKw55fQe8yaiGnue57wt3y1Ue8vF8f2SE2yjFA6');

-- ---------------------------------------------------------------------
-- about: group members and their Part 1 and Part 2 contributions,
-- shown on about.php.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS about;
CREATE TABLE about (
    display_order       TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    full_name           VARCHAR(60)  NOT NULL,
    short_name          VARCHAR(30)  NOT NULL,
    student_id          VARCHAR(12)  NOT NULL,
    team_role           VARCHAR(40)  NOT NULL,
    is_leader           TINYINT(1)   NOT NULL DEFAULT 0,
    part1_contribution  VARCHAR(255) NOT NULL,
    part2_contribution  VARCHAR(255) NOT NULL,
    summary             TEXT         NOT NULL,
    quote_original      TEXT NULL,
    quote_lang          VARCHAR(10) NULL,
    quote_roman         TEXT NULL,
    quote_translation   TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO about (display_order, full_name, short_name, student_id, team_role, is_leader,
                   part1_contribution, part2_contribution, summary,
                   quote_original, quote_lang, quote_roman, quote_translation) VALUES
(1, 'Rayahen Raad', 'Rayahen', '106729165', 'Team leader', 1, 'About Us page, shared stylesheet, GitHub repository and pull request reviews', 'settings.php, users table, login.php, logout.php and manage.php (all HR queries); about table and about.php; database SQL file', 'Built the About Us page and the shared stylesheet, owned both GitHub repositories, reviewed and merged every pull request, and in Part 2 built the secure manager login, the HR manage page and the database-driven About Us page.', 'الي يزرع شجر وهو يدري ماراح يگعد بظلها، هذا على الأقل بدأ يفهم معنى الحياة.', 'ar', '', 'The one who plants trees knowing they will never sit in their shade has at least begun to understand the meaning of life.'),
(2, 'Redha Waseem', 'Redha', '106389158', 'Team member', 0, 'Home page', 'index.php conversion, home page search bar linked to the jobs database, presentation slides', 'Built the Home page, including the hero banner, the projects and branch tables, the office locations and the team testimonial, and in Part 2 converted it to PHP and connected its search bar to our jobs database.', 'ہر چیز ہے محوِ خود نمائی
ہر ذرہ شہیدِ کبریائی', 'ur', '', 'Every particle of nature reflects the greatness of its Creator, so nature deserves respect.'),
(3, 'Marwa Noor Nasir', 'Marwa', '106245690', 'Team member', 0, 'Shared header and footer; assisted with the Home page and two job listings', 'header.inc, nav.inc and footer.inc includes; search.php job search results page', 'Designed the shared header, navigation bar and footer used on every page, added the Renewable Energy Project Engineer and Data Analyst job listings, and in Part 2 turned the shared layout into PHP includes and built the job search page.', 'وقت تو وقت پر بدلتا ہے لیکن انسان کسی بھی وقت بدل سکتا ہے۔', 'ur', 'Waqt to waqt per badalta hai lekin insaan kisi bhi waqt badal sakta hai.', 'Time changes at its time but a person can change any time.'),
(4, 'Hiba Imran Ahmad', 'Hiba', '106246499', 'Team member', 0, 'Job application page', 'apply.php, eoi table and process_eoi.php with full server-side validation', 'Built the Job Application page and its Grid and Flexbox layout, and in Part 2 moved every validation rule to the server in process_eoi.php and designed the eoi table.', 'قدرت كا تحفظ، انسانيت كا تحفظ ہے', 'ur', '', 'Protecting nature is protecting humanity.'),
(5, 'Zainab Umar', 'Zainab', '106194213', 'Team member', 0, 'Job descriptions page', 'jobs table and jobs.php, loading every job description from the database', 'Built the Job Descriptions page, including the Front-End Web Developer and UX/UI Designer listings and the "Why Join Us?" aside, and in Part 2 designed the jobs table and made every job card load from it.', 'گر گر کر سنبھلنا ہی تو زندگی کا ہنر ہے،
منزل انہیں ملتی ہے جن کے حوصلوں میں اثر ہے۔', 'ur', '', 'The art of life is learning to rise after every fall; those with unwavering courage are the ones who reach their destination.');
