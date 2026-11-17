<?php
/*
 * logout.php - ends the manager's session. Group G03, COS10026 Part 2.
 */
require_once "functions.inc";

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 3600, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
header("Location: login.php?loggedout=1");
exit();
