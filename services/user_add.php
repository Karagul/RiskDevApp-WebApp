<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo user_add($_POST["return_type"]);

function user_add($return_type) {
    global $db_conn;
	
    // Checking Input Validity
    if(!isset($_POST["user_name"]) || !isset($_POST["user_type"])) die("ค่าตัวแปรไม่ถูกต้อง กรุณาติดต่อผู้ดูแลระบบ");

    // Preparing default values
    $user_password   = base64_encode($_POST["user_name"]);
    $valid_from_date = date("Y-m-d");
    //$valid_to_date   = "9999-12-31";
    $valid_to_date   = date("Y-m-d", strtotime($_POST["valid_to"]));

    // Checking Username Validity
    $user_check_query = $db_conn->prepare("SELECT *
                                             FROM user_account
                                            WHERE user_name = :username");
    $user_check_query->bindParam(":username", $_POST["user_name"]);
    if($user_check_query->execute()) {
        $user_check_result = $user_check_query->fetchAll();
        $user_account_array = [];

        foreach($user_check_result as $user_check_single) {
            array_push($user_account_array, ["user_name" => $user_check_single["user_name"]]);
        }

        if(count($user_account_array) > 0) die("มีผู้ใช้ดังกล่าวอยู่ในระบบอยู่แล้ว");
    } else die(var_dump($user_check_query->errorInfo()));

    // Checking User Type Validity
	//die("User Existence Checked: Pass");

    // Adding a new user
    $user_add_query = $db_conn->prepare("INSERT INTO user_account
                                         VALUES(:username, :usertype, :password, :validfrom, :validto)");
    $user_add_query->bindParam(":username", $_POST["user_name"], PDO::PARAM_STR);
    $user_add_query->bindParam(":usertype", $_POST["user_type"], PDO::PARAM_STR);
    $user_add_query->bindParam(":password", $user_password, PDO::PARAM_STR);
    $user_add_query->bindParam(":validfrom", $valid_from_date, PDO::PARAM_STR);
    $user_add_query->bindParam(":validto", $valid_to_date, PDO::PARAM_STR);
    if($user_add_query->execute()) {
        // Checking User Add Result
        $user_check_query = $db_conn->prepare("SELECT *
                                                  FROM user_account
                                                 WHERE user_name = :username");
        $user_check_query->bindParam(":username", $_POST["user_name"], PDO::PARAM_STR);
        if($user_check_query->execute()) {
            $user_check_result = $user_check_query->fetchAll();
            $user_account_array = [];

            foreach($user_check_result as $user_check_single) {
                array_push($user_account_array, ["user_name" => $user_check_single["user_name"]]);
            }

            if(count($user_account_array) > 0) die("สร้างผู้ใช้สำเร็จ");
            else die("ไม่สามารถสร้างผู้ใช้งานได้ในขณะนี้ กรุณาติดต่อผู้ดูแลระบบ"); 
        } else die(var_dump($user_check_query->errorInfo()));
    } else die(var_dump($user_add_query->errorInfo()));
}
?>