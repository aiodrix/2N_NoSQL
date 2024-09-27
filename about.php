<?php

// Function to generate random points
function generatePoints($numPoints, $width, $height) {
    $points = [];
    for ($i = 0; $i < $numPoints; $i++) {
        $points[] = [
            'x' => mt_rand(0, $width),
            'y' => mt_rand(0, $height)
        ];
    }
    return $points;
}

// Function to calculate the circumcircle of three points
function circumcircle($p1, $p2, $p3) {
    $d = 2 * ($p1['x'] * ($p2['y'] - $p3['y']) + $p2['x'] * ($p3['y'] - $p1['y']) + $p3['x'] * ($p1['y'] - $p2['y']));
    $ux = (($p1['x'] * $p1['x'] + $p1['y'] * $p1['y']) * ($p2['y'] - $p3['y']) +
           ($p2['x'] * $p2['x'] + $p2['y'] * $p2['y']) * ($p3['y'] - $p1['y']) +
           ($p3['x'] * $p3['x'] + $p3['y'] * $p3['y']) * ($p1['y'] - $p2['y'])) / $d;
    $uy = (($p1['x'] * $p1['x'] + $p1['y'] * $p1['y']) * ($p3['x'] - $p2['x']) +
           ($p2['x'] * $p2['x'] + $p2['y'] * $p2['y']) * ($p1['x'] - $p3['x']) +
           ($p3['x'] * $p3['x'] + $p3['y'] * $p3['y']) * ($p2['x'] - $p1['x'])) / $d;
    $r = sqrt(pow($p1['x'] - $ux, 2) + pow($p1['y'] - $uy, 2));
    return ['x' => $ux, 'y' => $uy, 'r' => $r];
}

// Function to perform Delaunay triangulation
function delaunayTriangulation($points) {
    $triangles = [];
    $superTriangle = [
        ['x' => -1000, 'y' => -1000],
        ['x' => 2000, 'y' => -1000],
        ['x' => 500, 'y' => 2000]
    ];
    $triangles[] = $superTriangle;

    foreach ($points as $point) {
        $badTriangles = [];
        foreach ($triangles as $t => $triangle) {
            $circle = circumcircle($triangle[0], $triangle[1], $triangle[2]);
            $dx = $circle['x'] - $point['x'];
            $dy = $circle['y'] - $point['y'];
            if ($dx * $dx + $dy * $dy <= $circle['r'] * $circle['r']) {
                $badTriangles[] = $t;
            }
        }

        $polygon = [];
        foreach ($badTriangles as $t) {
            $triangle = $triangles[$t];
            for ($i = 0; $i < 3; $i++) {
                $edge = [$triangle[$i], $triangle[($i + 1) % 3]];
                $shared = false;
                foreach ($badTriangles as $ot) {
                    if ($t != $ot) {
                        $otherTriangle = $triangles[$ot];
                        for ($j = 0; $j < 3; $j++) {
                            $otherEdge = [$otherTriangle[$j], $otherTriangle[($j + 1) % 3]];
                            if (($edge[0] == $otherEdge[1] && $edge[1] == $otherEdge[0]) ||
                                ($edge[0] == $otherEdge[0] && $edge[1] == $otherEdge[1])) {
                                $shared = true;
                                break 2;
                            }
                        }
                    }
                }
                if (!$shared) {
                    $polygon[] = $edge;
                }
            }
        }

        foreach ($badTriangles as $t) {
            unset($triangles[$t]);
        }

        foreach ($polygon as $edge) {
            $triangles[] = [$edge[0], $edge[1], $point];
        }
    }

    $triangles = array_values(array_filter($triangles, function($triangle) use ($superTriangle) {
        return !in_array($superTriangle[0], $triangle) &&
               !in_array($superTriangle[1], $triangle) &&
               !in_array($superTriangle[2], $triangle);
    }));

    return $triangles;
}

// Main script
$width = 800;
$height = 600;
$numPoints = 15;  // Increased number of points for smoother triangles

$points = generatePoints($numPoints, $width, $height);
$triangles = delaunayTriangulation($points);

// Create image
$image = imagecreatetruecolor($width, $height);
$background = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $background);

// Enable anti-aliasing for smoother edges
imageantialias($image, true);

// Draw triangles
foreach ($triangles as $triangle) {
    // Generate a random grayscale color
    $grayValue = mt_rand(200, 255); // Use a lighter range for smoother gradients
    $color = imagecolorallocate($image, $grayValue, $grayValue, $grayValue);

    imagefilledpolygon($image, array(
        $triangle[0]['x'], $triangle[0]['y'],
        $triangle[1]['x'], $triangle[1]['y'],
        $triangle[2]['x'], $triangle[2]['y']
    ), 3, $color);
}

// Apply a smoothing filter to further soften edges
imagefilter($image, IMG_FILTER_SMOOTH, 5);  // Apply a softening filter

// Save the generated image to a file

imagepng($image, 'background_image.png');
imagedestroy($image);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Arthur Sacramento</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Set background image */
        .background {
            position: relative;
            flex: 1;
            width: 100%;
            background-image: url('background_image.png'); /* Image generated by PHP */
            background-size: cover;
            background-position: left;
        }

        /* Use overlay for opacity */
        .background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.3); /* White overlay with opacity */
            z-index: 0; /* Keep this below content */
        }

        .content {
            position: relative;
            z-index: 2; /* Content on top of overlay */
            color: #333;
            text-align: left;
            padding-top: 20px;
        }

        header {
            text-align: center;
            margin-top: 5%;
        }

        h1 {
            font-size: 2.5rem;
            color: #333;
        }

        h3 {
            font-size: 1.6rem;
            color: #666;
        }

        a {
            text-decoration: none;
            color: #333;
        }

        a:hover {
            text-decoration: underline;
        }

        main {
            max-width: 800px;
            margin: auto; /* Centering the main content */
            padding-left: 10px; /* Responsive padding */
            padding-right: 10px; /* Responsive padding */
        }

        .about p {
            font-size: 1.2rem;
            margin: 20px 0;
            text-align: justify; /* Justified text for better readability */
        }

        .checklist {
            margin-top: 40px; /* Spacing above checklist */
        }

        .checklist h2 {
            margin-bottom: 20px; /* Spacing below checklist heading */
        }

        ul {
            list-style-type: none; /* Remove default list styling */
            padding-left: 0; /* Remove default padding */
        }

        ul li {
            margin-bottom: 10px; /* Spacing between list items */
        }

        input[type="checkbox"] {
            margin-right: 10px; /* Spacing between checkbox and label */
        }

        .contact {
            position: relative;
            z-index: 3; /* Ensure contact section is above other elements */
        }

        footer {
            text-align: center;
            margin-top: auto; /* Push footer to the bottom */
            font-size: 0.9rem;
            color: #777; 
            padding-bottom: 20px; /* Padding at the bottom of footer */
        }

        @media (max-width: 600px) {
          main {
              padding-left: 10px; 
              padding-right: 10px; 
          }
          h1 {
              font-size: 2rem; 
          }
          h3 {
              font-size: 1.4rem; 
          }
          .about p, .checklist h2, footer p {
              font-size: 1rem; 
          }
      }
    </style>
</head>
<body>    
    <header>
        <h1>Arthur Sacramento</h1>
        <h3>Remote PHP Programmer</h3>
    </header>

    <main class="background">
        <div class="content">
                <section class="about">
                    <p>
                        I am a remote PHP programmer who utilizes AI to assist my work. 
                        Additionally, I work with the production of text, images, music, 
                        and any multimedia resources that can be used to build
                        a complete website from scratch or by using modern frameworks.
                    </p>
                </section>

                <section class="checklist">
                    <h2>Skills</h2>
                    <ul>
                        <li><input type="checkbox" checked> PHP Development</li>
                        <li><input type="checkbox" checked> MySQL / Database Management</li>
                        <li><input type="checkbox" checked> Frontend Development (HTML, CSS, JavaScript)</li>
                        <li><input type="checkbox" checked> Frameworks (Laravel, Vue.js)</li>
                        <li><input type="checkbox" checked> Version Control (Git)</li>
                        <li><input type="checkbox" checked> CMS Development (WordPress)</li>
                        <li><input type="checkbox" checked> Server Management (Ubuntu Linux)</li>
                        <li><input type="checkbox" checked> Project Management</li>
                        <li><input type="checkbox" checked> Social Media and Content Creator</li>
                        <li><input type="checkbox" checked> Product Quality</li>
                        <li><input type="checkbox" checked> AI Integration for Workflow</li>
                    </ul>
                </section>

                <section class="contact">
                    <h2>Contact</h2>
                    <ul>
                        <li><a href="https://wa.me/5591986042104" target="_blank">Whatsapp</a></li>
                        <li><a href="https://t.me/arthursacramento" target="_blank">Telegram</a></li>
                        <li><a href="https://github.com/aiodrix" target="_blank">Github</a></li>
                        <li><a href="https://www.linkedin.com/in/arthur-sacramento-25-80b5972bb/" target="_blank">LinkedIn</a></li>
                        <li><a href="http://2n.atwebpages.com" target="_blank">Website</a></li> 
                        <li><a href="https://2-nw.blogspot.com/" target="_blank">Blog</a></li> 
                    </ul>
                </section>            
        </div>
    </main>

    <footer>
        <p>&copy; 2024 - Remote PHP Programmer</p>
    </footer>

</body>
</html>