<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/comparisonRecord.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$pymtID = $clean_data['pymtID'];
foreach($clean_data as $k => $v){
	if($k == "paymentdate"){
		$d = date("Y-m-d", strtotime($v));
		$clean_data['paymentdate'] = $d;
	}
	/*if($k == "ordNo"){
		$ordNo = $v;
		unset($clean_data['ordNo']);
	}*/
}

$final_arr = array_diff($clean_data, comparisonRecord('Payments', 'Payment_ID', $pymtID));

if(!empty($final_arr)){
	$fields_arr = array_keys($final_arr);
	array_unshift($final_arr, createBindTypes($final_arr));
	echo dbConn(createUpdateSql($fields_arr, 'Payments', 'Payment_ID', $pymtID),$final_arr, 'update');
}
?>