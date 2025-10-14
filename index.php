<?php
session_start();

// require "../includes/db.php";
require __DIR__ . '/../google_drive_project/vendor/autoload.php';

if (isset($_GET['checkAuth'])) {
    checkAuth();
}

function checkAuth()
{
    $client = new Google\Client();
    $client->setClientId('653081781599-vv88bskel1osssvntcjltnhgjvfd5kmj.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://localhost/Intern_Tasks/google_drive_project/lib/google_callback.php');
    $client->setScopes(array('https://www.googleapis.com/auth/drive.file'));

    $authUrl = $client->createAuthUrl();
    header('Location: ' . $authUrl);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>

<body>

    <div class="container mt-5">
        <form action="upload_google.php">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <h2>Connect with Google Drive:</h2>
                <a href="./index.php?checkAuth=true" type="submit" class="btn btn-primary">Authenticate</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>

</html>