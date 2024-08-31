<?php
// Set the directory to search
$directory = 'html/';

// Get the list of directories in the 'html' directory
$folders = array_diff(scandir($directory), array('.', '..'));

// Create an array to store the categories
$categories = [];

// Loop through the folders and add them to the categories array
foreach ($folders as $folder) {
    if (is_dir($directory . $folder)) {
        $categories[] = [
            'name' => $folder,
            'link' => "{$directory}{$folder}/1.html"
        ];
    }
}

// Convert the categories array to a JSON string without escaping slashes
$json_data = json_encode($categories, JSON_UNESCAPED_SLASHES);

// Save the JSON data to a file
//file_put_contents('categories.json', $json_data);

$file = fopen('categories.json', 'w');
fwrite($file, $json_data);
fclose($file);  

// Output a link to the JSON file
//echo "<a href='categories.json'>View Categories</a>";
?>
