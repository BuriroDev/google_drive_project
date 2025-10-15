<?php
session_start();

if (!isset($_SESSION['upload_token'])) {
    header("Location: index.php");
    exit;
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
        body {
            display: flex;
            justify-content: center;
            align-items: center;
        }

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>