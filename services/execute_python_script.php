<?php
require_once dirname(__FILE__)."/../config.php";

session_start();

// System versions
$python_version = trim(exec("$python_bin -V 2>&1"));

// Input validation: User
if(isset($_SESSION["user_name"])) {
    $check_username_query = $db_conn->prepare("SELECT user_name
                                                 FROM user_account
                                                WHERE user_name = :username
                                                  AND user_type_name = 'ADMIN'");
    $check_username_query->bindValue(":username", $_SESSION["user_name"], PDO::PARAM_STR);
    if($check_username_query->execute()) {
        $check_username_result = $check_username_query->fetchAll();
        if(count($check_username_result) != 1) die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-1)");
    } else die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-1)");
} else die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-1)");

// Input validation: Execution type
if(isset($_POST["type"])) {
    $check_type_query = $db_conn->prepare("SELECT execute_type_name
                                             FROM execute_type
                                            WHERE execute_type_name = :typeName");
    $check_type_query->bindValue(":typeName", $_POST["type"], PDO::PARAM_STR);
    if($check_type_query->execute()) {
        $check_type_result = $check_type_query->fetchAll();
        if(count($check_type_result) != 1) die("ไม่พบประเภทการวิเคราะห์ กรุณาติดต่อผู้ดูแลระบบ");
    } else die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-2)");
} else die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-2)");

// Input validation: Execution for year
if(isset($_POST["year"])) {
    try {
        $year_value = intval($_POST["year"]);
        if($year_value < 1000 || $year_value > 3000) die("ไม่สามารถสั่งการประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-3)");
    } catch(Exception $e) {
        die("ไม่สามารถสั่งการประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-3)");
    }
} else die("ไม่สามารถสั่งประมวลผลข้อมูลได้ กรุณาติดต่อผู้ดูแลระบบ (Code: EXEC-3)");

// Clearing any previous runs with the same execution type
$clear_previous_run_query = $db_conn->prepare("DELETE FROM execute_result
                                                WHERE execute_type_name = :executeType");
$clear_previous_run_query->bindValue(":executeType", $_POST["type"], PDO::PARAM_STR);
$clear_previous_run_query->execute();

// ========== BEGIN OF Calculating the Python Model ==========
// Parameter Settings
$beta_value    = 0.1;
$gamma_value   = 0.5;
$sigma_value   = 0.0;
$cleanup_stage = 0.0;

// Determining the set of parameters to be used
$query_text = "SELECT param_name, param_value FROM system_param WHERE param_name IN ";
switch($_POST["type"]) {
    case "ASF": 
        $query_text .= "('ASF_BETA_VALUE', 'ASF_GAMMA_INV_VALUE', 'ASF_SIGMA_INV_VALUE')";
        break;
    case "FMD": 
        $query_text .= "('FMD_BETA_VALUE', 'FMD_GAMMA_INV_VALUE', 'FMD_SIGMA_INV_VALUE')";
        break;
    case "HPAI": 
        $query_text .= "('HPAI_BETA_VALUE', 'HPAI_GAMMA_INV_VALUE', 'HPAI_SIGMA_INV_VALUE')";
        break;
    case "NIPAH": 
        $query_text .= "('NIPAH_BETA_VALUE', 'NIPAH_GAMMA_INV_VALUE', 'NIPAH_SIGMA_INV_VALUE')";
        break;
    default: die("WTF");
}

$param_list_query = $db_conn->prepare($query_text);
if($param_list_query->execute()) {
    $param_list = $param_list_query->fetchAll();

    foreach($param_list as $param_single) {        
        switch($param_single["param_name"]) {
            // ASF
            case "ASF_BETA_VALUE":        $beta_value  = $param_single["param_value"]; break;
            case "ASF_GAMMA_INV_VALUE":   $gamma_value = $param_single["param_value"]; break;
            case "ASF_SIGMA_INV_VALUE":   $sigma_value = $param_single["param_value"]; break;
            // FMD
            case "FMD_BETA_VALUE":        $beta_value  = $param_single["param_value"]; break;
            case "FMD_GAMMA_INV_VALUE":   $gamma_value = $param_single["param_value"]; break;
            case "FMD_SIGMA_INV_VALUE":   $sigma_value = $param_single["param_value"]; break;
            // HPAI
            case "HPAI_BETA_VALUE":        $beta_value  = $param_single["param_value"]; break;
            case "HPAI_GAMMA_INV_VALUE":   $gamma_value = $param_single["param_value"]; break;
            case "HPAI_SIGMA_INV_VALUE":   $sigma_value = $param_single["param_value"]; break;
            // NIPAH
            case "NIPAH_BETA_VALUE":        $beta_value  = $param_single["param_value"]; break;
            case "NIPAH_GAMMA_INV_VALUE":   $gamma_value = $param_single["param_value"]; break;
            case "NIPAH_SIGMA_INV_VALUE":   $sigma_value = $param_single["param_value"]; break;
        }
    }
} else die(var_dump($param_list_query->errorInfo()));

switch($_POST["type"]) {
    case "ASF": 
        $python_script = "$python_bin ".dirname(__FILE__)."\\..\\scripts\\Scripts\\ASF_riskMapCreation.py ".dirname(__FILE__)."\\..\\results $cleanup_stage $beta_value $gamma_value $sigma_value ASF";
        break;
    case "FMD": 
        $python_script = "$python_bin ".dirname(__FILE__)."\\..\\scripts\\Scripts\\FMD_riskMapCreation.py ".dirname(__FILE__)."\\..\\results $cleanup_stage $beta_value $gamma_value $sigma_value";
        break;
    case "HPAI": 
        $python_script = "$python_bin ".dirname(__FILE__)."\\..\\scripts\\Scripts\\HPAI_riskMapCreation.py ".dirname(__FILE__)."\\..\\results $cleanup_stage $beta_value $gamma_value $sigma_value";
        break;
    case "NIPAH": 
        $python_script = "$python_bin ".dirname(__FILE__)."\\..\\scripts\\Scripts\\NIPAH_riskMapCreation.py ".dirname(__FILE__)."\\..\\results $cleanup_stage $beta_value $gamma_value $sigma_value NIPAH";
        break;
}
$python_output = exec($python_script." 2>&1");
die("===== Using $python_version =====\n$python_output\nสั่งการประมวลผลแล้ว กรุณารอสักครู่");
// ========== END   OF Calculating the Python Model ==========

// ========== BEGIN OF Execution Logging ==========
/*$insert_log_query = $db_conn->prepare("INSERT INTO execute_result VALUES(:epidemicType, :currentDate, :currentUser,
                                                                       'PENDING', :executeFirstDate, '', '', 0, 0)");
$insert_log_query->bindValue("epidemicType", $_POST["type"], PDO::PARAM_STR);
$insert_log_query->bindValue(":currentDate", date("Y-m-d"), PDO::PARAM_STR);
$insert_log_query->bindValue(":currentUser", $_SESSION["user_name"], PDO::PARAM_STR);
$insert_log_query->bindValue(":resultForYear", $_POST["year"], PDO::PARAM_STR);
if(!$insert_log_query->execute()) die("ไม่สามารถบันทึกการวิเคราะห์ได้ กรุณาติดต่อผู้ดูแลระบบ");*/
// ========== END   OF Execution Logging ==========
?>