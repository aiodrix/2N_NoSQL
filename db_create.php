<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Define the table creation SQL
$table_sql = "CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    url VARCHAR(255) NOT NULL UNIQUE,
    filename VARCHAR(255),
    hash VARCHAR(64),
    filesize BIGINT,
    latency FLOAT,
    user VARCHAR(255),
    likes INT DEFAULT 0,
    deslikes INT DEFAULT 0,
    weight INT DEFAULT 0,
    source_url VARCHAR(255)
)";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create table if not exists
    $conn->exec($table_sql);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to validate URL and check file extension
function validateURL($url) {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    $path = parse_url($url, PHP_URL_PATH);
    if (!$path) return false;
    
    // Check for file extension (3 or 4 characters after dot)
    if (!preg_match('/\.[a-zA-Z0-9]{3,4}$/', $path)) {
        return false;
    }
    
    return true;
}

// Function to get file details
function getFileDetails($url) {
    $start_time = microtime(true);
    
    $headers = get_headers($url, 1);
    if ($headers === false) {
        return false;
    }
    
    $latency = microtime(true) - $start_time;
    
    $filename = basename($url);
    $filesize = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;
    $hash = hash('sha256', $url);
    
    return [
        'filename' => $filename,
        'filesize' => $filesize,
        'hash' => $hash,
        'latency' => $latency
    ];
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    
    if (validateURL($url)) {
        $fileDetails = getFileDetails($url);
        
        if ($fileDetails) {
            try {
                $stmt = $conn->prepare("INSERT INTO files (url, filename, hash, filesize, latency) 
                                      VALUES (:url, :filename, :hash, :filesize, :latency)");
                
                $stmt->execute([
                    ':url' => $url,
                    ':filename' => $fileDetails['filename'],
                    ':hash' => $fileDetails['hash'],
                    ':filesize' => $fileDetails['filesize'],
                    ':latency' => $fileDetails['latency']
                ]);
                
                $message = "URL successfully added!";
            } catch(PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    $message = "This URL already exists in the database.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
            }
        } else {
            $message = "Could not access the file at the given URL.";
        }
    } else {
        $message = "Invalid URL or file extension.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Submission Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        input[type="url"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .message {
            padding: 10px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Submit URL</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false || strpos($message, 'Invalid') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="url" name="url" placeholder="Enter URL" required>
            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>