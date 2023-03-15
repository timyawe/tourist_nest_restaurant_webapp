<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/comparisonRecord.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $clean_data['ordNo'];
$res = new stdClass();
$final_arr = array_diff($clean_data, comparisonRecord('Orders', 'Order_No', $ordNo));

if(!empty($final_arr)){
	$fields_arr = array_keys($final_arr);
	array_unshift($final_arr, createBindTypes($final_arr));
	$dbAccess = json_decode(dbConn(createUpdateSql($fields_arr, 'Orders', 'Order_No', $ordNo), $final_arr, 'update'));
	if($dbAccess->status = 1){
		updateActivityLog('Update Order', 'Order '.$ordNo .' updated to Delivered successfully', $clean_data['userID']);
		$res->status = 1;
		$res->message = $dbAccess->message;
		echo json_encode($res);
	}else{
		$res->status = $dbAccess->status;
		$res->message = $dbAccess->message;
		echo json_encode($res);
	}
}

?>