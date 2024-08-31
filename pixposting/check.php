<?php
if (isset($_GET['url'])) {
    $url = $_GET['url'];
    $urlHash = sha1($url);

    $urlHashDir = "url_hash/$urlHash" . ".txt";

    echo $hashUser = file_get_contents($urlHashDir);

    // Create the 'hashes' folder if it doesn't exist
    if (!file_exists("users/$hashUser")) {
        mkdir("users/$hashUser", 0777, true);
    }

    // Create the folder with the SHA-256 hash of the URL
    $urlFolder = 'hashes/' . $urlHash;
    if (!file_exists($urlFolder)) {
        mkdir($urlFolder, 0777, true);
    }

    // Get user IP address
    $userIP = $_SERVER['REMOTE_ADDR'];
    $userIPHash = hash('sha256', $userIP);

    // Get user agent and other information
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
    $timestamp = date('Y-m-d H:i:s');

    // Prepare audit information
    $auditInfo = "Timestamp: $timestamp\n";
    //$auditInfo .= "User IP: $userIP\n";
    $auditInfo .= "User Agent: $userAgent\n";
    $auditInfo .= "Referer: $referer\n";

    $file = fopen("users/$hashUser/$userIPHash", 'w');
    fwrite($file, "");
    fclose($file);   

    // Redirect after 5 seconds
    //header("Refresh: 5; URL=$url");
    //echo "You will be redirected in 5 seconds. If not, click <a href=\"$url\">here</a>.";
} else {
    //echo "No URL specified.";
}
?>