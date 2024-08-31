<?php
// Get the protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

// Get the host (domain)
$host = $_SERVER['HTTP_HOST'];

// Get the request URI
//$requestUri = $_SERVER['REQUEST_URI'];

// Construct the full URL
$fullUrl = $protocol . $host;

$directory = 'categories'; // Specify the directory to scan

// Check if the directory exists
if (is_dir($directory)) {
    // Scan the main directory
    $folders = scandir($directory);

    // Loop through each item in the categories directory
    foreach ($folders as $folder) {
        // Skip current and parent directory entries
        if ($folder != '.' && $folder != '..') {
            $folderPath = $directory . '/' . $folder;

            // Check if the item is a directory
            if (is_dir($folderPath)) {

                // Scan the subdirectory for files
                $files = scandir($folderPath);
                foreach ($files as $file) {
                    // Skip current and parent directory entries
                    if ($file != '.' && $file != '..') {
                        echo "$fullUrl/categories/$folder/$file\n"; // List the file
                    }
                }
                
            }
        }
    }

    echo "</ul>"; // Close the main list
} else {
    echo "The 'categories' directory does not exist.";
}
?>
