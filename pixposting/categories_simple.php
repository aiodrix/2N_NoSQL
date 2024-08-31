<?php
// Set the directory to search
$directory = 'html/';

// Get the list of directories in the 'html' directory
$folders = array_diff(scandir($directory), array('.', '..'));

// Display the list of folders with links
echo "<h2>Folders in the 'html' directory:</h2>";
echo "<ul>";
foreach ($folders as $folder) {
    if (is_dir($directory . $folder)) {
        echo "<li><a href='{$directory}{$folder}/1.html' target='_blank'>{$folder}</a></li>";
    }
}
echo "</ul>";
?>