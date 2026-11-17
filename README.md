# G03 Evergreen Energy, Part 2 (Server-Side & Dynamic Web Programming)

COS10026 Web Technology Project, Group 03. Part 2 turns the static Part 1 site
([Part 1 repo](https://github.com/raad106729165/G03-Sustainable-Energy-Solutions-Company))
into a PHP and MySQL web application.

## Run it locally (XAMPP)

1. Copy the `project2` folder into `C:\xampp\htdocs\` (Mac: `/Applications/XAMPP/htdocs/`).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin, choose **Import**, and import `project2/evergreen_db.sql`.
   It creates the `evergreen_db` database, all four tables and sample data.
4. Visit http://localhost/project2/index.php
5. HR manager login: http://localhost/project2/login.php, username `admin`, password `admin`.

`settings.php` uses host `localhost`, user `root`, **no password**, database `evergreen_db`.

## Folder structure

```
project2/
├── index.php, jobs.php, apply.php, about.php   public pages (Part 1 pages converted)
├── process_eoi.php     validates and stores applications (POST only)
├── search.php          job search results (search bars on index.php and jobs.php)
├── login.php, logout.php, manage.php           HR manager area
├── header.inc, nav.inc, footer.inc             shared layout includes
├── functions.inc       shared helpers (DB connection, sanitising, CSRF, job cards)
├── settings.php        database settings
├── evergreen_db.sql    database: jobs, eoi, users, about
├── .htaccess           blocks direct access to .inc and .sql files
├── images/             page content images
└── styles/
    ├── style.css
    └── images/         images used by CSS (hero background)
```

## Brief checklist

| Requirement | Where |
|---|---|
| Shared header, nav and footer as includes | `header.inc`, `nav.inc`, `footer.inc` |
| `settings.php` used for every DB connection, no password | `settings.php`, `db_connect()` in `functions.inc` |
| `eoi` table with every apply field and `status` (New, Current, Final; default New) | `evergreen_db.sql`, also created in code by `process_eoi.php` |
| Form posts to `process_eoi.php`, shows the new EOInumber | `apply.php`, `process_eoi.php` |
| Direct URL access to `process_eoi.php` blocked | redirects to `apply.php` unless it is a POST from the form |
| All validation on the server; browser validation off | `novalidate`, no `required`/`pattern`; rules in `process_eoi.php` |
| Jobs loaded from a `jobs` table, with a working search bar | `jobs.php`, `search.php` |
| `users` table, login page, `admin`/`admin` | `login.php`, `evergreen_db.sql` |
| Manager queries: list all, by job ref, by name, delete by job ref, change status, choose sort field | `manage.php` |
| `about` table with Part 1 and Part 2 contributions | `about.php`, `evergreen_db.sql` |
| No JavaScript | none used |

### Security features (for HD)

- Passwords stored with `password_hash()`; checked with `password_verify()`.
- Account locks for 5 minutes after 3 wrong passwords.
- Session ID regenerated at login; manager session expires after 15 idle minutes.
- Prepared statements for every query that uses input; sort column taken from a whitelist.
- CSRF tokens on the application form, login form and every manage action.
- Input trimmed, slashes stripped and HTML escaped; all output escaped.
- Delete needs a confirmation tick box; `.inc` and `.sql` files cannot be opened in the browser.

## Part 2 task split (proposed, edit in `evergreen_db.sql` `about` table if it changes)

| Member | Part 2 files |
|---|---|
| Rayahen Raad (team leader) | `settings.php`, `login.php`, `logout.php`, `manage.php`, `users` and `about` tables, `about.php` |
| Redha Waseem | `index.php` conversion and search bar, presentation slides |
| Marwa Noor Nasir | `header.inc`, `nav.inc`, `footer.inc`, `search.php` |
| Hiba Imran Ahmad | `apply.php`, `process_eoi.php`, `eoi` table |
| Zainab Umar | `jobs` table, `jobs.php` |

## Workflow

| Step | What to do |
|---|---|
| 1 | Create your own branch before editing. |
| 2 | Edit only your own files. Commit from your own GitHub account. |
| 3 | Open a Pull Request into `main` when your part is ready. |
| 4 | The repo owner reviews and merges into `main`. |

## Submission (due Sunday 6 Dec 2026, 11:59pm)

- `G03_part2.zip` containing the `project2` folder (with `evergreen_db.sql`).
- Group presentation slides.
- This repository link as a Canvas submission comment.
- Individual slides and Peer & Self Evaluation form (each member).

Generative AI acknowledgement: Claude (Anthropic) helped draft the Part 2 PHP structure,
validation and database code. Each member reviewed, tested and edited their own part.
