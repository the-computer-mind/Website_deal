<?php

// Enable error reporting
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Set the upload directory path
$uploadDirectory = 'uploads/';

// Create the upload directory if it doesn't exist
if (!file_exists($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

// Function to generate a unique filename
function generateUniqueFilename($directory, $extension)
{
    $filename = uniqid() . '_' . time() . $extension;
    $path = $directory . $filename;
    while (file_exists($path)) {
        $filename = uniqid() . '_' . time() . $extension;
        $path = $directory . $filename;
    }
    return $filename;
}

// Function to handle file upload
function handleFileUpload($file)
{
    global $uploadDirectory;

    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png'];

    if (in_array($fileExtension, $allowedExtensions)) {
        $filename = generateUniqueFilename($uploadDirectory, '.' . $fileExtension);
        $destination = $uploadDirectory . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $imageUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $destination;
            return $imageUrl;
        }
    }

    return null;
}

// Route for file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/upload') {
    if (isset($_FILES['image'])) {
        $files = $_FILES['image'];
        $imageUrls = [];

        // Handle multiple files
        if (is_array($files['name'])) {
            $fileCount = count($files['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];

                $imageUrl = handleFileUpload($file);
                if ($imageUrl) {
                    $imageUrls[] = $imageUrl;
                }
            }
        } else {
            // Handle single file
            $imageUrl = handleFileUpload($files);
            if ($imageUrl) {
                $imageUrls[] = $imageUrl;
            }
        }

        if (!empty($imageUrls)) {
            header('Content-Type: application/json');
            echo json_encode(['imageUrls' => array_values($imageUrls)]);
        } else {
            echo 'Failed to upload image.';
        }
    } else {
        echo 'No image file uploaded.';
    }
}

// Route for image deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/image_delete') {
    if (isset($_POST['imageLinks'])) {
        $imageLinks = is_array($_POST['imageLinks']) ? $_POST['imageLinks'] : [$_POST['imageLinks']];
        $deletedImageUrls = [];

        foreach ($imageLinks as $link) {
            $imagePath = $uploadDirectory . basename($link);

            if (file_exists($imagePath)) {
                unlink($imagePath);
                $deletedImageUrls[] = $link;
                echo 'Image deleted: ' . $link . '<br>';
            } else {
                echo 'Image not found: ' . $link . '<br>';
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['deletedImageUrls' => array_values($deletedImageUrls)]);
    } else {
        echo 'No image links provided for deletion.';
    }
}

// Route for image updating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/image_update') {
    if (isset($_FILES['image']) && isset($_POST['imageLinks'])) {
        $imageLinks = is_array($_POST['imageLinks']) ? $_POST['imageLinks'] : [$_POST['imageLinks']];
        $updatedImageFile = $_FILES['image'];
        $updatedImageUrls = [];

        foreach ($imageLinks as $link) {
            $imagePath = $uploadDirectory . basename($link);

            if (file_exists($imagePath)) {
                unlink($imagePath);
                echo 'Old image deleted: ' . $link . '<br>';

                $imageUrl = handleFileUpload($updatedImageFile);
                if ($imageUrl) {
                    $updatedImageUrls[] = $imageUrl;
                    echo 'Image updated: ' . $link . ' -> ' . $imageUrl . '<br>';
                } else {
                    echo 'Failed to update image: ' . $link . '<br>';
                }
            } else {
                echo 'Image not found: ' . $link . '<br>';
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['updatedImageUrls' => array_values($updatedImageUrls)]);
    } else {
        echo 'No image file or image links provided for updating.';
    }
}

// Route for checking if the server is running
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/something') {
    echo 'Server is running /something';
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/') {
    require 'homepage.html';
}  else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/about') {
    require 'about.html';
}  else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/contacts') {
    require 'contacts.html';
}  else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/privacy-policy-zvQTaO') {
    require 'privacy-policy-zvQTaO.html';
}  else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/refund-policy-zSH8HH') {
    require 'refund-policy-zSH8HH.html';
} 
   else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/terms-and-conditions-zGi4Fl') {
    require 'terms-and-conditions-zGi4Fl.html';
}


else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/data.json') {
     header('Content-Type: application/json');

    // Read the contents of the data.json file
    $data = file_get_contents('data.json');

    // Send the data as the response
    echo $data;
    exit;
}
// else {
//     http_response_code(404);
//     echo '404 Not Found';
// }

?>

