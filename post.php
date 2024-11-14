<?php
session_start();

// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'decenphp'
];

function createPostsTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(255) NOT NULL,
        url VARCHAR(2048) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        thumb VARCHAR(2048) NOT NULL,
        file_hash VARCHAR(64) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (file_hash),
        INDEX (category),
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (!$conn->query($sql)) {
        throw new Exception("Error creating posts table: " . $conn->error);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Create database connection
        $conn = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");

        // Create table if it doesn't exist
        createPostsTable($conn);

        // Retrieve and validate input fields
        $fields = ['user', 'url', 'title', 'description', 'category', 'thumb'];
        $data = [];

        foreach ($fields as $field) {
            $value = trim($_POST[$field] ?? '');
            if (empty($value)) {
                throw new Exception("Error: $field is required.");
            }
            $data[$field] = $value;
        }

        // Generate file hash
        $fileHash = hash('sha256', $data['url']);

        // Check if URL/hash already exists
        $stmt = $conn->prepare("SELECT id FROM posts WHERE file_hash = ?");
        $stmt->bind_param("s", $fileHash);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Error: This URL has already been posted.");
        }

        // Prepare insert statement
        $stmt = $conn->prepare("
            INSERT INTO posts (user_id, url, title, description, category, thumb, file_hash)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("sssssss",
            $data['user'],
            $data['url'],
            $data['title'],
            $data['description'],
            $data['category'],
            $data['thumb'],
            $fileHash
        );

        // Execute the insert
        if (!$stmt->execute()) {
            throw new Exception("Error inserting data: " . $stmt->error);
        }

        // Success response
        $response = [
            'success' => true,
            'message' => 'Post has been saved successfully!',
            'file_hash' => $fileHash,
            'id' => $conn->insert_id
        ];

    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }

    // If it's an AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // For regular form submissions, display message
    if ($response['success']) {
        echo "<br><br>Success: " . $response['message'] . "<br>";
        echo "File Hash: " . htmlspecialchars($response['file_hash']);
    } else {
        echo "<br><br>Error: " . htmlspecialchars($response['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], 
        input[type="url"], 
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <form id="postForm" method="POST">
        <div class="form-group">
            <label for="user">User:</label>
            <input type="text" id="user" name="user" required>
        </div>

        <div class="form-group">
            <label for="url">URL:</label>
            <input type="url" id="url" name="url" required>
        </div>

        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>
        </div>

        <div class="form-group">
            <label for="thumb">Thumbnail URL:</label>
            <input type="url" id="thumb" name="thumb" required>
        </div>

        <button type="submit">Submit Post</button>
    </form>

    <script>
        document.getElementById('postForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.createElement('div');
                messageDiv.className = data.success ? 'success' : 'error';
                messageDiv.textContent = data.message;
                
                const existingMessage = document.querySelector('.success, .error');
                if (existingMessage) {
                    existingMessage.remove();
                }
                
                this.insertBefore(messageDiv, this.firstChild);
                
                if (data.success) {
                    this.reset();
                }
            })
            .catch(error => {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'error';
                messageDiv.textContent = 'An error occurred. Please try again.';
                this.insertBefore(messageDiv, this.firstChild);
            });
        });
    </script>
</body>
</html>