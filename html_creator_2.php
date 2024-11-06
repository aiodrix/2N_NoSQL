<?php

    if(!$category){exit;}

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

    $htmlContainer = "<div class='item'>
                <a href='../../redirect.html?url=$url' target='_blank'><img src='$thumb'></a>
                <div class='item-info'>
                    <div class='title'>$title</div>
                    <div class='user'>$user</div>
                    <div class='description'>$date : $description</div>
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

            $prevLink = ($fileIndex == 1) ? "" : "<a href='$prevPage' class='prevLink'>Prev</a>";

            $contentHead = "<link rel='stylesheet' href='../../responsive.css'><script src='../../default.js'></script><script src='../../ads.js'></script><div id='ads' name='ads' class='ads'></div><div align='right' class='prevPageLink'>$prevLink <a href='$nextPage' class='nextLink'>Next</a></div><div class='container'><div class='items-grid'>$htmlContainer";

            $file = fopen($fileName, 'a');
            fwrite($file, $contentHead);
            fclose($file);

            echo "<div align='center' class='successStatus'>Post created successfully! Open <a href='$fileName' target='_blank'>$category</a></div>";

            break;
        }

        $fileIndex++;
    }
?>