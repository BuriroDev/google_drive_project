<?php
session_start();

require '../vendor/autoload.php';
require '../config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
} else {
    echo "Issue in connectivity with Google.";
    exit;
}

try {
    $token = $client->fetchAccessTokenWithAuthCode($code);
    $_SESSION['upload_token'] = $token;

    header("Location: ../upload.php");
    exit;
} catch (Exception $e) {
    echo "Something went wrong. Please try again later.";
    exit;
}
