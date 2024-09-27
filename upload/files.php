<?php
function listFilesInDirectory($dir) {
    // Ensure the directory exists
    if (!is_dir($dir)) {
        echo "Directory not found: $dir\n";
        return;
    }

    // Open the directory
    $files = scandir($dir);

    // Loop through each file/folder in the directory
    foreach ($files as $file) {
        // Ignore the current and parent directory symbols ('.' and '..')
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Construct full path
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;

        // If it's a directory, recurse into it
        if (is_dir($filePath)) {
            echo "<h1>Files</h1>";
            listFilesInDirectory($filePath); // Recursively scan the directory
        } else {
            // If it's a file, create a clickable link that opens the file in a new tab
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath); // Make path relative for web access
            echo "<a href='$relativePath' target='_blank'>$file</a><br>";
        }
    }
}

// Specify the root directory 'users_zip'
$rootDir = 'users_zip';

// Start scanning from the root directory
listFilesInDirectory($rootDir);
?>
