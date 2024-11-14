<?php 

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

    // Get the date range for last week
    $endDate = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d H:i:s', strtotime('-1 week'));
    $previousWeekStart = date('Y-m-d H:i:s', strtotime('-2 week'));

    // First, get all unique file_hashes from invest table
    $fileHashQuery = "SELECT DISTINCT file_hash FROM invest";
    $stmt = $pdo->query($fileHashQuery);
    $fileHashes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $likesGrowth = [];

    // For each file_hash, calculate the likes growth
    foreach ($fileHashes as $fileHash) {
        // Get likes count for current week
        $currentWeekLikesQuery = "
            SELECT COUNT(*) as likes_count
            FROM likes l
            JOIN files f ON l.file_id = f.id
            WHERE f.hash = :file_hash
            AND l.action = 'like'
            AND l.timestamp BETWEEN :start_date AND :end_date";

        $stmt = $pdo->prepare($currentWeekLikesQuery);
        $stmt->execute([
            ':file_hash' => $fileHash,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $currentWeekLikes = $stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];

        // Get likes count for previous week
        $previousWeekLikesQuery = "
            SELECT COUNT(*) as likes_count
            FROM likes l
            JOIN files f ON l.file_id = f.id
            WHERE f.hash = :file_hash
            AND l.action = 'like'
            AND l.timestamp BETWEEN :previous_start AND :start_date";

        $stmt = $pdo->prepare($previousWeekLikesQuery);
        $stmt->execute([
            ':file_hash' => $fileHash,
            ':previous_start' => $previousWeekStart,
            ':start_date' => $startDate
        ]);
        $previousWeekLikes = $stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];

        // Calculate growth percentage
        $previousLikes = max(1, $previousWeekLikes); // Prevent division by zero
        $growthPercent = (($currentWeekLikes - $previousLikes) / $previousLikes) * 100;
        $likesGrowth[$fileHash] = $growthPercent;
    }

    // Get investments and calculate new amounts
    $investQuery = "
        SELECT 
            i.user_id,
            i.file_hash,
            i.amount,
            i.transaction_date
        FROM invest i
        ORDER BY i.user_id, i.transaction_date";

    $stmt = $pdo->prepare($investQuery);
    $stmt->execute();
    $results = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userId = $row['user_id'];
        $fileHash = $row['file_hash'];
        $originalAmount = $row['amount'];
        
        // Calculate new amount based on likes growth
        $growth = $likesGrowth[$fileHash] ?? 0;
        $newAmount = $originalAmount * (1 + ($growth / 100));
        
        if (!isset($results[$userId])) {
            $results[$userId] = [
                'original_total' => 0,
                'new_total' => 0,
                'files' => []
            ];
        }
        
        $results[$userId]['original_total'] += $originalAmount;
        $results[$userId]['new_total'] += $newAmount;
        $results[$userId]['files'][] = [
            'file_hash' => $fileHash,
            'original_amount' => $originalAmount,
            'new_amount' => $newAmount,
            'growth_percent' => $growth,
            'transaction_date' => $row['transaction_date']
        ];
    }

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
        /* Style for the overall body */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            padding: 20px;
        }

        /* Style for the main container */
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header styles */
        h2 {
            color: #007BFF;
            border-bottom: 2px solid #007BFF;
            padding-bottom: 5px;
        }

        h3 {
            color: #333;
            margin-top: 20px;
            font-size: 1.2em;
        }

        /* Original and new total amount styling */
        .totals {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            color: #555;
            margin: 10px 0;
        }

        /* Style for growth percentage */
        .growth {
            color: #28a745;
        }

        .growth.negative {
            color: #dc3545;
        }

        /* File details section */
        .file-details {
            margin-top: 15px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .file {
            padding: 10px;
            border-radius: 4px;
            background-color: #f9f9f9;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .file h4 {
            color: #333;
            font-size: 1em;
            margin: 0 0 5px;
        }

        .file p {
            margin: 5px 0;
            color: #666;
            font-size: 0.9em;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Investment Growth Report</h2>
        
        <?php foreach ($results as $userId => $data): ?>
            <div class="user-report">
                <h3>User ID: <?= $userId ?></h3>
                <div class="totals">
                    <div>Original Total Amount: $<?= number_format($data['original_total'], 2) ?></div>
                    <div>New Total Amount: $<?= number_format($data['new_total'], 2) ?></div>
                    <?php 
                        $totalGrowth = (($data['new_total'] - $data['original_total']) / $data['original_total']) * 100;
                        $growthClass = $totalGrowth >= 0 ? 'growth' : 'growth negative';
                    ?>
                    <div>Total Growth: <span class="<?= $growthClass ?>"><?= number_format($totalGrowth, 2) ?>%</span></div>
                </div>
                
                <div class="file-details">
                    <h4>File Details:</h4>
                    <?php foreach ($data['files'] as $file): ?>
                        <div class="file">
                            <h4>File Hash: <?= $file['file_hash'] ?></h4>
                            <p>Original Amount: $<?= number_format($file['original_amount'], 2) ?></p>
                            <p>New Amount: $<?= number_format($file['new_amount'], 2) ?></p>
                            <p>Growth: <?= number_format($file['growth_percent'], 2) ?>%</p>
                            <p>Transaction Date: <?= $file['transaction_date'] ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="divider"></div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
