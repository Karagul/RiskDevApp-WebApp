<?php
require_once dirname(__FILE__)."/../config.php";

file_upload(false);

function file_upload($bool_return_json) {
    global $db_conn;

    $directory_current = getcwd();
    $directory_upload  = "/uploads/";
    //beg+++iKS27.01.2019 Copy the uploaded file to the result workspace
    $directory_result  = "/results/";
    //end+++iKS27.01.2019 Copy the uploaded file to the result workspace

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

    // Check directory existence
    $directory_path = $directory_current."/..".$directory_upload.strtoupper($upload_type);
    if(!file_exists($directory_path) && !is_dir($directory_path)) {
        mkdir($directory_path);
    }

    // File upload process
    $path_upload = $directory_path."/".basename($file_name);
    $path_result = "$directory_current/../$directory_result".basename($file_name);
    if(isset($_POST["upload_by"]) && isset($_POST["upload_type"])) {
        $upload_result = move_uploaded_file($file_temp, $path_upload);
        if($upload_result) {
            //beg+++eKS03.02.2019 Check for replacement upload case
            $file_check_query = $db_conn->prepare("SELECT *
                                                     FROM file_log
                                                    WHERE file_name = :filename
                                                      AND file_type_name = :filetype");
            $file_check_query->bindValue(":filename", $file_name, PDO::PARAM_STR);
            $file_check_query->bindValue(":filetype", $upload_type, PDO::PARAM_STR);
            if($file_check_query->execute()) {
                $file_check_result = $file_check_query->fetchAll();
                if(count($file_check_result) > 0) {
                    // File already exists in the database; update the date and username
                    $file_update_query = $db_conn->prepare("UPDATE file_log
                                                               SET upload_date = :uploaddate, upload_user = :uploadby
                                                             WHERE file_name = :filename AND file_type_name = :filetype");
                    $file_update_query->bindValue(":uploaddate", date("Y-m-d"), PDO::PARAM_STR);
                    $file_update_query->bindValue(":uploadby", $upload_by, PDO::PARAM_STR);
                    $file_update_query->bindValue(":filename", $file_name, PDO::PARAM_STR);
                    $file_update_query->bindValue(":filetype", $upload_type, PDO::PARAM_STR);
                    if($file_update_query->execute()) die("ไฟล์อัพโหลดสำเร็จ");
                    else die("ไม่สามารถอัพโหลดไฟล์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ");
                }
            }
            //end+++eKS03.02.2019 Check for replacement upload case

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
                    if(count($file_check_result) > 0) {
                        //beg+++iKS27.01.2019 Copy the uploaded file to the result workspace
                        if(!copy($path_upload, $path_result)) die("ไม่สามารถทำสำเนาไฟล์ได้ ($path_upload => $path_result)");
                        //end+++iKS27.01.2019 Copy the uploaded file to the result workspace
                        die("ไฟล์อัพโหลดสำเร็จ");
                    }
                    else die("ไม่สามารถอัพโหลดไฟล์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ");
                } else die(var_dump($file_check_query->errorInfo()));
            } else die(var_dump($file_upload_query->errorInfo()));
        } else die("ไม่สามารถอัพโหลดไฟล์ได้ในขณะนี้ กรุณาลองใหม่อีกครั้งหรือติดต่อผู้ดูแลระบบ");
    }
}
?>