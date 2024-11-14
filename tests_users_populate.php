<?php
// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'decenphp'
];

class DatabaseSetup {
    private $conn;
    
    public function __construct($config) {
        try {
            // First, connect without selecting a database
            $this->conn = new mysqli(
                $config['host'],
                $config['username'],
                $config['password']
            );
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Create database if it doesn't exist
            $this->conn->query("CREATE DATABASE IF NOT EXISTS {$config['database']}");
            
            // Select the database
            $this->conn->select_db($config['database']);
            
        } catch (Exception $e) {
            die("Setup error: " . $e->getMessage());
        }
    }
    
    public function createTables() {
        try {
            // Create users table
            $userTableSQL = "
                CREATE TABLE IF NOT EXISTS users (
                    user_id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    balance DECIMAL(10, 2) DEFAULT 1000.00,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_login TIMESTAMP NULL,
                    status ENUM('active', 'inactive') DEFAULT 'active'
                ) ENGINE=InnoDB;
            ";
            
            // Create invest table
            $investTableSQL = "
                CREATE TABLE IF NOT EXISTS invest (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    file_hash VARCHAR(128) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(user_id)
                ) ENGINE=InnoDB;
            ";
            
            $this->conn->query($userTableSQL);
            $this->conn->query($investTableSQL);
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Error creating tables: " . $e->getMessage());
        }
    }
    
    public function populateUsers() {
        try {
            // Check if users already exist
            $result = $this->conn->query("SELECT COUNT(*) as count FROM users");
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                return "Users table already has data. Skipping population.";
            }
            
            // Sample users data with hashed passwords
            $users = [
                [
                    'username' => 'john_doe',
                    'password' => password_hash('password123', PASSWORD_DEFAULT),
                    'email' => 'john@example.com',
                    'balance' => 2500.00
                ],
                [
                    'username' => 'jane_smith',
                    'password' => password_hash('password456', PASSWORD_DEFAULT),
                    'email' => 'jane@example.com',
                    'balance' => 3500.00
                ],
                [
                    'username' => 'bob_wilson',
                    'password' => password_hash('password789', PASSWORD_DEFAULT),
                    'email' => 'bob@example.com',
                    'balance' => 1500.00
                ],
                [
                    'username' => 'alice_brown',
                    'password' => password_hash('passwordabc', PASSWORD_DEFAULT),
                    'email' => 'alice@example.com',
                    'balance' => 4500.00
                ],
                [
                    'username' => 'charlie_davis',
                    'password' => password_hash('passwordxyz', PASSWORD_DEFAULT),
                    'email' => 'charlie@example.com',
                    'balance' => 5500.00
                ]
            ];
            
            // Prepare insert statement
            $stmt = $this->conn->prepare("
                INSERT INTO users (username, password, email, balance) 
                VALUES (?, ?, ?, ?)
            ");
            
            // Insert each user
            foreach ($users as $user) {
                $stmt->bind_param(
                    "sssd",
                    $user['username'],
                    $user['password'],
                    $user['email'],
                    $user['balance']
                );
                $stmt->execute();
            }
            
            return "Successfully added 5 sample users.";
            
        } catch (Exception $e) {
            throw new Exception("Error populating users: " . $e->getMessage());
        }
    }
    
    public function __destruct() {
        $this->conn->close();
    }
}

// Run the setup
try {
    echo "<h2>Database Setup Process</h2>";
    
    $setup = new DatabaseSetup($db_config);
    
    echo "<p>1. Connecting to database... OK</p>";
    
    echo "<p>2. Creating tables... ";
    $setup->createTables();
    echo "OK</p>";
    
    echo "<p>3. Populating users... ";
    $result = $setup->populateUsers();
    echo $result . "</p>";
    
    echo "<h3>Setup completed successfully!</h3>";
    echo "<p>You can now use the following test accounts:</p>";
    echo "<ul>";
    echo "<li>Username: john_doe / Password: password123</li>";
    echo "<li>Username: jane_smith / Password: password456</li>";
    echo "<li>Username: bob_wilson / Password: password789</li>";
    echo "<li>Username: alice_brown / Password: passwordabc</li>";
    echo "<li>Username: charlie_davis / Password: passwordxyz</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>