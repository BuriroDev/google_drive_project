<?php
session_start();

require '../vendor/autoload.php';
require '../config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
} else {
    echo "Failed to connect with google";
}
try {
    $token = $client->fetchAccessTokenWithAuthCode($code);
    $_SESSION['upload_token'] = $token['access_token'];

    header("Location: ../upload.php");
    exit;
} catch (Exception $e) {
    echo "Something went wrong. Please try again later.";
}
