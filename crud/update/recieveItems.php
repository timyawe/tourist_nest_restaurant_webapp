<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$recd_fail = [];//holds item lines which have failed to update

foreach($clean_data['recvd_items'] as $v){
	$detNo = array_shift($v);
	$fields_arr = array_keys($v);
	array_unshift($v, createBindTypes($v));
	$res = json_decode(dbConn(createUpdateSql($fields_arr, 'PurchaseDetails', 'Details_No', $detNo), $v, 'update'));
	
	if($res->status === 0){
		array_push($recd_fail, $detNo); 
	}
}

$json_res = new stdClass();

if(!empty($recd_fail)){
	$json_res->status = 0;
	$json_res->message = "Some of the Items failed to update, reload and try again";
	echo json_encode($json_res);
}else{
	$json_res->status = 1;
	$json_res->message = "Updated Succesfully";
	echo json_encode($json_res);
}
?>