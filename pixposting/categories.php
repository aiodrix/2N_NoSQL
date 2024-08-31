<?php
// Set the directory path
$directoryPath = 'html';

// Open the directory
if ($handle = opendir($directoryPath)) {
    echo '<ul>';
    // Read each entry in the directory
    while (false !== ($entry = readdir($handle))) {
        // Skip the current and parent directory entries
        if ($entry != "." && $entry != "..") {
            // Check if the entry is a directory
            if (is_dir("$directoryPath/$entry")) {
                // Create a link to the 1.html file in the subfolder
                echo '<li><a href="?folder=' . urlencode($entry) . '">' . htmlspecialchars($entry) . '</a></li>';
            }
        }
    }
    echo '</ul>';
    // Close the directory handle
    closedir($handle);
}

// Check if a folder is selected
if (isset($_GET['folder'])) {
    $selectedFolder = $_GET['folder'];
    $filePath = "$directoryPath/$selectedFolder/1.html";
    // Check if the file exists
    if (file_exists($filePath)) {
        // Display the content of the file
        echo '<h2>Contents of ' . htmlspecialchars($selectedFolder) . '/1.html:</h2>';
        echo '<iframe src="' . htmlspecialchars($filePath) . '" width="100%" height="600px"></iframe>';
    } else {
        echo '<p>File not found.</p>';
    }
}
?>
