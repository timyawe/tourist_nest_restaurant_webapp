<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$tblID = $clean_data['tblID'];

/* Here select record for comparison with submitted data */
$select_sql = "SELECT * FROM `Tables` WHERE Table_ID = '$tblID'";

//print_r (json_decode(dbConn($select_sql, array(), "select"))->message[0]->Status);
$res_obj = json_decode(dbConn($select_sql, array(), "select"))->message[0];
$res_arr = json_decode(json_encode($res_obj),true);//turns object to assoc array

$final_arr = array_diff($clean_data, $res_arr);

if(!empty($final_arr)){
	$fields_arr = array_keys($final_arr);

	array_unshift($final_arr, createBindTypes($final_arr));
	
	echo dbConn(createUpdateSql($fields_arr, "Tables", "Table_ID", $tblID),$final_arr, "update");
}else{
	$json_res = new stdClass();
	$json_res->status = 2;
	$json_res->message = "No fields were edited";
	echo json_encode($json_res);
}

?>