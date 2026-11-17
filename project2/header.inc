<?php
/*
 * header.inc - shared <head> and opening <body> for every page.
 * Original header design by Marwah, edited by Rayahen Raad (Part 1).
 * Converted to a PHP include for Part 2.
 *
 * Each page sets these variables before including this file:
 *   $page_title, $page_description, $page_keywords, $page_author,
 *   $body_class, and optionally $page_style (embedded CSS).
 */
$page_title       = $page_title       ?? "Evergreen Energy";
$page_description = $page_description ?? "Evergreen Energy: sustainable energy solutions and careers.";
$page_keywords    = $page_keywords    ?? "Evergreen Energy, renewable energy, careers";
$page_author      = $page_author      ?? "Group G03";
$body_class       = $body_class       ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo e($page_description); ?>">
    <meta name="keywords" content="<?php echo e($page_keywords); ?>">
    <meta name="author" content="<?php echo e($page_author); ?>">
    <title><?php echo e($page_title); ?> | Evergreen Energy</title>
    <link rel="stylesheet" href="styles/style.css">
<?php if (!empty($page_style)) : ?>
    <style>
<?php echo $page_style; ?>
    </style>
<?php endif; ?>
</head>
<body class="<?php echo e($body_class); ?>">
    <a href="#main" class="skip-link">Skip to main content</a>
