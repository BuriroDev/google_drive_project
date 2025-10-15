<?php
session_start();

include_once 'GoogleDriveApi.class.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $GoogleDriveApi = new GoogleDriveApi();
    $alert = false;

    if (empty($_FILES["file"]["name"])) {
        echo 'Please select a file to upload.<br/>';
    } else {
        $targetDir = "uploads/";
        $fileName = basename($_FILES["file"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {

            $file_name = $fileName;
            $target_file = 'uploads/' . $file_name;
            $file_content = file_get_contents($target_file);
            $mime_type = mime_content_type($target_file);
            $access_token = $_SESSION['upload_token'];

            if ($access_token) {
                try {
                    $drive_file_id = $GoogleDriveApi->UploadFileToDrive($access_token, $file_content, $mime_type);

                    if ($drive_file_id) {
                        $file_meta = array(
                            'name' => basename($file_name)
                        );

                        $drive_file_meta = $GoogleDriveApi->UpdateFileMeta($access_token, $drive_file_id, $file_meta);

                        if ($drive_file_meta) {
                            unset($_SESSION['last_file_id']);
                            unset($_SESSION['google_access_token']);

                            $alert = true;

                            $status = 'success';
                            $statusMsg = '<p>File has been uploaded to Google Drive successfully!</p>';
                            $statusMsg .= '<p><a href="https://drive.google.com/open?id=' . $drive_file_meta['id'] . '" target="_blank">' . $drive_file_meta['name'] . '</a>';
                            $link = 'https://drive.google.com/open?id=' . $drive_file_meta['id'] . '" target="_blank';
                        }
                    }
                } catch (Exception $e) {
                    $statusMsg = $e->getMessage();
                }
            }
        }
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