<?php
// Define the directory path
$usersDir = 'users';

// Check if the directory exists
if (is_dir($usersDir)) {
    // Initialize an array to hold folder names and their file counts
    $folders = [];
    $totalFileCount = 0;

    // Open the users directory
    if ($handle = opendir($usersDir)) {
        // Loop through each folder in the users directory
        while (false !== ($entry = readdir($handle))) {
            // Skip the current and parent directory entries
            if ($entry != '.' && $entry != '..' && is_dir("$usersDir/$entry")) {
                // Count the files in the current folder
                $fileCount = count(glob("$usersDir/$entry/*"));
                // Store the folder name and file count in the array
                $folders[$entry] = $fileCount;
                // Add to the total file count
                $totalFileCount += $fileCount;
            }
        }
        // Close the directory handle
        closedir($handle);
    }

    // Sort the folders array in descending order by file count
    arsort($folders);

    // Display the results
    echo "<h1>Folders in '$usersDir' Directory</h1>";
    echo "<p>Total number of files: $totalFileCount</p>";
    echo "<ul>";
    foreach ($folders as $folder => $count) {
        // Calculate the percentage of total files for each folder
        $percentage = ($totalFileCount > 0) ? ($count / $totalFileCount) * 100 : 0;
        echo "<li><strong>$folder</strong>: $count files (" . number_format($percentage, 2) . "%)</li>";
    }
    echo "</ul>";
} else {
    echo "Directory '$usersDir' does not exist.";
}
?>
