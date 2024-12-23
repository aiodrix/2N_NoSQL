<?php error_reporting(0); session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="date"],
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
        <h2>Registration</h2>
        <form action="nodb_register.php" method="post">
            <input type="text" name="nome" placeholder="Username" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="date" name="data_nascimento" placeholder="Data de Nascimento" required>
            <input type="text" name="chave_pix" placeholder="Chave Pix">
            <input type="text" name="paypal" placeholder="PayPal required">
            <br>At least 10 characters, one uppercase letter and one special character <br>
            <input type="password" name="senha" placeholder="Password" required>
            <input type="password" name="repetir_senha" placeholder="Repeat password" required>
            <input type="submit" value="Register">
        </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

function strongPassword($password) {
    // Check if the password is at least 10 characters long
    if (strlen($password) < 10) {
        return false;
    }

    // Check if the password has at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Check if the password has at least one special character
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        return false;
    }

    // If all conditions are met
    return true;
}

    // Retrieve form data
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $data_nascimento = $_POST['data_nascimento'];
    $chave_pix = $_POST['chave_pix'];
    $paypal = $_POST['paypal'];
    $senha = $_POST['senha'];
    $repetir_senha = $_POST['repetir_senha'];

    $emailHash = sha1($email);

    if (strongPassword($senha)) {
        //echo "The password is strong.";
    } else {
        echo "<div class='form-container'><div class='error'>The password does not meet the strength requirements.</div></div>";
        exit;
    }

    // Check if passwords match
    if ($senha !== $repetir_senha) {
        echo "<div class='form-container'><div class='error'>Passwords do not match. Please try again.</div></div>";
        exit;
    }

    // Hash the password for security
    $hashed_password = password_hash($senha, PASSWORD_DEFAULT);

    // Create user data array
    $new_user = [
        'nome' => $nome,
        'email' => $email,
        'data_nascimento' => $data_nascimento,
        'chave_pix' => $chave_pix,
        'paypal' => $paypal,
        'senha' => $hashed_password
    ];

    // Write the updated user data to the file
    if (!file_exists('json_accounts')) {
        mkdir('json_accounts', 0777, true);
    }

    // Define the file path
    $file_path = "json_accounts/$emailHash.json";

    if (file_exists($file_path)) {
        echo "<div class='form-container'><div class='error'>Email already registered. Please use a different email.</div></div>";
        exit;
    }

    // Read existing users from the file or create a new array if the file doesn't exist
    if (file_exists($file_path)) {
        $json_data = file_get_contents($file_path);
        $users = json_decode($json_data, true);
    } else {
        $users = [];
    }

    // Check if the email already exists
    foreach ($users as $user) {
        if ($user['email'] == $email) {
            echo "<div class='form-container'><div class='error'>Email already registered. Please use a different email.</div></div>";
            exit;
        }
    }

    // Add the new user to the array
    $users[] = $new_user;

    $file = fopen("$file_path", 'w');
    fwrite($file, json_encode($users));
    fclose($file);       

    //file_put_contents($file_path, json_encode($users));
  
    $_SESSION['user'] = $nome;

    echo "<div class='form-container'><div class='success'>Registration successful. <a href='index.php'>Continue</a>.</div></div>";
}
?>

    </div>
</body>
</html>