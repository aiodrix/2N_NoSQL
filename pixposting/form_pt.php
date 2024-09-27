<?php error_reporting(0); session_start(); ?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Envio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        }

        .form-container input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container input[type="submit"]:hover {
            background-color: #45a049;
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
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Envio de Arquivo</h2>
        <form action="form.php" method="post">
            <label for="image-url">URL da Imagem
                <span class="tooltip">?
                    <span class="tooltiptext">O endereço ou caminho em que sua imagem está hospedada</span>
                </span>
            </label>
            <input type="url" id="image-url" name="image-url" required>

            <label for="file-url">URL de redirecionamento (opcional)</label>
            <input type="url" id="file-url" name="file-url">

            <label for="title">Título (opcional)</label>
            <input type="text" id="title" name="title">

            <label for="description">Descrição (opcional)</label>
            <textarea id="description" name="description" rows="4"></textarea>

            <label for="category">Categoria Principal (opcional)</label>
            <select id="category" name="category">
                <option value="random">Outros</option>
                <option value="adult">Adulto</option>
                <option value="music">Música</option>                
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
            </select>

            <label for="description">Subcategoria (opcional)</label>
            <input type="text" id="subcategory" name="subcategory">

            <input type="submit" value="Enviar">

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                function validateInput($data) {
                    $max_length = 255;

                    // Remove unwanted characters
                    $data = str_replace(['<', '>'], '', $data);
                    $data = htmlspecialchars($data);

                    // Truncate the string if it exceeds the max length
                    if (strlen($data) > $max_length) {
                        $data = substr($data, 0, $max_length) . '...';
                    }

                    return $data;
                }

                /*/
                function resizeImage($file, $newWidth) {
                    list($width, $height) = getimagesize($file);
                    $newHeight = ($height / $width) * $newWidth;

                    $src = imagecreatefromjpeg($file);
                    $dst = imagecreatetruecolor($newWidth, $newHeight);

                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                    return $dst;
                }

                function cropImage($image, $cropHeight) {
                    $width = imagesx($image);
                    $height = imagesy($image);

                    $cropY = ($height - $cropHeight) / 2;
                    $dst = imagecreatetruecolor($width, $cropHeight);

                    imagecopy($dst, $image, 0, 0, 0, $cropY, $width, $cropHeight);

                    return $dst;
                }

                function saveImage($image, $file) {
                    imagejpeg($image, $file);
                    imagedestroy($image);
                }

                // Main script
                $imageUrl = $_POST['image-url'];
                $newWidth = 150;
                $cropHeight = 100;

                // Download the image from the URL
                $fileContent = file_get_contents($imageUrl);

                if ($fileContent == "") {
                    echo "<div align='center' class='errorStatus'>Erro! Não foi possível acessar a imagem.</div>";
                    die;
                }

                // Generate SHA-256 hash of the file contents
                $hash = hash('sha256', $fileContent);
                $thumbsDir = 'thumbs';
                $outputFile = $thumbsDir . '/' . $hash . '.jpg';

                // Create 'thumbs' directory if it doesn't exist
                if (!is_dir($thumbsDir)) {
                    mkdir($thumbsDir, 0777, true);
                }

                // Save the file content to a temporary file
                $tempFile = tempnam(sys_get_temp_dir(), 'image');
                file_put_contents($tempFile, $fileContent);

                $mimeType = mime_content_type($tempFile);

                if ($mimeType != "image/jpeg" && $mimeType != "image/png" && $mimeType != "image/gif") {
                    echo "<div align='center' class='errorStatus'>Erro! Formato de arquivo não aceito.</div>";
                    unlink($tempFile);
                    die;
                }

                $resizedImage = resizeImage($tempFile, $newWidth);
                $croppedImage = cropImage($resizedImage, $cropHeight);
                saveImage($croppedImage, $outputFile);

                // Clean up temporary file
                unlink($tempFile);
                /*/

                $imageUrl = validateInput($_POST['image-url']);
                $fileUrl = validateInput($_POST['file-url']);
                $title = validateInput($_POST['title']);
                $description = validateInput($_POST['description']);
                $category = validateInput($_POST['category']);
                $subcategory = validateInput($_POST['subcategory']);

                $date = date("d/m/Y");

                $urlHash = sha1($imageUrl);

                $user = "";

                if (isset($_SESSION['user'])) {
                    $user = $_SESSION['user'];
                }

                // Define the file path
                $file_path = "json_files/$category/$urlHash.json";

                // Create the folder if it doesn't exist
                if (!file_exists("json_files/$category")) {
                    mkdir("json_files/$category", 0777, true);
                }

                // Prepare data to be saved
                $formData = array(
                    'image_url' => $imageUrl,
                    'file_url' => $fileUrl,
                    'title' => $title,
                    'description' => $description,
                    'category' => $category,
                    'user' => $user
                );

                // Read existing data from the file
                $jsonData = array();
                if (file_exists($file_path)) {
                    $jsonData = json_decode(file_get_contents($file_path), true);
                }

                // Append new data
                $jsonData[] = $formData;

                // Save the data to the file
                
                $file = fopen($file_path, 'w');
                fwrite($file, json_encode($jsonData, JSON_UNESCAPED_SLASHES));
                fclose($file);     

                // Create the folder if it doesn't exist
                if (!file_exists("html/$category")) {
                    mkdir("html/$category", 0777, true);
                }

                $urlFileHash = sha1($fileUrl);   

                if (file_exists("url_hash/$urlFileHash" . ".txt")){
                    echo "<div align='center' class='errorStatus'>Erro. Esse link já existe</div>"; die;                     
                }

                if ($user != ""){ 

                    $urlFileHash = sha1($fileUrl);

                    if (!file_exists("url_hash")) {
                        mkdir("url_hash", 0777, true);
                    } 

                    $urlHashDir = "url_hash/$urlFileHash" . ".txt";
                    $file = fopen($urlHashDir, 'w');
                    fwrite($file, $user);
                    fclose($file);   
                }

                $fileIndex = 1;
                $maxFileSize = 20 * 1024; // 20 kilobytes

                $htmlContainer = "<div class='container'>
                    <div class='image-container'>
                        <a href='$imageUrl' target='_blank'><img src='../../$outputFile' onerror=\"this.onerror=null;this.src='$imageUrl';\"></a>
                    </div>
                    <div class='content-container'>
                        <h2 class='title'>$title</h2>
                        <div class='details'>
                            <a href='$fileUrl' target='_blank' class='download'>Acessar &#8599;</a>
                            <span class='user'>User : $user</span>
                            <span class='date'>Date : $date</span>
                        </div>
                        <div class='description-container'>
                            <a href='#' class='view' onclick='toggleDescription(event)'>Detalhes</a>
                            <p class='description'>$description</p>
                        </div>
                    </div>
                </div>";

                while (true) {
                    $fileName = "html/$category/" . $fileIndex . ".html";
                    $lastPageNumber = "html/$category/count.txt";

                    if (file_exists($fileName)) {
                        $fileSize = filesize($fileName);

                        if ($fileSize < $maxFileSize) {
                            
                            $file = fopen($fileName, 'a');
                            fwrite($file, $htmlContainer);
                            fclose($file);                                         

                            echo "<div align='center' class='successStatus'>Postagem criada com sucesso! Abrir categoria <a href='$fileName' target='_blank'>$category</a></div>";
                            break;
                        }
                    } else {
                        $prevIndex = $fileIndex - 1;
                        $nextIndex = $fileIndex + 1;
                        $nextPage = $nextIndex . ".html";
                        $prevPage = $prevIndex . ".html";
 
                        $contentHead = "<link rel='stylesheet' href='../../default.css'><script src='../../default.js'></script><script src='../../ads.js'></script><div id='ads' name='ads' class='ads'></div><div align='right' class='nextPageLink'><a href='$nextPage' class='nextLink'>&lt; Prev</a> <a href='$prevPage' class='prevLink'>Next &gt;</a></div>$htmlContainer";

                        $file = fopen($fileName, 'a');
                        fwrite($file, $contentHead);
                        fclose($file);                                              

                        echo "<div align='center' class='successStatus'>Postado com sucesso! Abrir categoria <a href='$fileName' target='_blank'>$category</a></div>";
                        file_put_contents($lastPageNumber, $fileIndex);
                         
                        //Create the folder if it doesn't exist
                        if (!file_exists("html/$subcategoryName")) {
                            mkdir("html/$subcategoryName", 0777, true);
                        } 

                        $penultimateFileName = "html/$category/" . $prevPage;
                        $penultimateFileNameHash = sha1_file($penultimateFileName);  

                        $file = fopen("hashes/$penultimateFileNameHash", 'a');
                        fwrite($file, "");
                        fclose($file);                                                                    
 
                        break;
                    }

                    $fileIndex++;
                }

                if ($subcategory != ""){

                    $subcategory = strtolower($subcategory); 

                    $subcategory = str_replace(" ", "_", $subcategory);

                    $subcategoryName = $category . "_" . $subcategory;

                    //Create the folder if it doesn't exist
                    if (!file_exists("html/$subcategoryName")) {
                        mkdir("html/$subcategoryName", 0777, true);
                    } 
 
                    $fileIndex = 1;
                
                    while (true) {                 

                        $fileName = "html/$subcategoryName/" . $fileIndex . ".html";
                        $lastPageNumber = "html/$subcategoryName/count.txt";

                    if (file_exists($fileName)) {
                        $fileSize = filesize($fileName);

                        if ($fileSize < $maxFileSize) {
                            //file_put_contents($fileName, $htmlContainer, FILE_APPEND);
                            $file = fopen($fileName, 'a');
                            fwrite($file, $htmlContainer);
                            fclose($file);  
                             
                            echo "<div align='center' class='successStatus'>Subcategoria : <a href='$fileName' target='_blank'>$subcategory</a></div>";
                            break;
                        }
                    } else {
                        $prevIndex = $fileIndex - 1;
                        $nextIndex = $fileIndex + 1;
                        $nextPage = $nextIndex . ".html";
                        $prevPage = $prevIndex . ".html";
 
                        $contentHead = "<link rel='stylesheet' href='../../default.css'><script src='../../default.js'></script><script src='../../ads.js'></script><div id='ads' name='ads' class='ads'></div><div align='right' class='nextPageLink'><a href='$nextPage' class='nextLink'>&lt; Prev</a> <a href='$prevPage' class='prevLink'>Next &gt;</a></div>$htmlContainer";

                        $file = fopen($fileName, 'a');
                        fwrite($file, $contentHead);
                        fclose($file);     

                        echo "<div align='center' class='successStatus'>Subcategoria : <a href='$fileName' target='_blank'>$subcategory</a></div>";
                        file_put_contents($lastPageNumber, $fileIndex);

                        $penultimateFileName = "html/$category/" . $prevPage;
                        $penultimateFileNameHash = sha1_file($penultimateFileName);  

                        $file = fopen("hashes/$penultimateFileNameHash", 'a');
                        fwrite($file, "");
                        fclose($file);                                                                                        

                        break;
                    }

                    $fileIndex++;
                }
                }
            }

            ?>
        </form>
    </div>
</body>
</html>
