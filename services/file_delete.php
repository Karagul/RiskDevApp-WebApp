<?php
require_once dirname(__FILE__)."/../config.php";

file_delete(false);

function file_delete($bool_return_json) {
    global $db_conn;

    // Post variables
    $file_name = $_POST["file_name"];
    $file_type = $_POST["file_type"];

    $directory_current = getcwd();
    $directory_upload  = dirname(__FILE__)."/../uploads/";
    $directory_result  = dirname(__FILE__)."/../results/";

    switch($file_type) {
        case "GENERAL - ไฟล์ประชากรสัตว์ (Animal Population)":
            break;
        case "ASF - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
            $directory_upload .= "RISK_ASF/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   = "RISK_ASF";
            break;
        case "FMD - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
            $directory_upload .= "RISK_FMD/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   = "RISK_FMD";
            break;
        case "FMD - ไฟล์ประชากรสัตว์ (Animal Population)":
            $directory_upload .= "POPULATION_FMD/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   = "POPULATION_FMD";
            break;
        case "HPAI - ไฟล์ประชากรสัตว์ (Animal Population)":
            $directory_upload .= "POPULATION_HPAI/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   = "POPULATION_HPAI";
            break;
        case "HPAI - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
            $directory_upload .= "RISK_HPAI/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   = "RISK_HPAI";
            break;
        case "NIPAH - ไฟล์การเคลื่อนย้ายสัตว์ (Pig Movement)": 
            $directory_upload .= "MOVEMENT_NIPAH/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   =  "MOVEMENT_NIPAH";
            break;
        case "NIPAH - ไฟล์ประชากรหมู (Pig Population)": 
            $directory_upload .= "POPULATION_NIPAH/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   =  "POPULATION_NIPAH";
            break;
        case "NIPAH - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)": 
            $directory_upload .= "RISK_NIPAH/".$file_name;
            $directory_result .= $file_name;
            $file_type_name   =  "RISK_NIPAH";
            break;
        default: die("ไม่สามารถลบไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ");
    }

    if(!file_exists($directory_upload)) die("ไม่พลไฟล์ดังกล่าว กรุณาติดต่อผู้ดูแลระบบ (-1) :: $directory_upload");

    unlink($directory_upload) or die("ไม่สามารถลบไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ (-2)");
    unlink($directory_result) or die("ไม่สามารถลบไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ (-3)");

    $file_delete_query = $db_conn->prepare("DELETE FROM file_log
                                             WHERE file_name = :filename AND file_type_name = :filetype");
    $file_delete_query->bindValue(":filename", $file_name, PDO::PARAM_STR);
    $file_delete_query->bindValue(":filetype", $file_type_name, PDO::PARAM_STR);
    if($file_delete_query->execute()) die("ลบไฟล์สำเร็จ");
    else die("ไม่สามารถลบไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ");
}
?>