<?php
session_start();

if (!isset($_SESSION['upload_token'])) {
    header("Location: index.php");
    exit;
}
require __DIR__ . '/vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive;

$access_token = $_SESSION['upload_token'] ?? null;

$client = new GoogleClient();
$client->setClientId('653081781599-vv88bskel1osssvntcjltnhgjvfd5kmj.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->addScope(Drive::DRIVE_FILE);
$client->setAccessToken($access_token);

$pageToken = null;

$service = new Drive($client);

do {
    $response = $service->files->listFiles([
        'pageSize' => 100,
        'fields' => 'nextPageToken, files(id, name)',
        'pageToken' => $pageToken
    ]);

    $pageToken = $response->getNextPageToken();
} while ($pageToken != null);

if (isset($_GET['action']) == "delete") {
    $id = $_GET['id'];
    $deleteAlert = false;

    try {
        $service->files->delete($id);
        $deleteAlert = true;
        $dMassage = "File with ID {$id} has been deleted successfully.";
    } catch (\Exception $e) {
        echo 'Delete failed: ' . htmlspecialchars($e->getMessage());
    }
}

if (isset($_GET['download'])) {
    $id = $_GET['id'];

    try {
        $meta = $service->files->get($id, ['fields' => 'name, mimeType']);
        $name = $meta->getName();
        $mime = $meta->getMimeType() ?? 'application/octet-stream';

        $response = $service->files->get($id, ['alt' => 'media']);
        $content = $response->getBody()->getContents();

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($name) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    } catch (\Exception $e) {
        echo "Error Message: " . htmlspecialchars($e->getMessage());
    }
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
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <style>
        #main {
            width: 70%;
            height: 200px;
            border-left: 4px solid black;
            border-right: 4px solid black;
            border-top: 4px solid black;
            margin-top: 50px;
            display: flex;
            justify-content: center;
            color: white;
            padding: 40px;
        }

        #fetchFiles {
            width: 70%;
            border-left: 4px solid black;
            border-right: 4px solid black;
            border-bottom: 4px solid black;
            color: white;
        }
    </style>
</head>

<body>
    <div id="parent">
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

        <div class="container" id="fetchFiles">
            <h3 class="mb-5">Existing Records in Drive:</h3>
            <form action="handle_upload.php" method="get" class="mb-4">
                <h4>Search a file</h4>
                <input type="text" name="find">
                <button type="submit" class="btn btn-secondary">Search</button>
            </form>
            <table class="table table-striped table-hover">
                <tr>
                    <th scope="col">File Name</th>
                    <th scope="col">Actions</th>
                </tr>

                <?php foreach ($response->getFiles() as $file) {
                    echo "<tr>";
                    echo "<td>" . $file->getName() . "</td>";
                    $id = $file->getId();
                    echo "<td> <a href='https://drive.google.com/open?id=$id' class='btn btn-secondary'>View</a> | <a href='upload.php?download=1&id=$id' class='btn btn-success'>Download</a> |
                <a href='upload.php?action=delete&id=$id' class='btn btn-danger'>Delete</a>
                </td>";
                    echo "</tr>";
                } ?>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>

<script>
    if (<?= $deleteAlert ?>) {
        Swal.fire({
            icon: 'success',
            title: 'File Successfully Deleted!',
            text: '<?php echo $dMassage ?>',
        }).then(() => {
            window.location.href = 'upload.php';
        });
    }
</script>