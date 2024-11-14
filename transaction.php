<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    //header('Location: login.php');
    //exit();
}

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'decenphp'
];

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Start transaction
        $conn->begin_transaction();

        // Get and validate inputs
        $userId = $_SESSION['user']['id'];
        $fileHash = filter_input(INPUT_POST, 'file_hash', FILTER_SANITIZE_STRING);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (!$fileHash || !$amount) {
            throw new Exception("Invalid input parameters");
        }

        // First, verify that the file hash exists in the files table
        $stmt = $conn->prepare("SELECT id FROM files WHERE hash = ?");
        $stmt->bind_param("s", $fileHash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid file hash: No matching file found");
        }

        // Check user balance
        $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ? FOR UPDATE");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $userBalance = $result->fetch_assoc()['balance'];
        
        if ($userBalance < $amount) {
            throw new Exception("Insufficient balance");
        }

        // Insert into invest table
        $stmt = $conn->prepare("
            INSERT INTO invest (user_id, file_hash, amount, transaction_date) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->bind_param("isd", $userId, $fileHash, $amount);
        $stmt->execute();

        // Update user balance
        $stmt = $conn->prepare("
            UPDATE users 
            SET balance = balance - ? 
            WHERE user_id = ?
        ");
        $stmt->bind_param("di", $amount, $userId);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        
        // Update session balance
        $_SESSION['user']['balance'] = $userBalance - $amount;
        
        $message = "Transaction completed successfully";
        $messageType = "success";

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollback();
        }
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }

    // If it's an AJAX request, return JSON response
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        echo json_encode([
            'success' => $messageType === 'success',
            'message' => $message,
            'new_balance' => $_SESSION['user']['balance'] ?? 0
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Hash Transaction</title>
    <style>
        :root {
            --primary-color: #4CAF50;
            --error-color: #f44336;
            --success-color: #4CAF50;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px var(--shadow-color);
            width: 100%;
            max-width: 500px;
        }

        .form-title {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        button {
            width: 100%;
            padding: 1rem;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #45a049;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            text-align: center;
        }

        .message.error {
            background-color: #ffebee;
            color: var(--error-color);
        }

        .message.success {
            background-color: #e8f5e9;
            color: var(--success-color);
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }

        .balance-display {
            text-align: right;
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .user-info {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1rem;
            }

            input, button {
                padding: 0.7rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="form-title">File Hash Transaction</h1>
        
        <div class="user-info">
            <div>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?></div>
            <div class="balance-display">
                Current Balance: $<span id="userBalance">
                    <?php echo number_format($_SESSION['user']['balance'] ?? 0, 2); ?>
                </span>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form id="transactionForm" method="POST">
            <div class="form-group">
                <label for="fileHash">File Hash</label>
                <input 
                    type="text" 
                    id="fileHash" 
                    name="file_hash" 
                    required 
                    placeholder="Enter file hash"
                    pattern="[a-fA-F0-9]{32,128}"
                    minlength="32"
                    maxlength="128"
                >
                <div class="error-message" id="hashError">Please enter a valid file hash</div>
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input 
                    type="number" 
                    id="amount" 
                    name="amount" 
                    required 
                    step="0.01" 
                    min="0.01"
                    placeholder="Enter amount"
                >
                <div class="error-message" id="amountError">Please enter a valid amount</div>
            </div>

            <button type="submit">Submit Transaction</button>
        </form>
    </div>

<script>
        const form = document.getElementById('transactionForm');
        const fileHash = document.getElementById('fileHash');
        const amount = document.getElementById('amount');
        const hashError = document.getElementById('hashError');
        const amountError = document.getElementById('amountError');
        const userBalance = parseFloat(document.getElementById('userBalance').textContent.replace(',', ''));

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            let isValid = true;

            // Validate file hash
            if (!fileHash.value.match(/^[a-fA-F0-9]{32,128}$/)) {
                hashError.style.display = 'block';
                isValid = false;
            } else {
                hashError.style.display = 'none';
            }

            // Validate amount
            const amountValue = parseFloat(amount.value);
            if (isNaN(amountValue) || amountValue <= 0 || amountValue > userBalance) {
                amountError.style.display = 'block';
                amountError.textContent = amountValue > userBalance ? 
                    'Insufficient balance' : 'Please enter a valid amount';
                isValid = false;
            } else {
                amountError.style.display = 'none';
            }

            if (isValid) {
                // Submit the form using fetch
                fetch(window.location.href, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update balance display
                        document.getElementById('userBalance').textContent = 
                            parseFloat(data.new_balance).toFixed(2);
                        
                        // Show success message
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message success';
                        messageDiv.textContent = data.message;
                        form.insertBefore(messageDiv, form.firstChild);
                        
                        // Reset form
                        form.reset();
                        
                        // Remove message after 3 seconds
                        setTimeout(() => messageDiv.remove(), 3000);
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message error';
                    messageDiv.textContent = error.message;
                    form.insertBefore(messageDiv, form.firstChild);
                    setTimeout(() => messageDiv.remove(), 3000);
                });
            }
        });
    </script>
</body>
</html>