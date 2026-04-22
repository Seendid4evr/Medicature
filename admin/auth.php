<?php
// Admin authentication - checks the is_admin flag set at login
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

if (!getUserIsAdmin()) {
    header('Location: ../pages/dashboard.php');
    exit;
}
