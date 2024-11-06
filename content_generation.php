<?php
// Specify the directory
$directory = 'files';

// Check if the directory exists
if (!is_dir($directory)) {
    die("Error: Directory '$directory' does not exist.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form input values
    $title = isset($_POST['title']) ? $_POST['title'] : '';
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $thumb = isset($_POST['thumb']) ? $_POST['thumb'] : '';
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $user = isset($_POST['user']) ? $_POST['user'] : '';

    // Get all files in the directory
    $files = scandir($directory);

    // Remove . and .. from the list
    $files = array_diff($files, array('.', '..'));

    // Loop through each file
    foreach ($files as $file) {
        $filepath = $directory . '/' . $file;
        
        // Get the file details
        $filesize = filesize($filepath);
        $filedate = date('Y-m-d H:i:s', filemtime($filepath));
        $filehash = hash_file('sha256', $filepath);
        $json_filename = $directory . '/' . $filehash . '.txt';

        // Prepare the JSON data
        $json_data = [
            'filename' => $file,
            'size' => $filesize,
            'date' => $filedate,
            'title' => $title,
            'description' => $description,
            'thumb' => $thumb,
            'category' => $category,
            'user' => $user
        ];

        // Write the JSON file
        file_put_contents($json_filename, json_encode($json_data, JSON_PRETTY_PRINT));
    }

    echo "JSON files generated successfully!";
} else {
    // Show the form for input if it's a GET request
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Generate JSON Files</title>
    </head>
    <body>
        <h1>Enter Information for JSON Files</h1>
        <form action="" method="POST">
            <label for="title">Title:</label><br>
            <input type="text" id="title" name="title" required><br><br>
            
            <label for="description">Description:</label><br>
            <textarea id="description" name="description" required></textarea><br><br>
            
            <label for="thumb">Thumbnail URL:</label><br>
            <input type="text" id="thumb" name="thumb" required><br><br>
            
            <label for="category">Category:</label><br>
            <input type="text" id="category" name="category" required><br><br>
            
            <label for="user">User:</label><br>
            <input type="text" id="user" name="user" required><br><br>

            <input type="submit" value="Generate JSON Files">
        </form>
    </body>
    </html>
    <?php
}
?>
