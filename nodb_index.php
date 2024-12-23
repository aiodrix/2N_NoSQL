<?php session_start();?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="screens/logo.jpg" type="image/jpeg">
    <title>DECEN PHP 1.4</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
        }
        .navbar {
            background-color: #007bff;
            overflow: hidden;
        }
        .navbar a {
            float: left;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .navbar a:hover {
            background-color: #0056b3;
        }
        .navbar a.active {
            background-color: #0056b3;
        }
        .navbar .menu-right {
            float: right;
        }
        .content {
            display: flex;
            flex-wrap: wrap; /* Allow wrapping for smaller screens */
            padding: 20px;
        }
        .left-side, .right-side {
            flex: 1; /* Default flex-grow */
            min-width: 300px; /* Minimum width for each side */
            padding-right: 20px; /* Space between left and right side */
        }
        .right-side {
            padding-right: 0; /* Remove right padding for the last column */
            text-align: justify;
        }
        .advantages {
            list-style-type: none;
            padding: 0;
        }
        .advantages li {
            background-color: #007bff;
            color: white;
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }
        img {
            max-width: 100%;
            height: auto;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .content {
                flex-direction: column; /* Stack elements vertically on smaller screens */
                align-items: center; /* Center align items */
            }
            .left-side, .right-side {
                padding-right: 0; /* Remove right padding on smaller screens */
                width: 100%; /* Full width for both sides */
                margin-bottom: 20px; /* Space between stacked elements */
            }
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="#home" class="active">Home</a>
        <a href="json.php">Create</a>
        <a href="links.php">Add Server</a>
        <a href="menu.html">Menu</a>        
        <div class="menu-right">
            <a href="nodb_login.php">Login</a>
            <a href="nodb_register.php">Sign Up</a>
        </div>
    </div>

    <div class="content">
        <div class="left-side">
            <img src="screens/logo.jpg" alt="Software Image">
            <ul class="advantages">
                <li>Copy all files from a server easily.</li>
                <li>Send and receive files in a decentralized way</li>
                <li>PHP is cool </li>
            </ul>
        </div>

        <div class="right-side">
            <h2>About</h2>
            <p>Our project aims to build simple distributed and decentralized systems, as well as to create static pages..</p>

            <h2>Main features</h2>
            <li>Each server displays all the files it hosts in <a href='files.php'>files.php</a> (showing a list of links) or <a href='files_json.php'>files_json.php</a> (showing a JSON text). This enables decentralized searching by checking if the user input matches the name of a file hosted on a server using <a href='search.php'>search.php</a> or <a href='search_json.php'>search_json.php</a>.</li>
            <br>
            <li>The <a href='receiver.php'>receiver.php</a> page can receive files from any user via a POST request. The file will be saved in the 'files' directory and renamed with the file's SHA-256 hash (plus the original extension). To send a file to a server, the user can use <a href='sender.php'>sender.php</a>, <a href='sender_multiple.php'>sender_multiple.php</a> or <a href='sender_multiple_list.php'>sender_multiple_list.php</a>.</li>  
            <br>
            <li>If the user wants to download all files from a server, they just need to use <a href='download_links.php'>download_links.php</a> and insert the desired URL using GET (for example, download_links.php?url=https://testsitename.com/files.php).</li>
            <br>
            <li>Within the 'html' directory there will be subdirectories categories with files organized inside (such as '1.html', '2.html'). This allows the user to find a category even if the server site is completely static. The <a href='html_creator_2.php'.php'>html_creator_2.php</a> tool assists in creating static pages and <a href='categories.php'>categories.php</a> show all categories.</li>

            <h2>Register your file
</h2>
            <li>You will gain access to numerous benefits, such as decentralized storage and backup.</li>

            <li>You may receive rewards if your file ranks among the most distributed or accessed.
</li>
            <li>You have additional assurance and documentation that the file is yours,and you are its owner.</li>

            <li>Your name, link, or banner will be featured on our supporters' page.</li>

            <p>We aim to create a system that is not only decentralized but can also be monetized to reward users with an easy and intuitive architecture. Support our project.</li>

            <h3>Download Source Code</h3>
            <a href="https://sourceforge.net/projects/decenphp/" target="_blank">Download Link</a><br>
            <a href="https://github.com/aiodrix" target="_blank">View on GitHub</a>

            <h3>Contact</h3>
            <a href="https://t.me/decenphp" target="_blank">Telegram</a><br>
            <a href="https://chat.whatsapp.com/Jm83Ib8KksaGEoTsq5kQL5" target="_blank">Whatsapp</a><br>
            <a href="https://x.com/2_nodes" target="_blank">Twitter</a><br>
        </div>
    </div>
<div align='center'>DecenPHP 2024 - All rights reserved</a>
</body>
</html>