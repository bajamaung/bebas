<?php
require 'config/database.php';
require 'includes/auth.php';

if (isLoggedIn()) {
    // Log logout activity
    logActivity($conn, $_SESSION['user_id'], 'Logout', 'User melakukan logout', 'users', $_SESSION['user_id']);
    logout();
} else {
    header("Location: login.php");
}
