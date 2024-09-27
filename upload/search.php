<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['input']; // Assume the POST variable is named 'input'

    if (!file_exists("links/$input.html")) {
        echo "Page not found.";
        die;
    }

    // The input is not a link, redirect to the corresponding HTML page
    $redirectTo = "links/" . $input . ".html";
    header("Location: $redirectTo");
    exit();
}

?>
