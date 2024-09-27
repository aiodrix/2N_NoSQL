<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Zip File</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column; /* Stack content and footer */
            min-height: 100vh; /* Ensure body takes at least full viewport height */
        }

        .container {
            display: flex;
            flex-direction: row; /* Default to row layout */
            width: 100%;
            max-width: 1200px;
            margin-top: 20px; /* Adjust margin for top spacing */
            padding: 20px; /* Add padding for container */
        }

        .form-section {
            flex: 1;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-right: 20px; /* Space between sections */
        }

        .description-section {
            flex: 2;
            padding: 20px;
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            justify-content: center;
            align-items: center;
            text-align: center; /* Center text */
        }

        h1 {
            color: #4A90E2;
            margin-bottom: 20px;
        }

        label {
            margin-bottom: 10px;
            font-weight: bold;
        }

        input[type="file"],
        input[type="text"] {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: border-color 0.3s ease-in-out;
        }

        input[type="file"]:focus,
        input[type="text"]:focus {
            border-color: #4A90E2;
            outline: none;
        }

        button {
            padding: 10px;
            background-color: #4A90E2; /* Default button color */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
        }

        button:hover {
            background-color: #357ABD; /* Darker shade on hover */
        }

        button:active {
            transform: scale(0.98); /* Slightly shrink on click */
        }

        .description-section h2 {
            font-size: 36px; /* Large font size for description */
            color: #333; /* Text color */
        }

        .big-links {
          margin-bottom :15px ;/* Space below big links*/
          display :flex ;/* Flexbox for layout*/
          justify-content :center ;/* Center align big links*/
          gap :20px ;/* Space between links*/
      }
      
      .big-links a {
          font-size :24px ;/* Large font size for links*/
          color :#4A90E2 ;/* Link color*/
          text-decoration :none ;/* Remove underline*/
      }
      
      footer {
          width :100%;
          background-color :#4A90E2; /* Footer background color*/
          color :white; /* Text color for footer*/
          text-align:center; /* Center align text in footer*/
          padding :10px ;/* Padding for footer*/
          position :absolute ;/* Position absolute for footer*/
          bottom :0 ;/* Align to bottom*/
          left :0 ;/* Align to left*/
      }
      
      .small-text {
          font-size :14px ;/* Small font size for description*/
          color :#666 ;/* Lighter text color for description*/
          max-width :600px ;/* Max width for better readability*/
      }
      
      .download-button {
          padding :12px 20px ;/* Padding for button*/
          background-color :#007BFF ;/* Blue button background color*/
          color :white ;/* Button text color*/
          border :none ;/* Remove border*/
          border-radius :5px ;/* Rounded corners*/
          font-size :18px ;/* Font size for button text*/
          cursor :pointer ;/* Pointer cursor on hover*/
          transition :background-color 0.3s ease-in-out , transform 0.2s ease-in-out ;/* Transition effects*/ 
      }
      
      .download-button:hover {
          background-color :#0056b3 ;/* Darker blue shade on hover*/
      }
        
       /* Responsive styles */
       @media (max-width: 768px) {
           .container {
               flex-direction: column; /* Stack elements vertically */
               align-items: center; /* Center align items */
               text-align: center; /* Center text */
               margin-top: 20px; /* Adjust margin for mobile devices */
           }

           .form-section {
               margin-right: 0; /* Remove right margin */
               width: 100%; /* Full width on small screens */
               margin-bottom: 20px; /* Add space between forms */
               box-shadow: none; /* Optional, remove shadow for simplicity */
               border-radius: 5px; /* Slightly smaller radius on mobile */
               padding-bottom: 30px; /* Add bottom padding for spacing */
               padding-top: 30px; /* Add top padding for spacing */
               background-color:#e7f3ff; /* Light background for better visibility */ 
           }
           
           .description-section h2 {
               font-size :24px; /* Smaller font size for mobile devices*/
           }
           
           button{
               width :100%;/* Full width buttons for better touch target*/
           }
           
           input[type="file"],
           input[type="text"]{
               width :100%;/* Full width inputs for better touch target*/
           }
           
           label{
               display:block;/* Ensure labels stack above inputs*/
           }
           
           h1{
               font-size :28px;/* Smaller heading size for mobile devices*/
           }
       }
    </style>
</head>
<body>

    <div class="container">
         <div class="description-section">
             <div class="big-links">
                 <a href="files.php">Files</a>
                 <a href="pages.php">Pages</a>
                 <a href="#about">About Us</a>
             </div>
             <h2>Upload your ZIP files easily and search for content.</h2> <!-- Fixed missing closing tag -->
             <p class="small-text">Our platform allows you to upload ZIP files quickly, extract their contents, and search through them efficiently. Enjoy seamless file management and enhanced productivity!</p> <!-- Added small explanatory text -->
             <!-- Download Button -->
             <button class="download-button" onclick="window.location.href='path/to/sourcecode.zip'">Download Source Code</button> <!-- Adjust the link as needed -->
         </div>

         <div class="form-section">
             <h1 id="upload">Upload a Zip File</h1>
             <form action="index.php" method="POST" enctype="multipart/form-data">
                 <label for="zip_file">Select a ZIP file:</label>
                 <input type="file" name="zip_file" id="zip_file" accept=".zip" required>
                 <button type="submit">Upload and Extract</button>
             </form>

             <h1 id="search">Search</h1>
             <form action="search.php" method="POST" enctype="multipart/form-data">
                 <input type="text" name="input" placeholder="Enter a link or a word">
                 <button type="submit">Search</button>
             </form>
         </div>
     </div>

     <footer>
         &copy; <?php echo date("Y"); ?>. All rights reserved.
     </footer>

</body>
</html>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] == 0) {
        // Get the uploaded file information
        $fileName = $_FILES['zip_file']['name'];
        $fileTmpName = $_FILES['zip_file']['tmp_name'];
        
        // Extract the file name without extension
        $folderName = pathinfo($fileName, PATHINFO_FILENAME);

        // Define the users directory
        $destinationDir = 'users/' . $folderName;

        $destinationDirZip = 'users_zip/' . $folderName;

        // Check if the folder already exists
        if (file_exists($destinationDir)) {
            echo "Error: Folder with the name '$folderName' already exists!";
        } else {
            // Open the zip file
            $zip = new ZipArchive;
            if ($zip->open($fileTmpName) === TRUE) {
                $valid = true; // Flag to check if ZIP contains invalid files
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $fileInfo = $zip->statIndex($i);
                    $fileNameInZip = $fileInfo['name'];

                    // Check if it's a directory or a PHP file
                    if (substr($fileNameInZip, -1) === '/' || preg_match('/\.php$/i', $fileNameInZip)) {
                        $valid = false;
                        break;
                    }
                }

                // If invalid files are found, show an error message
                if (!$valid) {
                    echo "Error: The zip file contains folders or PHP files, which are not allowed!";
                } else {
                    // Proceed to create the folder and extract the files
                    mkdir($destinationDirZip, 0777, true);
                    $zip->extractTo($destinationDir);
                    echo "Files extracted successfully to '$destinationDirZip'.";

                    // Move the uploaded zip file to the users directory for storage
                    $storedZipPath = $destinationDirZip . '/' . $fileName;
                    if (move_uploaded_file($fileTmpName, $storedZipPath)) {
                        echo "The zip file has been saved in '$storedZipPath'.";
                    } else {
                        echo "Error: Could not store the zip file.";
                    }
                }

                $zip->close();
                include("reverse.php");
            } else {
                echo "Error: Failed to open the zip file!";
            }
        }
    } else {
        echo "Error: Please upload a valid zip file!";
    }
}
?>
