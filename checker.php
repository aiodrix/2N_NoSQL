<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Checker</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .result {
            margin-top: 10px;
        }
        .found {
            color: green;
            cursor: pointer;
        }
        .not-found {
            color: red;
        }
    </style>
</head>
<body>

<h1>File Checker</h1>

<div>
    <form action="" method="POST">
        <label for="file-name">Enter file name: </label>
        <input type="text" id="file-name" name="fileName" required>
        <button type="submit" name="check-file">Check File</button>
    </form>
</div>

<div id="result" class="result">
    <?php
    if (isset($_POST['check-file'])) {
        $fileName = trim($_POST['fileName']);
        
        if (!empty($fileName)) {
            // List of servers to check
            $servers = [
                'http://localhost/test',
                'http://localhost/new/files',
                'http://server3.com'
            ];
            
            foreach ($servers as $server) {
                $url = $server . '/' . $fileName;
                echo "<p>Checking: $url...</p>";
                
                // Check if file exists on the server
                if (checkFileOnServer($url)) {
                    echo "<p class='found'><a href='$url' target='_blank'>Found: $url</a></p>";
                } else {
                    echo "<p class='not-found'>File not found on: $server</p>";
                }
            }
        } else {
            echo "<p>Please enter a file name.</p>";
        }
    }

    /**
     * Function to check if a file exists on the server.
     */
    function checkFileOnServer($url) {
        $ch = curl_init($url);
        
        // Set options for a HEAD request
        curl_setopt($ch, CURLOPT_NOBODY, true); // Only fetch headers
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return as string
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout after 5 seconds
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Connection timeout
        curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Return true if HTTP status code is 200 (file found)
        return $httpCode == 200;
    }
    ?>
</div>

</body>
</html>
