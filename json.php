<?php

session_start();

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
}

?>

<html>
<link rel="icon" href="screens/logo.jpg" type="image/jpeg">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5; /* Light gray background */
        color: #333; /* Dark gray text */
        margin: 0;
        padding: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh; /* Full viewport height */
    }

    .minimalist-form {
        background-color: #fff; /* White background for the form */
        border-radius: 8px; /* Rounded corners */
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        padding: 20px;
        width: 300px; /* Fixed width for the form */
    }

    input[type="text"],
    input[type="url"],
    textarea {
        width: 100%; /* Full width inputs */
        padding: 10px; /* Padding inside inputs */
        margin-bottom: 15px; /* Space between inputs */
        border: 1px solid #ccc; /* Light gray border */
        border-radius: 4px; /* Slightly rounded corners for inputs */
        font-size: 14px; /* Font size for inputs */
        transition: border-color 0.3s; /* Smooth transition for focus effect */
    }

    input[type="text"]:focus,
    input[type="url"]:focus,
    textarea:focus {
        border-color: #888; /* Darker gray on focus */
        outline: none; /* Remove default outline */
    }

    textarea {
        resize: none; /* Disable resizing of the textarea */
        height: 80px; /* Fixed height for the textarea */
    }

    input[type="submit"] {
        background-color: #333; /* Dark gray background for submit button */
        color: #fff; /* White text color for button */
        border: none; /* Remove border */
        padding: 10px;
        border-radius: 4px; /* Slightly rounded corners for button */
        cursor: pointer; /* Pointer cursor on hover */
        font-size: 16px; /* Larger font size for button */
        transition: background-color 0.3s; /* Smooth transition for hover effect */
    }

    input[type="submit"]:hover {
        background-color: #555; /* Darker gray on hover */
    }
</style>

<!-- HTML Form for user input -->
<form action="" method="post">
    User <input type="text" name="user" value="<?php if (isset($_SESSION['user'])) {echo $user;} ?>"<br>
    URL <input type="url" name="url" required><br>
    Title <input type="text" name="title"><br>
    Description <textarea name="description"></textarea><br>
    Category <input type="text" name="category"><br>
    Thumbnail URL <input type="url" name="thumb"><br>
    <input type="submit" value="Save">

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve input fields
    $user = trim($_POST['user']);
    $url = trim($_POST['url']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $thumb = trim($_POST['thumb']);

   
    // Check for required fields
    if (empty($user) || empty($url) || empty($title) || empty($description) || empty($category) || empty($thumb)) {
        die('Error: All fields are required.');
    }

    // Create directories if they do not exist
    $jsonDir = 'files/';
    if (!is_dir($jsonDir)) {
        mkdir($jsonDir, 0755, true);
    }
    
    $categoriesDir = 'categories/' . $category . '/';
    if (!is_dir($categoriesDir)) {
        mkdir($categoriesDir, 0755, true);
    }

    // Generate a SHA256 hash of the URL
    $fileHash = hash('sha256', $url);
    $jsonFilePath = $jsonDir . $fileHash . '.json';

    // Check if the file already exists in the json folder
    if (file_exists($jsonFilePath)) {
        //die('Error: File already exists.');
    }

    // Prepare the data to be saved as JSON
    $data = [
        'user' => $user,
        'url' => $url,
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'thumb' => $thumb
    ];

    // Convert the data array to JSON format
    $jsonData = json_encode($data, JSON_PRETTY_PRINT);

    // Save the JSON data to the file
    if (file_put_contents($jsonFilePath, $jsonData) === false) {
        die('Error: Failed to save the JSON file.');
    }

    // Write the hash to a text file inside the category folder
    $hashFile = $categoriesDir . 'files.txt';
    $fileHandle = fopen($hashFile, 'a'); // Open file in append mode
    
    if ($fileHandle) {
        fwrite($fileHandle, $fileHash . "\n"); // Write the hash to the file
        fclose($fileHandle); // Close the file
    } else {
        die('Error: Failed to write the hash in the category folder.');
    }

    // If $_SESSION['user'] is set, create a user folder and save the hash as a text file
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        $userDir = 'users/' . $_SESSION['user'] . '/';
        
        // Create user directory if it doesn't exist
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }

        // Write the hash of the file as a text file inside the user's folder
        $userHashFile = $userDir . $fileHash . '.txt';
        $userFileHandle = fopen($userHashFile, 'w'); // Open file in write mode
        
        if ($userFileHandle) {
            fwrite($userFileHandle, $fileHash . "\n"); // Write the hash to the file
            fclose($userFileHandle); // Close the file
        } else {
            die('Error: Failed to write the hash in the user\'s folder.');
        }
    }

    include("html_creator_2.php");

    // Success message (can be customized to suit your needs)
    echo "<br><br>Success: The JSON file has been saved!<br><a href='$jsonFilePath' target='_blank'>$jsonFilePath</a>";
}


?>

</form>

