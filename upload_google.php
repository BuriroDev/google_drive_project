<?php
include "lib/config_google.php";

$googleOAuthURI = 'https://accounts.google.com/o/oauth2/auth?scope=' .
    urlencode(Config::GOOGLE_ACCESS_SCOPE) . '&redirect_uri=' .
    Config::AUTHORIZED_REDIRECT_URI . '&response_type=code&client_id=' .
    Config::GOOGLE_WEB_CLIENT_ID . '&access_type=online';

header("Location: $googleOAuthURI");