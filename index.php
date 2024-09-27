<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="screens/icon.jpg" type="image/jpeg">
    <title>Freelance</title>
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
            position: relative;
            overflow: hidden;
        }

        /* 2x2 grid background */
        .background-grid {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            z-index: 0;
            opacity: 0.3; /* Transparent images */
        }

        .background-grid div {
            background-size: cover;
            background-position: center;
        }

        .bg1 {
            background-image: url('screens/1.jpg');
        }

        .bg2 {
            background-image: url('screens/2.jpg');
        }

        .bg3 {
            background-image: url('screens/3.jpg');
        }

        .bg4 {
            background-image: url('screens/4.jpg');
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 20px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1; /* Put content above background */
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
            background-color: #333333;
        }

        .social-links {
            display: flex;
            justify-content: center;
            position: relative;
            z-index: 1;
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
    <!-- Background Images in 2x2 Grid -->
    <div class="background-grid">
        <div class="bg1"></div>
        <div class="bg2"></div>
        <div class="bg3"></div>
        <div class="bg4"></div>
    </div>

    <div class="container">
        <a href="chat/index.php" class="block block-blue">Chat</a>
        <a href="pixposting/index.php" class="block block-pink">Posts</a>
        <a href="links/index.php" class="block block-orange">Links</a>
        <a href="upload/index.html" class="block block-black">&nbsp; Upload files &nbsp;</a>
    </div>

    <div class="social-links">
        <a href="about.php" class="social-sphere">A</a>
        <a href="mailto:2nodesw@gmail.com" class="social-sphere" target="_blank">G</a>
        <a href="store/index.html" class="social-sphere">$</a>              
    </div>
    <div align='center'>
        <br>Welcome to <b>Arthur Sacramento's</b><br>portfolio page.
        <br><br>
        <i style='font-size: 10px;'>2024 - All rights reserved</i>
    </div>
</body>
</html>