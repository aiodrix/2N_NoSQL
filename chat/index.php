<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['category'])) {
        $_SESSION['category'] = htmlspecialchars(trim($_POST['category']), ENT_QUOTES, 'UTF-8');
        header('Location: messages.php');
        exit;
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
        .landing-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .landing-container h1 {
            font-size: 36px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .landing-container input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            width: 300px;
            margin-bottom: 20px;
        }
        .landing-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .landing-container button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>Welcome to the PHP Chat</h1>
        <form method="post" action="">
            <input type="text" name="category" placeholder="Enter a category or group name" required />
            <button type="submit">Go to Chat</button>
        </form>
    </div>
</body>
</html>
