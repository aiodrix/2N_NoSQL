<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query for likes analysis
$sql = "SELECT 
            f.id AS file_id,
            f.filename,
            COUNT(CASE WHEN l.action = 'like' THEN 1 END) AS likes_count,
            COUNT(CASE WHEN l.action = 'dislike' THEN 1 END) AS dislikes_count,
            COUNT(*) AS total_interactions,
            ROUND(
                (COUNT(CASE WHEN l.action = 'like' THEN 1 END) * 100.0) / 
                NULLIF(COUNT(*), 0),
                2
            ) AS like_percentage,
            MIN(l.timestamp) AS first_interaction,
            MAX(l.timestamp) AS last_interaction
        FROM 
            files f
        LEFT JOIN 
            likes l ON f.id = l.file_id
        WHERE 
            l.timestamp >= DATE_SUB(NOW(), INTERVAL 1 WEEK)
        GROUP BY 
            f.id, f.filename
        HAVING 
            likes_count > 0
        ORDER BY 
            likes_count DESC, like_percentage DESC";

// Execute query
$result = $conn->query($sql);

// Check if query was successful
if ($result === false) {
    die("Error executing query: " . $conn->error);
}

// Check if there are results
if ($result->num_rows > 0) {
    // HTML output with styling
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>File Likes Analysis</title>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
                margin: 20px 0;
                font-family: Arial, sans-serif;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            tr:hover {
                background-color: #f5f5f5;
            }
            .percentage-bar {
                background-color: #e0e0e0;
                width: 100px;
                height: 20px;
                display: inline-block;
                position: relative;
            }
            .percentage-fill {
                background-color: #4CAF50;
                height: 100%;
                position: absolute;
                left: 0;
            }
        </style>
    </head>
    <body>
        <h2>File Likes Analysis (Last 7 Days)</h2>
        <table>
            <tr>
                <th>File ID</th>
                <th>Filename</th>
                <th>Likes</th>
                <th>Dislikes</th>
                <th>Total Interactions</th>
                <th>Like Percentage</th>
                <th>First Interaction</th>
                <th>Last Interaction</th>
            </tr>
            <?php
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row["file_id"]) . "</td>";
                echo "<td><a href='id_redirect.php?id=" . htmlspecialchars($row["file_id"]) . "' target='_blank'>" . htmlspecialchars($row["filename"]) . "</a></td>";
                echo "<td>" . htmlspecialchars($row["likes_count"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["dislikes_count"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["total_interactions"]) . "</td>";
                echo "<td>
                        <div class='percentage-bar'>
                            <div class='percentage-fill' style='width: " . htmlspecialchars($row["like_percentage"]) . "%;'></div>
                        </div>
                        " . htmlspecialchars($row["like_percentage"]) . "%
                    </td>";
                echo "<td>" . htmlspecialchars($row["first_interaction"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["last_interaction"]) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </body>
    </html>
    <?php
} else {
    echo "No likes found in the last 7 days.";
}

// Close database connection
$conn->close();
?>