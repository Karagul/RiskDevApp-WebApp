<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo nipah_starting_subdistrict(true);

function nipah_starting_subdistrict($bool_return_json) {
    global $db_conn;

    $nipah_subdistrict_query = $db_conn->prepare("SELECT DISTINCT subdistrict_master.subdistrict_code, subdistrict_name_th
                                                  FROM execute_result
                                                  JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                                 WHERE execute_type_name = 'NIPAH' AND result_for_year = :year AND district_code = :districtCode
                                                 ORDER BY subdistrict_name_th ASC");
    $nipah_subdistrict_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $nipah_subdistrict_query->bindValue(":districtCode", $_GET["district_code"], PDO::PARAM_STR);
    if($nipah_subdistrict_query->execute()) {
        $nipah_subdistrict_list = $nipah_subdistrict_query->fetchAll();
        $nipah_subdistrict_array = array();

        foreach($nipah_subdistrict_list as $nipah_source_single) {
            $nipah_source_intermediate = array();
            $nipah_source_intermediate["subdistrict_code"] = $nipah_source_single["subdistrict_code"];
            $nipah_source_intermediate["subdistrict_name_th"] = $nipah_source_single["subdistrict_name_th"];
            array_push($nipah_subdistrict_array, $nipah_source_intermediate);
        }

        if(count($nipah_subdistrict_array) > 0) {
            if($bool_return_json) return json_encode($nipah_subdistrict_array);
            else return $nipah_subdistrict_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>