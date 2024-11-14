<?php session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 400px;
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-container input[type="email"],
        .form-container input[type="password"],
        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background-color: #45a049;
        }
        .form-container .error {
            color: red;
            margin-bottom: 20px;
        }
        .form-container .success {
            color: green;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            .form-container {
                padding: 15px;
            }
            .form-container h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form action="nodb_login.php" method="post">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Pass" required>
            <input type="submit" value="Login">
        </form>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $emailHash = sha1($email);

    // Define the file path
    $file_path = "json_accounts/$emailHash.json";

    // Read existing users from the file
    if (file_exists($file_path)) {
        $json_data = file_get_contents($file_path);
        $users = json_decode($json_data, true);

        // Check if the email exists and verify the password
        foreach ($users as $user) {
            if ($user['email'] == $email && password_verify($senha, $user['senha'])) {
                echo "<div class='form-container'><div class='success'>Successful login. Welcome, " . htmlspecialchars($user['nome']) . "!</div>";
                $_SESSION['user'] = $user['nome'];

                echo " <a href='index.php'> Continue</a></div>";  
    
                exit;
            }
        }

        echo "<div class='form-container'><div class='error'>E-mail or password wrong.</div></div>";
    } else {
        echo "<div class='form-container'><div class='error'>Account not found</div></div>";
    }
} else {

    echo "<div align='center'><a href='nodb__register.php'>Create account</a></a>";

}
?>

    </div>
</body>
</html>