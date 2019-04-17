<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo hpai_starting_subdistrict(true);

function hpai_starting_subdistrict($bool_return_json) {
    global $db_conn;

    $hpai_subdistrict_query = $db_conn->prepare("SELECT DISTINCT subdistrict_master.subdistrict_code, subdistrict_name_th
                                                  FROM execute_result
                                                  JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                                 WHERE execute_type_name = 'HPAI' AND result_for_year = :year AND district_code = :districtCode
                                                 ORDER BY subdistrict_name_th ASC");
    $hpai_subdistrict_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $hpai_subdistrict_query->bindValue(":districtCode", $_GET["district_code"], PDO::PARAM_STR);
    if($hpai_subdistrict_query->execute()) {
        $hpai_subdistrict_list = $hpai_subdistrict_query->fetchAll();
        $hpai_subdistrict_array = array();

        foreach($hpai_subdistrict_list as $hpai_source_single) {
            $hpai_source_intermediate = array();
            $hpai_source_intermediate["subdistrict_code"] = $hpai_source_single["subdistrict_code"];
            $hpai_source_intermediate["subdistrict_name_th"] = $hpai_source_single["subdistrict_name_th"];
            array_push($hpai_subdistrict_array, $hpai_source_intermediate);
        }

        if(count($hpai_subdistrict_array) > 0) {
            if($bool_return_json) return json_encode($hpai_subdistrict_array);
            else return $hpai_subdistrict_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>