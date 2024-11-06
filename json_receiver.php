<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /*
    // Check if all required fields are present
    $requiredFields = array("thumbnail", "title", "user", "url", "description", "category", "receivers");
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            echo "Error: $field is required.";
            //exit;
        }
    }
    */

    $thumb = $_FILES["thumbnail"];
    $title = $_POST["title"];   
    $user = $_POST["user"];
    $url = $_POST["url"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $receivers = $_POST["receivers"];

    // Check if the thumbnail file was uploaded successfully
    if ($thumb["error"] !== UPLOAD_ERR_OK) {
        echo "Error uploading thumbnail file.";
        exit;
    }

    // Define the target directory to save the file
    $targetDirectory = "html/$category/";
    $thumbDirectory = "files/";

    // Check if the target directory exists, and create it if not
    if (!file_exists($targetDirectory)) {
        mkdir($targetDirectory, 0777, true);
    }
    if (!file_exists($thumbDirectory)) {
        mkdir($thumbDirectory, 0777, true);
    }

    // Calculate SHA-256 hash of the file content
    $thumbnailHash = hash_file('sha256', $thumb["tmp_name"]);

    // Get the file extension of the uploaded thumbnail
    $thumbnailExtension = pathinfo($thumb["name"], PATHINFO_EXTENSION);

    // Construct the filename using the hash and the original extension
    $thumbnailFileName = $thumbnailHash . '.' . $thumbnailExtension;

    // Move the uploaded thumbnail to the target directory
    $thumbnailPath = $thumbDirectory . $thumbnailFileName;
    if (!move_uploaded_file($thumb["tmp_name"], $thumbnailPath)) {
        echo "Error moving uploaded file.";
        exit;
    }

    $date = date("d/m/Y");
    $urlHash = sha1($thumb);

    if (isset($_SESSION['user'])) {
        $user = $_SESSION['user'];
    } else {
        $user = "user";
    }

    // Create the folder if it doesn't exist
    if (!file_exists("html/$category")) {
        mkdir("html/$category", 0777, true);
    }

    $fileIndex = 1;
    $maxFileSize = 20 * 1024; // 20 kilobytes

    $htmlContainer = "<div class='container'>
        <div class='image-container'>
            <a href='../../$thumbnailPath' target='_blank'><img src='../../$thumbnailPath'></a>
        </div>
        <div class='content-container'>
            <h2 class='title'>$title</h2>
            <div class='details'>
                <a href='../../redirect.html?url=$url' target='_blank' class='download'>Access &#8599;</a>
                <span class='user'>User : $user</span>&nbsp;
                <span class='date'>Date : $date</span>
            </div>
            <div class='description-container'>
                <a href='#' class='view' onclick='toggleDescription(event)'>more</a>
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

                echo "<div align='center' class='successStatus'>Post created successfully! Open <a href='$fileName' target='_blank'>$category</a></div>";

                break;
            }
        } else {

            $prevIndex = $fileIndex - 1;
            $nextIndex = $fileIndex + 1;
            $nextPage = $nextIndex . ".html";
            $prevPage = $prevIndex . ".html";

            $prevLink = ($fileIndex == 1) ? "" : "<a href='$prevPage' class='prevLink'>&lt; Prev</a>";

            $contentHead = "<link rel='stylesheet' href='../../default.css'><script src='../../default.js'></script><script src='../../ads.js'></script><div id='ads' name='ads' class='ads'></div><div align='right' class='prevPageLink'>$prevLink <a href='$nextPage' class='nextLink'>Next &gt;</a></div>$htmlContainer";

            $file = fopen($fileName, 'a');
            fwrite($file, $contentHead);
            fclose($file);

            echo "<div align='center' class='successStatus'>Post created successfully! Open <a href='$fileName' target='_blank'>$category</a></div>";

            break;
        }

        $fileIndex++;
    }

    echo "Content and image received successfully.";
} else {
    echo "Invalid request.";
}
?>
