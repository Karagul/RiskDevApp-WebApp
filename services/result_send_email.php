<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo result_send_email(true);

function result_send_email($bool_return_json) {
    global $python_bin, $python_version;
    //$result_type = $_GET["result_type"];
    //$result_year = $_GET["result_year"];
    //$email_recipient = $_GET["selected_recipient"];
    //$subdistrict_list = $_GET["selected_subdistricts"];
	$result_type = $_POST["result_type"];
	$result_year = $_POST["result_year"];
	$email_recipient = $_POST["selected_recipient"];
	$subdistrict_list = $_POST["selected_subdistricts"];
	
	//beg+++iKS09.05.2019 Workaround for long subdistrict list selection (>1000 starting subdistricts)
	$temp_subdistrict_list_file_name = "tmp_subdistrict_".$email_recipient."_".date("YmdHis").".txt";
	file_put_contents("../temp/".$temp_subdistrict_list_file_name, $subdistrict_list);
	//$python_script = "$python_bin ".dirname(__FILE__)."/../scripts/email_result.py $result_type $result_year $email_recipient $subdistrict_list";
	$python_script = "$python_bin ".dirname(__FILE__)."/../scripts/email_result.py $result_type $result_year $email_recipient $temp_subdistrict_list_file_name";
	//end+++eKS09.05.2019 Workaround for long subdistrict list selection (>1000 starting subdistricts)
	$python_output = exec($python_script." 2>&1");
    die($python_output);
}
?>