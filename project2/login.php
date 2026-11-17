<?php
/*
 * login.php - HR manager login. Protects manage.php.
 * Group G03, COS10026 Part 2.
 *
 * Security:
 * - Passwords are stored as password_hash() hashes in the users table,
 *   never as plain text, and checked with password_verify().
 * - Prepared statements stop SQL injection.
 * - After 3 wrong passwords the account is locked for 5 minutes.
 * - The session ID is regenerated on login (prevents session fixation).
 * - The form carries a CSRF token.
 * - Error messages do not reveal whether the username exists.
 */
require_once "functions.inc";

const MAX_ATTEMPTS = 3;
const LOCK_MINUTES = 5;

if (!empty($_SESSION["manager"])) {
    header("Location: manage.php");
    exit();
}

$error    = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = sanitise_input($_POST["username"] ?? "");
    $password = $_POST["password"] ?? ""; // not escaped: compared as a hash only

    if (!csrf_valid($_POST["csrf_token"] ?? "")) {
        $error = "Your session expired. Please try again.";
    } elseif ($username === "" || $password === "") {
        $error = "Please enter both a username and a password.";
    } elseif (!($conn = db_connect())) {
        $error = "Login is unavailable right now. Please try again later.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT user_id, password_hash, failed_attempts,
                    TIMESTAMPDIFF(SECOND, NOW(), locked_until) AS lock_seconds
             FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $account = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        // Lock time is worked out inside MySQL so PHP and MySQL time zones cannot disagree.
        if ($account && $account["lock_seconds"] !== null && (int) $account["lock_seconds"] > 0) {
            $minutes = (int) ceil((int) $account["lock_seconds"] / 60);
            $error = "Too many failed attempts. This account is locked for " . $minutes . " more minute(s).";
        } elseif ($account && password_verify($password, $account["password_hash"])) {
            // Success: reset the counter and start a fresh session.
            $stmt = mysqli_prepare($conn, "UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "i", $account["user_id"]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            session_regenerate_id(true);
            $_SESSION["manager"]       = $username;
            $_SESSION["last_activity"] = time();
            unset($_SESSION["csrf_token"]);
            header("Location: manage.php");
            exit();
        } else {
            if ($account) {
                $attempts = (int) $account["failed_attempts"] + 1;
                if ($attempts >= MAX_ATTEMPTS) {
                    $stmt = mysqli_prepare($conn, "UPDATE users SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL " . LOCK_MINUTES . " MINUTE) WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "i", $account["user_id"]);
                    $error = "Too many failed attempts. This account is locked for " . LOCK_MINUTES . " minutes.";
                } else {
                    $stmt = mysqli_prepare($conn, "UPDATE users SET failed_attempts = ? WHERE user_id = ?");
                    mysqli_stmt_bind_param($stmt, "ii", $attempts, $account["user_id"]);
                    $error = "Incorrect username or password. " . (MAX_ATTEMPTS - $attempts) . " attempt(s) left before a " . LOCK_MINUTES . "-minute lock.";
                }
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            } else {
                $error = "Incorrect username or password.";
            }
        }
        mysqli_close($conn);
    }
}

$page_title   = "Manager Login";
$page_description = "Login for Evergreen Energy HR managers.";
$body_class   = "admin";
$current_page = "";
include "header.inc";
include "nav.inc";
?>
    <main id="main">
        <section class="login-box" aria-labelledby="login-heading">
            <h1 id="login-heading">HR Manager Login</h1>
<?php if (isset($_GET["timeout"])) : ?>
            <p class="notice">You were logged out after 15 minutes of inactivity.</p>
<?php endif; ?>
<?php if (isset($_GET["loggedout"])) : ?>
            <p class="notice">You have logged out.</p>
<?php endif; ?>
<?php if ($error !== "") : ?>
            <p class="notice error" role="alert"><?php echo e($error); ?></p>
<?php endif; ?>
            <form action="login.php" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo e(csrf_token()); ?>">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo e($username); ?>" autocomplete="username">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password">
                </div>
                <button type="submit" class="button">Log in</button>
            </form>
        </section>
    </main>
<?php include "footer.inc"; ?>
