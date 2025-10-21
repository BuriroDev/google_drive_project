
<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

if ($_SERVER['REQUEST_METHOD'] === "POST" && $_POST['to_folder'] === '') {
    $alert = false;
    $emptAlert = false;

    echo $_POST['to_folder'];

    if (empty($_FILES["file"]["name"])) {
        echo 'Please select a file to upload.<br/>';
    } else {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["file"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {

            $file_name = $fileName;
            $target_file = $targetFilePath;
            $file_content = file_get_contents($target_file);
            $mime_type = mime_content_type($target_file);
            $access_token = $_SESSION['upload_token'] ?? null;

            $client = new GoogleClient();
            $client->addScope(Drive::DRIVE_FILE);
            $client->setAccessToken($access_token);

            $service = new Drive($client);

            try {
                $fileMetadata = new DriveFile(['name' => $file_name]);
                $createdFile = $service->files->create($fileMetadata, [
                    'data' => $file_content,
                    'mimeType' => $mime_type,
                    'uploadType' => 'multipart',
                    'fields' => 'id,name,webViewLink'
                ]);

                unset($_SESSION['last_file_id'], $_SESSION['google_access_token']);
                $alert = true;
            } catch (\Exception $e) {
                echo 'Upload failed: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['to_folder'])) {

    $access_token = $_SESSION['upload_token'] ?? null;
    $client = new GoogleClient();
    $client->addScope(Drive::DRIVE_FILE);
    $client->setAccessToken($access_token);
    $service = new Drive($client);

    $folder_id = $_POST['to_folder'];

    $targetDir = "uploads/";
    $fileName = basename($_FILES["file"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath);

    $file_name = $fileName;
    $target_file = $targetFilePath;
    $file_content = file_get_contents($target_file);
    $mime_type = mime_content_type($target_file);
    $access_token = $_SESSION['upload_token'] ?? null;

    try {
        $fileMetadata = new Drive\DriveFile(array(
            'name' => $file_name,
            'parents' => array($folder_id)
        ));
        $file = $service->files->create($fileMetadata, array(
            'data' => $file_content,
            'mimeType' => 'image/jpeg',
            'uploadType' => 'multipart',
            'fields' => 'id'
        ));
        $alert = true;
    } catch (Exception $e) {
        echo "Error Message: " . $e;
    }
}

if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['find'])) {

    $access_token = $_SESSION['upload_token'] ?? null;

    $client = new GoogleClient();
    $client->setClientId('653081781599-vv88bskel1osssvntcjltnhgjvfd5kmj.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->addScope(Drive::DRIVE_FILE);
    $client->setAccessToken($access_token);

    $service = new Drive($client);
    $fileName = $_GET['find'] ?? '';

    $escapedName = str_replace("'", "\\'", $fileName);

    try {

        $response = $service->files->listFiles([
            'q' => "name = '{$escapedName}'",
            'fields' => 'files(id, name)',
        ]);

        if (count($response->getFiles()) > 0) {
            $file = $response->getFiles()[0];
            echo "Found file with File ID: " . $file->getId() . "\n";
        } else {
            echo "File not found.\n";
        }
    } catch (\Exception $e) {
        echo 'List failed: ' . htmlspecialchars($e->getMessage()) . "\n";
    }
}

if ($alert === true) {
    echo "
     <!DOCTYPE html>
     <html>
     <head>
         <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
     </head>
     <body>
         <script>
             Swal.fire({
                 icon: 'success',
                 title: 'File Successfully Uploaded!',
                 text: 'Check Google Drive',
             }).then(() => {
                 window.location.href = 'upload.php'; 
             });
         </script>
     </body>
     </html>
     ";
}
