<?php
require 'config/database.php';
require 'includes/auth.php';

// Jika user tidak login, redirect ke login
if (!isLoggedIn()) {
    redirectByRole('guest');
} else {
    // Redirect ke dashboard sesuai role
    redirectByRole($_SESSION['role']);
}
