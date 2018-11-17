<?php
require_once dirname(__FILE__)."/../config.php";

file_upload(false);

function file_upload($bool_return_json) {
    global $db_conn;

    $directory_current = getcwd();
    $directory_upload  = "/uploads/";

    $file_name = $_FILES["file"]["name"];
    $file_size = $_FILES["file"]["size"];
    $file_temp = $_FILES["file"]["tmp_name"];
    $file_type = $_FILES["file"]["type"];
    $upload_by   = $_POST["upload_by"];
    $upload_type = $_POST["upload_type"];

    // Upload user validation
    $user_check_query = $db_conn->prepare("SELECT * FROM user_account WHERE user_name = :username");
    $user_check_query->bindValue(":username", $upload_by, PDO::PARAM_STR);
    if($user_check_query->execute()) {
        $user_check_result = $user_check_query->fetchAll();
        if(count($user_check_result) == 0) die("ไม่พบผู้ใช้สำหรับการอัพโหลด กรุณาติดต่อผู้ดูแลระบบ");
    }

    // Upload type validation
    $upload_check_query = $db_conn->prepare("SELECT * FROM file_type WHERE file_type_name = :typename");
    $upload_check_query->bindValue(":typename", $upload_type, PDO::PARAM_STR);
    if($upload_check_query->execute()) {
        $upload_check_result = $upload_check_query->fetchAll();
        if(count($upload_check_result) == 0) die("ไม่พบประเภทไฟล์สำหรับการอัพโหลด กรุณาติดต่อผู้ดูแลระบบ");
    }

    // File upload process
    $path_upload = $directory_current."/..".$directory_upload.strtoupper($upload_type)."/".basename($file_name);
    if(isset($_POST["upload_by"]) && isset($_POST["upload_type"])) {
        $upload_result = move_uploaded_file($file_temp, $path_upload);
        if($upload_result) {
            // Update database log
            $file_upload_query = $db_conn->prepare("INSERT INTO file_log VALUES(:filename, :filetype, :uploaddate, :uploadby)");
            $file_upload_query->bindParam(":filename", $file_name, PDO::PARAM_STR);
            $file_upload_query->bindParam(":filetype", $upload_type, PDO::PARAM_STR);
            $file_upload_query->bindValue(":uploaddate", date("Y-m-d"), PDO::PARAM_STR);
            $file_upload_query->bindParam(":uploadby", $upload_by, PDO::PARAM_STR);
            if($file_upload_query->execute()) {
                // Check file existence
                $file_check_query = $db_conn->prepare("SELECT * 
                                                         FROM file_log 
                                                        WHERE file_name = :filename
                                                          AND file_type_name = :filetype
                                                          AND upload_date    = :uploaddate
                                                          AND upload_user    = :uploadby");
                $file_check_query->bindParam(":filename", $file_name, PDO::PARAM_STR);
                $file_check_query->bindParam(":filetype", $upload_type, PDO::PARAM_STR);
                $file_check_query->bindValue(":uploaddate", date("Y-m-d"), PDO::PARAM_STR);
                $file_check_query->bindParam(":uploadby", $upload_by, PDO::PARAM_STR);
                if($file_check_query->execute()) {
                    $file_check_result = $file_check_query->fetchAll();
                    if(count($file_check_result) > 0) die("ไฟล์อัพโหลดสำเร็จ");
                    else die("ไม่สามารถอัพโหลดไฟล์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ");
                }
            }
        } else die("ไม่สามารถอัพโหลดไฟล์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ");
    }
}
?>