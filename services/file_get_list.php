<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo file_get_list(true, $_POST["file_type"]);

function file_get_list($bool_return_json, $file_type) {
    global $db_conn;

    switch($file_type) {
        case "ALL": 
            $file_list_query = $db_conn->prepare("SELECT * 
                                                    FROM file_log
                                                    JOIN file_type ON file_log.file_type_name = file_type.file_type_name 
                                                   ORDER BY upload_date DESC");
            break;
        default:
            $file_list_query = $db_conn->prepare("SELECT * 
                                                    FROM file_log 
                                                    JOIN file_type ON file_log.file_type_name = file_type.file_type_name
                                                   WHERE file_type.file_type_name = :filetype 
                                                   ORDER BY upload_date DESC");
            $file_list_query->bindValue(":filetype", $file_type, PDO::PARAM_STR);
    }
    if($file_list_query->execute()) {
        $file_list_result = $file_list_query->fetchAll();
        $file_list_array  = [];

        foreach($file_list_result as $file_list_single) {
            array_push($file_list_array, ["file_name"   => $file_list_single["file_name"],
                                          "file_type"   => $file_list_single["file_type_desc"],
                                          "upload_date" => date("j M Y", strtotime($file_list_single["upload_date"])),
                                          "upload_by"   => $file_list_single["upload_user"]]);
        }

        if(count($file_list_array) > 0) {
            if($bool_return_json) return json_encode($file_list_array);
            else return $file_list_array;
        } else return null;
    }
}
?>