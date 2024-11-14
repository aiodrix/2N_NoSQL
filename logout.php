<?php
session_start(); // Start the session

// Check if the user is logged in
if (isset($_SESSION['user'])) {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Redirect to login page or another page
    header("Location: login.php"); // Change "login.php" to the desired redirect page
    exit();
} else {
    // If the user is not logged in, redirect to the login page directly
    header("Location: login.php");
    exit();
}
?>
