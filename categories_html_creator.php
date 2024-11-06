<?php
// Set the directory to search
$directory = 'html/';

// Get the list of directories in the 'html' directory
$folders = array_diff(scandir($directory), array('.', '..'));

// Open the 'categories.html' file for writing
$file = fopen('categories.html', 'w');

// Start the HTML output
fwrite($file, "<h2>Categories</h2>");
fwrite($file, "<ul>");

// Loop through the folders and write the links to the file
foreach ($folders as $folder) {
    if (is_dir($directory . $folder)) {
        fwrite($file, "<li><a href='{$directory}{$folder}/1.html'>{$folder}</a></li>");
    }
}

// Close the unordered list and the HTML file
fwrite($file, "</ul>");
fclose($file);
?>