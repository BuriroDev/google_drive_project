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

$view = false;
$create_fd = false;

if (isset($_GET['view'])) {
    $view = true;
    $folder_id = $_GET['id'];
    do {
        $result = $service->files->listFiles([
            'q' => "'{$folder_id}' in parents",
            'pageSize' => 100,
            'pageToken' => $pageToken,
        ]);

        $pageToken = $result->getNextPageToken();
    } while ($pageToken != null);
}

if (isset($_GET['back'])) {
    $view = false;
}

if (isset($_GET['newFolder'])) {
    $create_fd = true;
}

do {
    $query = "mimeType = 'application/vnd.google-apps.folder'";
    $response = $service->files->listFiles([
        'q' => $query,
        'pageSize' => 100,
        'pageToken' => $pageToken
    ]);

    $pageToken = $response->getNextPageToken();
} while ($pageToken != null);

$query = "'root' in parents and mimeType != 'application/vnd.google-apps.folder'";
do {
    $root_files = $service->files->listFiles([
        'q' => $query,
        'pageSize' => 100,
        'pageToken' => $pageToken
    ]);

    $pageToken = $root_files->getNextPageToken();
} while ($pageToken != null);

if (isset($_GET['action']) == "delete") {
    $id = $_GET['id'];
    $deleteAlert = false;

    try {
        $service->files->delete($id);
        $deleteAlert = true;
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

use Google\Client;

if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['create_fd'])) {
    $fd_Alert = false;
    $fd_name = $_POST['fdName'];
    try {
        $service = new Drive($client);
        $fileMetadata = new Drive\DriveFile(array(
            'name' => $fd_name,
            'mimeType' => 'application/vnd.google-apps.folder'
        ));
        $file = $service->files->create($fileMetadata, array(
            'fields' => 'id'
        ));
        $fd_Alert = true;
    } catch (Exception $e) {
        echo "Error Message: " . $e;
    }
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    if (isset($_POST['move'])) {
        $move_alert = false;
        $folderId = $_POST['to_folder'];
        $fileId = $_POST['id'];
        try {
            $service = new Drive($client);
            $emptyFileMetadata = new Drive\DriveFile();
            $file = $service->files->get($fileId, array('fields' => 'parents'));
            $previousParents = join(',', $file->parents);
            $file = $service->files->update($fileId, $emptyFileMetadata, array(
                'addParents' => $folderId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents'
            ));
            return $file->parents;
        } catch (Exception $e) {
            echo "Error Message: " . $e;
        }
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
            height: 300px;
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

        #addFolder {
            background: url('./folder.png');
            background-repeat: no-repeat;
            background-size: 150px;
            background-position: center center;
            border-radius: 2rem;
        }

        .form-group {
            display: flex;
            gap: 10px;
            align-items: center;
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
                    <div class="mb-3">
                        <label for="roleInput" class="form-label">Select Folder:</label>
                        <select name="to_folder" id="roleInput" class="form-control">
                            <option value="">Select a Folder</option>
                            <?php foreach ($response->getFiles() as $file) : ?>
                                <option value="<?= $file->getId() ?>"><?= $file->getName(); ?></option>';
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>

        <div class="container" id="fetchFiles">
            <h3 class="mb-5">Existing Records in Drive:</h3>
            <a href="upload.php?newFolder=true" class="btn btn-warning">New Folder</a>

            <div class="options" style="display: flex; justify-content:space-between">
                <div class="newFolder">
                    <?php if ($create_fd == true) : ?>
                        <form method="POST">
                            <label>Name:</label>
                            <input type="text" class="form-control" placeholder="Enter folder name....." name="fdName">
                            <button type="submit" class="btn btn-primary mt-1" name="create_fd">Create</button>
                        </form>

                        <?php $create_fd == false; ?>
                    <?php endif; ?>
                </div>

                <div class="search">
                    <form action="handle_upload.php" method="get" class="mb-4">
                        <h4>Search a file</h4>
                        <input type="text" name="find">
                        <button type="submit" class="btn btn-secondary">Search</button>
                    </form>
                </div>
            </div>

            <?php if ($view === false) : ?>
                <div class="d-flex flex-wrap justify-content-start align-items-center overflow-auto">
                    <?php foreach ($response->getFiles() as $file) {
                        echo "<div id='addFolder'; style='width:200px; height:300px; padding:10px; background-color: #ffffffff; display:flex; align-items: center; justify-content: space-between; flex-direction: column; color: black; margin-bottom: 10px; overflow: hidden;'>";
                        echo "<div><p style='font-weight: 800; margin-top: 150px;'>" . $file->getName() . "</p></div>";
                        $id = $file->getId();
                        echo "<div class='mb-5'> <a href='upload.php?view=true&id=$id' class='btn btn-secondary'>View</a> |
                <a href='upload.php?action=delete&id=$id' class='btn btn-danger'>Delete</a> 
                </div>";
                        echo "</div>";
                    } ?>

                    <table class="table table-striped table-hover">
                        <tr>
                            <th scope="col">File Name</th>
                            <th scope="col">Actions</th>
                            <th scope="col">Move to Other Folder</th>
                        </tr>

                        <?php foreach ($root_files->getFiles() as $root) : ?>
                            <tr>
                                <td><?= $root->getName() ?></td>
                                <?php $id = $root->getId(); ?>
                                <td> <a href='https://drive.google.com/open?id=<?= $id ?>' class='btn btn-secondary'>View</a> | <a href='upload.php?download=1&id=<?= $id ?>' class='btn btn-success'>Download</a> |
                                    <a href='upload.php?action=delete&id=<?= $id ?>' class='btn btn-danger'>Delete</a>
                                </td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <div class="form-group">
                                            <select name="to_folder" class="form-control w-50">
                                                <option value="">Select a Folder</option>
                                                <?php foreach ($response->getFiles() as $file) : ?>
                                                    <option value="<?= $file->getId() ?>"><?= $file->getName(); ?></option>';
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="move" class="btn btn-secondary">Move</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </table>

                <?php endif; ?>

                <?php if ($view === true) : ?>
                    <table class="table table-striped table-hover">
                        <a href="upload.php?back=true" class="btn btn-secondary mb-3">Back</a>
                        <tr>
                            <th scope="col">File Name</th>
                            <th scope="col">Actions</th>
                            <th scope="col">Move to Other Folder</th>
                        </tr>

                        <?php foreach ($result->getFiles() as $file) : ?>
                            <tr>
                                <td><?= $file->getName() ?></td>
                                <?php $id = $file->getId(); ?>
                                <td> <a href='https://drive.google.com/open?id=<?= $id ?>' class='btn btn-secondary'>View</a> | <a href='upload.php?download=1&id=<?= $id ?>' class='btn btn-success'>Download</a> |
                                    <a href='upload.php?action=delete&id=<?= $id ?>' class='btn btn-danger'>Delete</a>
                                </td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="id" value="<?= $id ?>">
                                        <div class="form-group">
                                            <select name="to_folder" class="form-control w-50">
                                                <option value="">Select a Folder</option>
                                                <?php foreach ($response->getFiles() as $file) : ?>
                                                    <option value="<?= $file->getId() ?>"><?= $file->getName(); ?></option>';
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="move" class="btn btn-secondary">Move</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </table>
                <?php endif; ?>

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
        }).then(() => {
            window.location.href = 'upload.php';
        });
    }
</script>

<script>
    if (<?= $fd_Alert ?>) {
        Swal.fire({
            icon: 'success',
            title: 'Folder Successfully Created!',
        }).then(() => {
            window.location.href = 'upload.php';
        });
    }
</script>