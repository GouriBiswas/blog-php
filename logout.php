<?php
require_once 'includes/functions.php';

// Destroy session
session_destroy();

// Redirect to login page
redirect('login.php');
?>