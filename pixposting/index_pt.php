<?php error_reporting(0); session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartilhe link e postagens facilmente. Monetize.</title>
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

        .userLogged {
            position: absolute;
            top: 5px;
            right: 5px;
            color: #DDD;
        }

    </style>
</head>
<body>
<?php if(isset($_SESSION['user'])){echo "<div class='userLogged'>Hello, " . $_SESSION['user'] . "</div>";} ?>

    <div class="container">
        <div class="left-side">
            <img src="1.jpg" alt="Image 1" class="active">
            <img src="2.jpg" alt="Image 2">
            <img src="3.jpg" alt="Image 3">
            <img src="4.jpg" alt="Image 4">
            <h1 class="active">Compartilhe link e postagens facilmente.</h1>
            <h1>Monetize suas publicações.</h1>
            <h1>Alcance mais pessoas.</h1>
            <h1>Crie conteúdo de valor.</h1>
        </div>
        <div class="form-container">
            <h2>Compartilhar Link</h2>
        <form action="form_direct.php" method="post">
            <label for="image-url">URL da Imagem
                <span class="tooltip">?
                    <span class="tooltiptext">O endereço ou caminho em que sua imagem está hospedada</span>
                </span>
            </label>
            <input type="url" id="image-url" name="image-url" placeholder="URL da imagem para thumbnail" required>

            <input type="url" id="file-url" name="file-url" placeholder="URL de redirecionamento">

            <input type="text" id="title" name="title" placeholder="Título">

            <textarea id="description" name="description" rows="1" placeholder="Descrição"></textarea>

            <select id="category" name="category">
                <option value="random">Selecionar categoria</option>
                <option value="adult">Adulto</option>
                <option value="music">Música</option>              
                <option value="adult_others">Adulto > Outros</option>
                <option value="memes_pt">Memes</option>
                <option value="fun_pt">Humor e Engraçado</option>
                <option value="art">Arte e Wallpapers</option>
                <option value="opensource">Open-source e códigos</option>
                <option value="games_and_apps">Jogos & Aplicativos</option>
                <option value="news_pt">Notícias</option>
                <option value="whats">Grupos de WhatsApp</option>
                <option value="telegram">Grupos de Telegram</option>
                <option value="freelancer">Freelancer e serviços</option>
                <option value="sites">Sites e blogs</option>
                <option value="random">Outros</option>
            </select>
                <input type="submit" value="Enviar">
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
        <a href='about_pt.html'>Termos de Uso</a> | <a href='menu_pt.html'>Menu</a> | <a href='index_eng.html'>English page</a>   
    </footer>
</body>
</html>