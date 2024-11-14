<?php
error_reporting(0);
session_start();

// Function to sanitize input
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Initialize stored content
$stored_content = '';

$category_file = $_GET['hash'];

// Sanitize the category for the directory name (remove unwanted characters)
$sanitized_category = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category_file);

function generateRandomName() {
    // Arrays with sample data
    $fruits = ['Apple', 'Banana', 'Cherry', 'Orange', 'Grapes', 'Mango', 'Peach'];
    $colors = ['Red', 'Green', 'Blue', 'Yellow', 'Purple', 'Orange', 'Pink'];
    $feelings = ['Happy', 'Sad', 'Excited', 'Calm', 'Angry', 'Bored', 'Surprised'];

    // Generate random selections
    $randomFruit = $fruits[array_rand($fruits)];
    $randomColor = $colors[array_rand($colors)];
    $randomFeeling = $feelings[array_rand($feelings)];
    
    // Generate a random number between 0 and 99
    $randomNumber = rand(0, 99);

    // Combine the randomly selected elements and the number
    $randomName = $randomFruit . $randomColor . $randomFeeling . $randomNumber;

    return $randomName;
}

if (empty($_SESSION['nickname'])) {
    $_SESSION['nickname'] = generateRandomName();
}

$category_hash = sha1($sanitized_category);

$directory = 'messages/' . $category_hash;

$file_path = $directory . '/' . $category_hash  . '.html';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize the inputs
    $message = sanitize_input($_POST['message']);
    $category = sanitize_input($_POST['category']);

    // Check if the directory exists, if not, create it
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }

    // Prepare the content to save
    $content = "<div class='message'>";
    $user = sanitize_input($_SESSION['nickname']);
    $content .= "<p class='user'>$user</p>";
    $content .= "<p class='message-text'>$message</p></div>";

    // Check if the file already exists
    if (file_exists($file_path)) {
        // Prepend the new message at the beginning of the file
        $existing_content = file_get_contents($file_path);
        $new_content = $content . $existing_content;
    } else {
        // If the file doesn't exist, the new content is the only content
        $new_content = $content;
    }

    $file = fopen($file_path, 'w');  // Open the file in write mode

    if ($file) {
        fwrite($file, $new_content);  // Write the new content to the file
        fclose($file);  // Close the file after writing
    } else {
        // Handle the error if the file cannot be opened
        echo "Unable to open the file.";
    }

    // Load the stored HTML file content
    $stored_content = file_get_contents($file_path);

    // Output the updated content for Ajax response
    echo $stored_content;
    exit;

} else {
    // Load the stored HTML file content on page load
    if (file_exists($file_path)) {
        $stored_content = file_get_contents($file_path);
    } else {
        $stored_content = '<p>No messages yet.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .chat-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .message {
            display: flex;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .message .user {
            font-weight: bold;
            margin-right: 10px;
            color: #007bff;
        }

        .message .message-text {
            background-color: #f4f4f4;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .input-container {
            display: flex;
            margin-top: 10px;
        }

        .input-container input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .input-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .input-container button:hover {
            background-color: #0056b3;
        }

        .slide-in {
            animation: slide-in 0.5s ease-in-out;
        }

        @keyframes slide-in {
            0% {
                transform: translateY(-100%);
            }
            100% {
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-messages" id="chatMessages">
            <?php echo $stored_content; ?>
        </div>
        <div class="input-container">
            <input type="text" id="messageInput" placeholder="Type your message" />
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        function sendMessage() {
            var message = document.getElementById('messageInput').value.trim();

            if (message !== '') {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Update the chat messages
                        document.getElementById('chatMessages').innerHTML = xhr.responseText;

                        // Apply the slide-in animation
                        document.getElementById('chatMessages').classList.add('slide-in');

                        // Clear the input field
                        document.getElementById('messageInput').value = '';

                        // Remove the animation class after the animation completes
                        setTimeout(function() {
                            document.getElementById('chatMessages').classList.remove('slide-in');
                        }, 500);
                    }
                };

                // Send the message and category (replace 'general' with a dynamic value if needed)
                xhr.send('message=' + encodeURIComponent(message) + '&category=general');
            }
        }
    </script>
</body>
</html>
