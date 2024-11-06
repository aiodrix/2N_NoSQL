<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Creation</title>
    <style>
        /* Reset some default styles */
        body, h1, form {
            margin: 0;
            padding: 0;
        }

        /* Page layout and styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex; /* Use flex layout */
            flex-wrap: wrap; /* Allow wrapping to the next row */
            justify-content: space-between; /* Space between columns */
        }

        label {
            font-weight: bold;
        }

        .column {
            width: calc(50% - 10px); /* Two columns with a little spacing between */
            margin-bottom: 10px;
        }

        input[type="text"],
        textarea,
        select,
        input[type="file"] {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="file"] {
            padding: 6px;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h2>Upload</h2>
    <form action="json_sender.php" method="post" enctype="multipart/form-data">
    <div class="column">
        <label for="thumbnail">Thumbnail:</label><br>
        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
    </div>
    <div class="column">    
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title">
    </div>
        
    <div class="column">
        <label for="user">User:</label><br>
        <input type="text" id="user" name="user">
    </div>
        
    <div class="column">
        <label for="url">URL:</label><br>
        <input type="text" id="url" name="url">
    </div>
        
    <div class="column">
        <label for="description">Description:</label>
        <textarea id="description" name="description"></textarea>
   </div>

   <div class="column">
        <label for="Servers receiver">Servers receiver</label><br>
        <textarea id="receivers" name="receivers"></textarea>
   </div>
        
   <div class="column">
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category">
   </div>
        
    <div class="column">
        <input type="submit" value="Submit">
    </div>
    </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /*/
    // Check if all required fields are present
    $requiredFields = array("thumbnail", "title", "user", "url", "description", "category");
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo "Error: $field is required.";
            exit;
        }
    }
    /*/

    $thumbnailFile = $_FILES["thumbnail"];
    $title = $_POST["title"];   
    $user = $_POST["user"];
    $url = $_POST["url"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $receivers = $_POST["receivers"];

    // Check if the thumbnail file was uploaded successfully
    if ($thumbnailFile["error"] !== UPLOAD_ERR_OK) {
        echo "Error: Thumbnail file upload failed.";
        exit;
    }

    // Define the target URL where to send the data
    $targetUrl = "http://localhost/portfolio/projects/playground/projects/receiverm.php"; // Replace with the actual URL

    $urls = explode("\n", $receivers);
    foreach ($urls as $urlReceivers) {
        $urlReceivers = trim($urlReceivers);

    // Prepare data for POST request
    $postData = array(
        "thumbnail" => new CURLFile($thumbnailFile["tmp_name"], $thumbnailFile["type"], $thumbnailFile["name"]),
        "title" => $title,
        "user" => $user,
        "url" => $url,
        "description" => $description,
        "category" => $category,
        "receivers" => $receivers
    );

    // Initialize cURL session
    $curl = curl_init($urlReceivers);

    // Set the POST data
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

    // Set options for cURL
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL request
    $response = curl_exec($curl);

    // Check for errors
    if (curl_errno($curl)) {
        echo 'Curl error: ' . curl_error($curl);
    } else {
        echo "Content sent successfully.";
    }

    // Close cURL session
    curl_close($curl);
}
} else {
    echo "Status: Invalid request.";
}
?>

</body>
</html>
