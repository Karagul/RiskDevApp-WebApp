<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo fmd_starting_subdistrict(true);

function fmd_starting_subdistrict($bool_return_json) {
    global $db_conn;

    $fmd_subdistrict_query = $db_conn->prepare("SELECT DISTINCT subdistrict_master.subdistrict_code, subdistrict_name_th
                                                  FROM execute_result
                                                  JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                                 WHERE execute_type_name = 'FMD' AND result_for_year = :year AND district_code = :districtCode
                                                 ORDER BY subdistrict_name_th ASC");
    $fmd_subdistrict_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $fmd_subdistrict_query->bindValue(":districtCode", $_GET["district_code"], PDO::PARAM_STR);
    if($fmd_subdistrict_query->execute()) {
        $fmd_subdistrict_list = $fmd_subdistrict_query->fetchAll();
        $fmd_subdistrict_array = array();

        foreach($fmd_subdistrict_list as $fmd_source_single) {
            $fmd_source_intermediate = array();
            $fmd_source_intermediate["subdistrict_code"] = $fmd_source_single["subdistrict_code"];
            $fmd_source_intermediate["subdistrict_name_th"] = $fmd_source_single["subdistrict_name_th"];
            array_push($fmd_subdistrict_array, $fmd_source_intermediate);
        }

        if(count($fmd_subdistrict_array) > 0) {
            if($bool_return_json) return json_encode($fmd_subdistrict_array);
            else return $fmd_subdistrict_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>