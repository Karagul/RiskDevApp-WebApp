<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo file_get_type(true);

function file_get_type($bool_return_json) {
    global $db_conn;

    $file_type_query = $db_conn->prepare("SELECT * FROM file_type ORDER BY file_type_desc");
    if($file_type_query->execute()) {
        $file_type_result = $file_type_query->fetchAll();
        $file_type_array  = [];

        foreach($file_type_result as $file_type_single) {
            array_push($file_type_array, ["type_name" => $file_type_single["file_type_name"], 
                                          "type_desc" => $file_type_single["file_type_desc"]]);
        }

        if(count($file_type_array) > 0) {
            if($bool_return_json) return json_encode($file_type_array);
            else return $file_type_array;
        } else return null;
    } else {
        die(error_display($file_type_query));
    }
}
?>