<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] == 0) {
        // Get the uploaded file information
        $fileName = $_FILES['zip_file']['name'];
        $fileTmpName = $_FILES['zip_file']['tmp_name'];
        
        // Extract the file name without extension
        $folderName = pathinfo($fileName, PATHINFO_FILENAME);

        // Define the users directory
        $destinationDir = 'users/' . $folderName;

        $destinationDirZip = 'users_zip/' . $folderName;

        // Check if the folder already exists
        if (file_exists($destinationDir)) {
            echo "Error: Folder with the name '$folderName' already exists!";
        } else {
            // Open the zip file
            $zip = new ZipArchive;
            if ($zip->open($fileTmpName) === TRUE) {
                $valid = true; // Flag to check if ZIP contains invalid files
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileInfo = $zip->statIndex($i);
                    $fileNameInZip = $fileInfo['name'];

                    // Check if it's a directory or a PHP file
                    if (substr($fileNameInZip, -1) === '/' || preg_match('/\.php$/i', $fileNameInZip)) {
                        $valid = false;
                        break;
                    }
                }

                // If invalid files are found, show an error message
                if (!$valid) {
                    echo "Error: The zip file contains folders or PHP files, which are not allowed!";
                } else {
                    // Proceed to create the folder and extract the files
                    mkdir($destinationDirZip, 0777, true);
                    $zip->extractTo($destinationDir);
                    echo "Files extracted successfully to '$destinationDirZip'.";

                    // Move the uploaded zip file to the users directory for storage
                    $storedZipPath = $destinationDirZip . '/' . $fileName;
                    if (move_uploaded_file($fileTmpName, $storedZipPath)) {
                        echo "The zip file has been saved in '$storedZipPath'.";
                    } else {
                        echo "Error: Could not store the zip file.";
                    }
                }

                $zip->close();
            } else {
                echo "Error: Failed to open the zip file!";
            }
        }
    } else {
        echo "Error: Please upload a valid zip file!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Zip File</title>
</head>
<body>
    <h1>Upload a Zip File</h1>
    <form action="index.php" method="POST" enctype="multipart/form-data">
        <label for="zip_file">Select a ZIP file:</label>
        <input type="file" name="zip_file" id="zip_file" accept=".zip" required>
        <button type="submit">Upload and Extract</button>
    </form>
</body>
</html>
