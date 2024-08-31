<?php
// Set the directory path
$dir_path = 'users/';

// Initialize an array to store folder file counts
$folder_file_counts = array();

// Loop through each item in the directory
foreach (scandir($dir_path) as $item) {
    // Check if the item is a directory (excluding . and ..)
    if (is_dir($dir_path . $item) && $item !== '.' && $item !== '..') {
        // Count the number of files in the directory
        $file_count = count(scandir($dir_path . $item)) - 2;
        
        // Store the folder name and file count in the array
        $folder_file_counts[$item] = $file_count;
    }
}

// Sort the array in descending order based on file counts
arsort($folder_file_counts);

// Display the folder names and file counts
echo "Folders with the most files (descending order):\n";
foreach ($folder_file_counts as $folder => $file_count) {
    echo "Folder: $folder, Files: $file_count\n";
}
?>