<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/comparisonRecord.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $clean_data['ordNo'];

$final_arr = array_diff($clean_data, comparisonRecord('Orders', 'Order_No', $ordNo));

if(!empty($final_arr)){
	$fields_arr = array_keys($final_arr);
	array_unshift($final_arr, createBindTypes($final_arr));
	echo dbConn(createUpdateSql($fields_arr, 'Orders', 'Order_No', $ordNo), $final_arr, 'update');
}

?>