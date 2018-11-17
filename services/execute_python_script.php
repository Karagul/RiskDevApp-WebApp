<?php
require_once dirname(__FILE__)."/../config.php";

session_start();

// Input Validation - Type
if(!isset($_POST["type"]) || !check_type($_POST["type"])) die("ไม่พบประเภทการวิเคราะห์ กรุณาติดต่อผู้ดูแลระบบ");

// Input Validation - Year
if(!isset($_POST["year"]) || !check_year($_POST["year"])) die("ไม่พบข้อมูลปี กรุณาติดต่อผู้ดูแลระบบ");

// Input Validation - Username
if(!isset($_SESSION["user_name"]) || !check_username($_SESSION["user_name"])) die("ไม่พบชื่อผู้ใช้งาน กรุณาติดต่อผู้ดูแลระบบ");

// ========== BEGIN OF Execution Logging ==========
$insert_log_query = $db_conn->prepare("INSERT INTO result_nipah VALUES(:executeID, 'NIPAH', :currentDate, :currentUser,
                                                                       'PENDING', :executeFirstDate, '', '', 0, 0)");
$insert_log_query->bindValue(":executeID", get_execute_id(), PDO::PARAM_INT);
$insert_log_query->bindValue(":currentDate", date("Y-m-d"), PDO::PARAM_STR);
$insert_log_query->bindValue(":currentUser", $_SESSION["user_name"], PDO::PARAM_STR);
$insert_log_query->bindValue(":executeFirstDate", $_POST["year"].'-01-01', PDO::PARAM_STR);
if(!$insert_log_query->execute()) die("ไม่สามารถบันทึกการวิเคราะห์ได้ กรุณาติดต่อผู้ดูแลระบบ");
// ========== END   OF Execution Logging ==========

// ========== BEGIN OF Calculating the Python Model ==========
// Parameter Settings
$beta_value = 0.1;
$gamma_value = 0.5;

$python_script = escapeshellcmd(dirname(__FILE__)."/../scripts/python modelProcess.py $cleanup_period $beta_value $gamma_value");
$python_output = shell_exec($python_script);
var_dump($python_output);
// ========== END   OF Calculating the Python Model ==========

// Function: Check username existence and privilege
function check_username($username) {
    global $db_conn;

    $check_username_query = $db_conn->prepare("SELECT user_name, user_type_name
                                                 FROM user_account
                                                WHERE user_name = :username AND user_type_name = 'ADMIN'");
    $check_username_query->bindValue(":username", $username, PDO::PARAM_STR);
    if($check_username_query->execute()) {
        $check_username_result = $check_username_query->fetchAll();
        if(count($check_username_result) == 1) return true;
        else return false;
    }
}

function check_type($type) {
    global $db_conn;

    $check_type_query = $db_conn->prepare("SELECT execute_type_name, execute_type_desc
                                             FROM result_type
                                            WHERE execute_type_name = :typeName");
    $check_type_query->bindValue(":typeName", $type, PDO::PARAM_STR);
    if($check_type_query->execute()) {
        $check_type_result = $check_type_query->fetchAll();
        if(count($check_type_result) == 1) return true;
        else return false;
    } else die("ไม่พบประเภทการวิเคราะห์ กรุณาติดต่อผู้ดูแลระบบ");
}

function check_year($year) {
    try {
        $year_value = intval($year);
        if($year_value >= 1000 && $year_value <= 9999) return true;
        else return false;
    } catch(Exception $e) {
        return false;
    }
}

function get_execute_id() {
    global $db_conn;

    $execute_id_query = $db_conn->prepare("SELECT MAX(execute_id) as current_max_id FROM result_nipah");
    if($execute_id_query->execute()) {
        $execute_id_result = $execute_id_query->fetch(PDO::FETCH_ASSOC);
        return intval($execute_id_result["current_max_id"]) + 1;
    }
}
?>