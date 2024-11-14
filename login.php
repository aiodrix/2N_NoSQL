<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Redirect to dashboard if already logged in
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Create database connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

$error = '';
$success = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize input
        $login_identifier = trim($_POST['login_identifier'] ?? ''); // Can be username or email
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($login_identifier) || empty($password)) {
            throw new Exception("Please enter both username/email and password.");
        }
        
        // Prepare SQL statement to check for user - now including balance
        $stmt = $conn->prepare("
            SELECT user_id, username, password, status, balance 
            FROM users 
            WHERE (username = ? OR email = ?) 
            LIMIT 1
        ");
        
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $stmt->bind_param("ss", $login_identifier, $login_identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid username/email or password.");
        }
        
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception("Invalid username/email or password.");
        }
        
        // Check if account is active
        if ($user['status'] !== 'active') {
            throw new Exception("Your account is not active. Please contact support.");
        }
        
        // Update last login timestamp
        $update_stmt = $conn->prepare("
            UPDATE users 
            SET last_login = CURRENT_TIMESTAMP 
            WHERE user_id = ?
        ");
        
        if (!$update_stmt) {
            throw new Exception($conn->error);
        }
        
        $update_stmt->bind_param("i", $user['user_id']);
        $update_stmt->execute();
        $update_stmt->close();
        
        // Set session variables in user array
        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'username' => $user['username'],
            'balance' => $user['balance']
        ];
        
        // Set success message and redirect
        $success = "Login successful! Redirecting...";
        header("refresh:2;url=index.php");
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .brand-title {
            color: #0d6efd;
            font-weight: bold;
            margin-bottom: 2rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="login-container">
            <h2 class="text-center brand-title">Login to Your Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <div class="form-floating mb-3">
                    <input type="text" 
                           class="form-control" 
                           id="login_identifier" 
                           name="login_identifier" 
                           placeholder="Username or Email"
                           value="<?php echo isset($_POST['login_identifier']) ? htmlspecialchars($_POST['login_identifier']) : ''; ?>"
                           required>
                    <label for="login_identifier">Username or Email</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Password"
                           required>
                    <label for="password">Password</label>
                </div>
                
                <div class="login-options">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-decoration-none">Forgot Password?</a>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    <a href="register.php" class="btn btn-outline-secondary">Create New Account</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>