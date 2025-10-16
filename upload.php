<?php
session_start();

if (!isset($_SESSION['upload_token'])) {
    header("Location: index.php");
    exit;
}
require __DIR__ . '/vendor/autoload.php';
use Google\Client as GoogleClient;
use Google\Service\Drive;

if ($_SERVER['REQUEST_METHOD'] === "GET" && isset($_GET['fetch'])) {
    $pageToken = null;

    $access_token = $_SESSION['upload_token'] ?? null;

    $client = new GoogleClient();
    $client->setClientId('653081781599-vv88bskel1osssvntcjltnhgjvfd5kmj.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->addScope(Drive::DRIVE_FILE);
    $client->setAccessToken($access_token);

    $service = new Drive($client);

    do {
        $response = $service->files->listFiles([
            'pageSize' => 100, 
            'fields' => 'nextPageToken, files(id, name)',
            'pageToken' => $pageToken
        ]);

        foreach ($response->getFiles() as $file) {
            echo "File Name: " . $file->getName() . " | File ID: " . $file->getId() . "\n";
            echo "</br>";
        }

        $pageToken = $response->getNextPageToken();
    } while ($pageToken != null);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload to Google Drive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>
        #main {
            width: 450px;
            height: 400px;
            border: 4px solid black;
            margin-top: 350px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
        }
    </style>
</head>

<body>
    <div class="container bg-warning" id="main">
        <div>
            <form action="handle_upload.php" method="post" enctype="multipart/form-data" class="d-flex flex-column justify-content-center align-items-center">
                <h3>Upload a File to GDrive</h3>
                <div class="mb-3">
                    <input class="form-control" type="file" name="file" id="formFile">
                </div>
                <button type="submit" class="btn btn-primary">Upload</button>
            </form>
        </div>
    </div>

    <form method="get" style="margin-top: 20px;" class="d-flex flex-column justify-content-center align-items-center">
        <h3>Fetch All Files:</h3>
        <button type="submit" name="fetch" class="btn btn-primary">Fetch Files</button>
    </form>

    <form action="handle_upload.php" method="get" style="margin-top: 20px;" class="d-flex flex-column justify-content-center align-items-center">
        <h3>Search a File</h3>
        <div class="mb-3 w-25">
            <input class="form-control" type="text" name="find" id="formFile">
        </div>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <form action="handle_upload.php" method="get" style="margin-top: 20px;" class="d-flex flex-column justify-content-center align-items-center">
        <h3>Delete a File</h3>
        <div class="mb-3 w-25">
            <input class="form-control" type="text" name="delete_id" id="formFile">
        </div>
        <button type="submit" class="btn btn-primary">Delete</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>