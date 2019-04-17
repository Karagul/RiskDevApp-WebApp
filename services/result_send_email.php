<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo result_send_email(true);

function result_send_email($bool_return_json) {
    global $python_bin, $python_version;
    $result_type = $_GET["result_type"];
    $result_year = $_GET["result_year"];
    $email_recipient = $_GET["selected_recipient"];
    $subdistrict_list = $_GET["selected_subdistricts"];

    $python_script = "$python_bin ".dirname(__FILE__)."/../scripts/email_result.py $result_type $result_year $email_recipient $subdistrict_list";
    $python_output = exec($python_script." 2>&1");
    die($python_output);
}
?>