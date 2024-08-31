<?php error_reporting(0); session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share links and posts easily.</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #7b4397, #dc2430);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            height: 80%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
        }

        h1 {
            font-size: 5rem;
            font-weight: bold;
        }

        .left-side {
            flex: 1;
            background-color: #333;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .left-side img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .left-side img.active {
            opacity: 1;
        }

        .left-side h1 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 5rem;
            font-weight: bold;
            color: #fff;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            text-align: center;
            width: 80%;
        }

        @media (max-width: 767px) {
            .container {
                flex-direction: column;
                height: auto;
            }

            .left-side {
                height: 300px;
                border-top-right-radius: 10px;
                border-bottom-left-radius: 0;
            }

            .form-container {
                padding: 20px;
            }
        }

        .left-side h1.active {
            opacity: 1;
        }

        .form-container {
            flex: 1;
            padding: 40px;
            width: 90%;
            max-width: 400px;
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-container label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .form-container input[type="url"],
        .form-container input[type="text"],
        .form-container textarea,
        .form-container select {
            width: 100%;
            padding: 10px;
            margin: 5px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }

        .form-container input[type="url"]::placeholder,
        .form-container input[type="text"]::placeholder,
        .form-container textarea::placeholder {
            color: #999;
        }

        .form-container input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #7b4397;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .form-container input[type="submit"]:hover {
            background-color: #5c3375;
        }

        .tooltip {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-left: 5px;
            font-weight: bold;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 100%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        .errorStatus {
            padding-top: 5px;
            color: red;
        }

        .successStatus {
            padding-top: 5px;
            color: green;
        }

        footer {
            color: #333;
            padding: 10px;
            position: absolute;
            bottom: 0;
            width: 95%;
        }

        footer a {
            color: #ccc;
            letter-spacing: 2px;
            padding: 5px;
        }

        footer a: hover {
            color: #ddd;
        }

        .responsive-div {
            display: none;
        }

        @media (min-width: 800px) {
            .responsive-div {
                display: block;
                padding: 20px;
                background-color: #555;
                border: 1px solid #000;
                text-align: center;
            }
        }
    </style>
</head>
<!DOCTYPE html>
<html>
<head>
    <title>Share Link</title>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <div class="container">
        <div class="left-side">
            <img src="1.jpg" alt="Image 1" class="active">
            <img src="2.jpg" alt="Image 2">
            <img src="3.jpg" alt="Image 3">
            <img src="4.jpg" alt="Image 4">
            <h1 class="active">Share links and posts easily.</h1>
            <h1>Monetize your publications.</h1>
            <h1>Reach more people.</h1>
            <h1>Create valuable content.</h1>
        </div>
        <div class="form-container">
            <h2>Share Link</h2>
        <form action="form_direct.php" method="post">
            <label for="image-url">Picture URL
                <span class="tooltip">?
                    <span class="tooltiptext">The address or path where your image is hosted</span>
                </span>
            </label>
            <input type="url" id="image-url" name="image-url" placeholder="URL to image or thumbail" required>

            <input type="url" id="file-url" name="file-url" placeholder="URL to redirect">

            <input type="text" id="title" name="title" placeholder="Title">

            <textarea id="description" name="description" rows="1" placeholder="Description"></textarea>

            <select id="category" name="category">
                <option value="random">Select category</option>
                <option value="adult_solo">Adult > Solo (one person)</option>
                <option value="adult_couple">Adult > Couple</option>
                <option value="adult_drawing">Adult > Drawing, Hentai & 3D</option>
                <option value="adult_gay">Adult > Gay and transvestite</option>
                <option value="adult_others">Adult > Others</option>
                <option value="memes_eng">Memes</option>
                <option value="fun_engt">Humor and Funny</option>
                <option value="art">Art and Wallpapers</option>
                <option value="opensource">Open-source and codes</option>
                <option value="games_and_apps">Games & Apps</option>
                <option value="news_eng">News</option>
                <option value="whats_eng">WhatsApp Groups</option>
                <option value="telegram_eng">Telegram Groups</option>
                <option value="freelancer">Freelancer and services</option>
                <option value="sites">Websites and blogs</option>
                <option value="random">Others</option>
            </select>
                <input type="submit" value="Send">
            </form>
        </div>
    </div>
    <script>
        const images = document.querySelectorAll('.left-side img');
        const texts = document.querySelectorAll('.left-side h1');
        let currentIndex = 0;

        function showNextImageAndText() {
            images[currentIndex].classList.remove('active');
            texts[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
            texts[currentIndex].classList.add('active');
        }

        setInterval(showNextImageAndText, 5000);
    </script>
    <footer>
        <a href='about.html'>Terms of Use</a> | <a href='menu.html'>Menu</a> | <a href='index.html'>Página em português</a>
    </footer>
</body>
</html>