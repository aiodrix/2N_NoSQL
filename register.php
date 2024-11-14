<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Function to handle database connection
function connectDB() {
    global $servername, $username, $password, $dbname;
    
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Function to create users table
function createUsersTable($conn) {
    $userTableSQL = "
        CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            pix VARCHAR(100),
            paypal VARCHAR(100),
            whatsapp VARCHAR(20),
            phone VARCHAR(20),
            balance DECIMAL(10, 2) DEFAULT 1000.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            status ENUM('active', 'inactive') DEFAULT 'active'
        ) ENGINE=InnoDB;
    ";
    
    try {
        if (!$conn->query($userTableSQL)) {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        die("Error creating users table: " . $e->getMessage());
    }
}

// Function to validate registration data
function validateRegistrationData($username, $password, $confirm_password, $email) {
    $errors = [];
    
    if (empty($username) || empty($password) || empty($email)) {
        $errors[] = "Username, password, and email are required fields.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    return $errors;
}

// Function to check for existing user
function checkExistingUser($conn, $username, $email) {
    try {
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['username'] === $username) {
                return "Username already exists.";
            }
            return "Email already exists.";
        }
        
        return null;
    } catch (Exception $e) {
        return "Error checking existing user: " . $e->getMessage();
    }
}

// Function to register new user
function registerUser($conn, $username, $password, $email, $pix, $paypal, $whatsapp, $phone) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO users (username, password, email, pix, paypal, whatsapp, phone) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bind_param("sssssss", 
            $username, 
            $hashed_password, 
            $email, 
            $pix, 
            $paypal, 
            $whatsapp, 
            $phone
        );
        
        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        return "Registration failed: " . $e->getMessage();
    }
}

// Initialize variables
$error = '';
$success = '';

// Process registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database
    $conn = connectDB();
    createUsersTable($conn);
    
    // Get and sanitize input data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = trim($_POST['email']);
    $pix = trim($_POST['pix'] ?? '');
    $paypal = trim($_POST['paypal'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate input
    $validation_errors = validateRegistrationData($username, $password, $confirm_password, $email);
    
    if (!empty($validation_errors)) {
        $error = implode("<br>", $validation_errors);
    } else {
        // Check for existing user
        $existing_user_error = checkExistingUser($conn, $username, $email);
        
        if ($existing_user_error) {
            $error = $existing_user_error;
        } else {
            // Register new user
            $registration_result = registerUser($conn, $username, $password, $email, $pix, $paypal, $whatsapp, $phone);
            
            if ($registration_result === true) {
                $success = "Registration successful! Please log in.";
                // Redirect to login page after 2 seconds
                header("refresh:2;url=login.php");
            } else {
                $error = $registration_result;
            }
        }
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="text-center mb-4">Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
                <!-- Required Fields Section -->
                <div class="mb-4">
                    <h5>Required Information</h5>
                    <hr>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label required-field">Username</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label required-field">Password</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               required minlength="8">
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label required-field">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               required minlength="8">
                    </div>
                </div>
                
                <!-- Payment Information Section -->
                <div class="mb-4">
                    <h5>Payment Information</h5>
                    <hr>
                    
                    <div class="mb-3">
                        <label for="pix" class="form-label">PIX Key</label>
                        <input type="text" class="form-control" id="pix" name="pix" 
                               value="<?php echo isset($_POST['pix']) ? htmlspecialchars($_POST['pix']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="paypal" class="form-label">PayPal Email</label>
                        <input type="email" class="form-control" id="paypal" name="paypal" 
                               value="<?php echo isset($_POST['paypal']) ? htmlspecialchars($_POST['paypal']) : ''; ?>">
                    </div>
                </div>
                
                <!-- Contact Information Section -->
                <div class="mb-4">
                    <h5>Contact Information</h5>
                    <hr>
                    
                    <div class="mb-3">
                        <label for="whatsapp" class="form-label">WhatsApp</label>
                        <input type="tel" class="form-control" id="whatsapp" name="whatsapp" 
                               value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>" 
                               placeholder="+1234567890">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Alternative Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                               placeholder="+1234567890">
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Register</button>
                    <a href="login.php" class="btn btn-outline-secondary">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>