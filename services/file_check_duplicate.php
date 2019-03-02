<?php
require_once dirname(__FILE__)."/../config.php";

file_check_duplicate($_POST["filename"]);

function file_check_duplicate($file_name) {
    global $db_conn;

    $check_duplicate_query = $db_conn->prepare("SELECT file_name FROM file_log WHERE file_name = :filename");
    $check_duplicate_query->bindValue(":filename", $file_name, PDO::PARAM_STR);
    if($check_duplicate_query->execute()) {
        $check_duplicate_result = $check_duplicate_query->fetchAll();
        $check_duplicate_count  = 0;

        foreach($check_duplicate_result as $check_duplicate_single) {
            $check_duplicate_count ++;
        } 

        if($check_duplicate_count > 0) die("ไฟล์ที่ต้องการอัพโหลด มีอยู่ในระบบอยู่แล้ว\nท่านต้องการอัพโหลดไฟล์นี้ใหม่หรือไม่");
        else die("OK");
    } else die("ไม่สามารถตรวจสอบไฟล์ในระบบได้ กรุณาติดต่อผู้ดูแลระบบ");
}
?>