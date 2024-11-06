<?php
session_start();

$_SESSION['user'] = "test";

// File upload configuration
$uploadDir = 'files/';
$blockDir = 'block/';
$maxFileSize = 10 * 1024 * 1024; // 10 MB

// Ensure the block directory exists
if (!is_dir($blockDir)) {
    mkdir($blockDir, 0755, true);
}

// Helper function to create a block
function createBlock($username, $fileHash, $fileName, $prevHash) {
    global $blockDir;
    
    // Create block data
    $blockData = [
        'username' => $username,
        'file_name' => $fileName,
        'file_hash' => $fileHash,
        'timestamp' => date('Y-m-d H:i:s'),
        'prev_hash' => $prevHash,
    ];
    
    // Serialize block data to JSON
    $blockContent = json_encode($blockData, JSON_PRETTY_PRINT);
    
    // Create a block file (named by current timestamp)
    $blockFileName = $blockDir . time() . '.block';
    
    // Save the block content
    file_put_contents($blockFileName, $blockContent);
    
    return hash('sha256', $blockContent); // Return the hash of this block
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['uploadedFile'])) {
    $file = $_FILES['uploadedFile'];
    
    // Check file size
    if ($file['size'] > $maxFileSize) {
        die('Error: File size exceeds the maximum limit of 10MB.');
    }
    
    // Check file extension (disallow .php files)
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($fileExtension == 'php') {
        die('Error: PHP files are not allowed.');
    }
    
    // Create the files directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a SHA256 hash for the file
    $fileHash = hash_file('sha256', $file['tmp_name']);
    $newFileName = $fileHash . '.' . $fileExtension;
    $filePath = $uploadDir . $newFileName;

    // Check if the file already exists
    if (file_exists($filePath)) {
        die('Error: File already exists.');
    }

    // Move the uploaded file to the 'files' directory
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo "File successfully uploaded.";

        // Check if $_SESSION['user'] is set and not empty
        if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
            $user = $_SESSION['user'];
            $userDir = 'users/' . $user . '/';
            
            // Create user directory if it doesn't exist
            if (!is_dir($userDir)) {
                mkdir($userDir, 0755, true);
            }

            // Save file hash in the user's directory
            $hashFile = $userDir . $fileHash;
            $fileHandle = fopen($hashFile, 'a'); // Open file in append mode
            
            if ($fileHandle) {
                fwrite($fileHandle, ""); // Write the hash to the file
                fclose($fileHandle);
                echo " Hash saved to user's directory.";
            } else {
                echo "Error: Failed to write hash to the user's directory.";
            }

            // Create a new block in the blockchain
            $prevHash = file_exists($blockDir . 'last_block.hash') ? file_get_contents($blockDir . 'last_block.hash') : '0';
            $newBlockHash = createBlock($user, $fileHash, $file['name'], $prevHash);
            
            // Save the new block's hash as the last block
            file_put_contents($blockDir . 'last_block.hash', $newBlockHash);
            
            echo " New block created in the blockchain.";
        } else {
            echo "Error: No user session found.";
        }
    } else {
        echo "Error: Failed to upload file.";
    }
}
?>

<!-- HTML Form for file upload -->
<form action="" method="post" enctype="multipart/form-data">
    Select file to upload (Max 10MB):
    <input type="file" name="uploadedFile" required>
    <input type="submit" value="Upload File">
</form>
