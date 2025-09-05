<?php
require_once '../config.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to login page with success message
Utilities::redirect('../views/login.php', 'You have been logged out successfully', 'success');
?>
