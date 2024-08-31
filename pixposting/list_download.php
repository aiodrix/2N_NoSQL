<?php
// Function to create zip file
function create_zip($folder, $zip_file, $category) {
  $root_path = realpath($folder);
  $zip = new ZipArchive();

  if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    // Add files from the specified folder, maintaining the 'html/category/' structure
 
    $zip->addFile('default.css', 'default.css');
    $zip->addFile('default.js', 'default.js');

    $html_files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($root_path, RecursiveDirectoryIterator::SKIP_DOTS),
      RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($html_files as $name => $html_file) {
      if (!$html_file->isDir()) {
        $html_file_path = $html_file->getRealPath();
        $html_relative_path = 'html/' . $category . '/' . substr($html_file_path, strlen($root_path) + 1);
        $zip->addFile($html_file_path, $html_relative_path);
        echo "Added file to zip: $html_relative_path<br>";
      }
    }

    $zip->close();
    return true;
  } else {
    return false;
  }
}

// List folders inside the 'html' directory
$html_dir = 'html';
$folders = array_filter(glob($html_dir . '/*'), 'is_dir');

if (isset($_GET['folder']) && isset($_GET['var'])) {
  $folder = $_GET['folder'];
  $category = $_GET['var'];
  $zip_file = 'downloads/' . basename($folder) . '.zip';

  if (!file_exists('downloads')) {
    mkdir('downloads', 0777, true);
  }

  if (create_zip($folder, $zip_file, $category)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_file) . '"');
    header('Content-Length: ' . filesize($zip_file));
    readfile($zip_file);
    unlink($zip_file);
    exit;
  } else {
    echo "Failed to create zip file.";
  }
} else {
  echo "<ul>";
  foreach ($folders as $folder) {
    $relative_path = substr($folder, strlen($html_dir) + 1);
    echo "<li><a href=\"?folder=$folder&var=$relative_path\">Download $relative_path</a></li>";
  }
  echo "</ul>";
}
?>
