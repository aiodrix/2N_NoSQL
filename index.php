<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NoSQL</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #DDD;
            font-family: Arial, sans-serif;
        }
        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 20px;
            margin-bottom: 40px;
        }
        .block {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            border-radius: 10px;
            transition: transform 0.3s ease;
            text-align: center;
            text-decoration: none;
            height: 200px;
        }
        .block:hover {
            transform: scale(1.1);
        }
        .block-blue {
            background-color: blue;
        }
        .block-pink {
            background-color: red;
        }
        .block-orange {
            background-color: orange;
        }
        .block-black {
            background-color: black;
        }
        .social-links {
            display: flex;
            justify-content: center;
        }
        .social-sphere {
            width: 50px;
            height: 50px;
            background-color: #333;
            border-radius: 50%;
            margin: 0 10px;
            transition: background-color 0.3s ease, transform 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 18px;
            text-decoration: none;
        }
        .social-sphere:hover {
            background-color: #AAA;
            transform: scale(1.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="chat/index.php" class="block block-blue">Chat</a>
        <a href="pixposting/index.php" class="block block-pink">Posts</a>
        <a href="links/index.php" class="block block-orange">Links</a>
        <a href="store/index.html" class="block block-black">&nbsp; Buy manifesto &nbsp;</a>
    </div>
    <div class="social-links">
        <a href="https://x.com/2_nodes" class="social-sphere" target="_blank">X</a>
        <a href="mailto:2nodesw@gmail.com" class="social-sphere" target="_blank">G</a>
        <a href="menu.html" class="social-sphere">+</a>
    </div>
</body>
</html>