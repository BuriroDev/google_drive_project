<?php
$client = new Google\Client();
$client->setClientId('653081781599-vv88bskel1osssvntcjltnhgjvfd5kmj.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/google_drive_project/lib/google_callback.php');
$client->setScopes(array('https://www.googleapis.com/auth/drive.file'));
