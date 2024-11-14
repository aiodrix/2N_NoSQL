<?php 
session_start();

// Database connection configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'decenphp',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get the logged-in user ID
    $userId = $_SESSION['user']['id'];

    // Define the date range for the last week
    $endDate = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d H:i:s', strtotime('-1 week'));
    $previousWeekStart = date('Y-m-d H:i:s', strtotime('-2 week'));

    // Fetch the investment and likes data for the user's files
    $investQuery = "
        SELECT 
            i.file_hash, i.amount, i.transaction_date,
            (SELECT COUNT(*) FROM likes l
                JOIN files f ON l.file_id = f.id
                WHERE f.hash = i.file_hash AND l.action = 'like'
                AND l.timestamp BETWEEN :start_date AND :end_date) AS current_week_likes,
            (SELECT COUNT(*) FROM likes l
                JOIN files f ON l.file_id = f.id
                WHERE f.hash = i.file_hash AND l.action = 'like'
                AND l.timestamp BETWEEN :previous_start AND :start_date) AS previous_week_likes
        FROM invest i
        WHERE i.user_id = :user_id
    ";

    $stmt = $pdo->prepare($investQuery);
    $stmt->execute([
        ':start_date' => $startDate,
        ':end_date' => $endDate,
        ':previous_start' => $previousWeekStart,
        ':user_id' => $userId
    ]);

    $investments = [];
    $totalNewAmount = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $previousLikes = max(1, $row['previous_week_likes']);
        $growthPercent = (($row['current_week_likes'] - $previousLikes) / $previousLikes) * 100;
        $newAmount = $row['amount'] * (1 + ($growthPercent / 100));

        $investments[] = [
            'file_hash' => $row['file_hash'],
            'original_amount' => $row['amount'],
            'new_amount' => $newAmount,
            'growth_percent' => $growthPercent,
            'transaction_date' => $row['transaction_date']
        ];

        $totalNewAmount += $newAmount;
    }

// Correct calculation should be:
$totalOriginalAmount = 0;
$totalNewAmount = 0;

foreach ($investments as $investment) {
    $totalOriginalAmount += $investment['original_amount'];
    $totalNewAmount += $investment['new_amount'];
}

// Calculate the net change (profit/loss)
$netChange = $totalNewAmount - $totalOriginalAmount;

// The remaining balance should be adjusted by the net change, not the total new amount
$remainingBalance = $_SESSION['user']['balance'] + $netChange;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Investment Growth Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
            color: #333;
        }
        h2 {
            color: #005f73;
        }
        .report {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .file-details {
            margin: 15px 0;
            padding: 10px;
            border-radius: 6px;
            background-color: #e0fbfc;
        }
        .file-details h4 {
            margin: 5px 0;
            color: #3a506b;
        }
        .file-details p {
            margin: 5px 0;
            font-size: 0.9em;
        }
        .balance-summary {
            margin-top: 20px;
            padding: 10px;
            border-top: 1px solid #ccc;
            color: #3a506b;
        }
    </style>
</head>
<body>
    <div class="report">
        <h2>Investment Growth Report for <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h2>

        <?php if (!empty($investments)): ?>
            <?php foreach ($investments as $investment): ?>
                <div class="file-details">
                    <h4>File Hash: <?php echo htmlspecialchars($investment['file_hash']); ?></h4>
                    <p>Original Amount: $<?php echo number_format($investment['original_amount'], 2); ?></p>
                    <p>New Amount (Adjusted): $<?php echo number_format($investment['new_amount'], 2); ?></p>
                    <p>Growth: <?php echo number_format($investment['growth_percent'], 2); ?>%</p>
                    <p>Transaction Date: <?php echo htmlspecialchars($investment['transaction_date']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No investment records found for the last week.</p>
        <?php endif; ?>

        <div class="balance-summary">
            <h3>Account Summary</h3>
            <p>Starting Balance: $<?php echo number_format($_SESSION['user']['balance'], 2); ?></p>
            <p><strong>Remaining Balance: $<?php echo number_format($remainingBalance, 2); ?></strong></p>
        </div>
    </div>
</body>
</html>
