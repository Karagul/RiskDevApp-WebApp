<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo asf_starting_subdistrict(true);

function asf_starting_subdistrict($bool_return_json) {
    global $db_conn;

    $asf_subdistrict_query = $db_conn->prepare("SELECT DISTINCT subdistrict_master.subdistrict_code, subdistrict_name_th
                                                  FROM execute_result
                                                  JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                                 WHERE execute_type_name = 'ASF' AND result_for_year = :year AND district_code = :districtCode
                                                 ORDER BY subdistrict_name_th ASC");
    $asf_subdistrict_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $asf_subdistrict_query->bindValue(":districtCode", $_GET["district_code"], PDO::PARAM_STR);
    if($asf_subdistrict_query->execute()) {
        $asf_subdistrict_list = $asf_subdistrict_query->fetchAll();
        $asf_subdistrict_array = array();

        foreach($asf_subdistrict_list as $asf_source_single) {
            $asf_source_intermediate = array();
            $asf_source_intermediate["subdistrict_code"] = $asf_source_single["subdistrict_code"];
            $asf_source_intermediate["subdistrict_name_th"] = $asf_source_single["subdistrict_name_th"];
            array_push($asf_subdistrict_array, $asf_source_intermediate);
        }

        if(count($asf_subdistrict_array) > 0) {
            if($bool_return_json) return json_encode($asf_subdistrict_array);
            else return $asf_subdistrict_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>