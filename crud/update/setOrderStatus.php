<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $clean_data['ordNo'];
$res = new stdClass();

if(array_key_exists('deliveredby',$clean_data )){
	$field = 'DeliveredBy';
	$value = $clean_data['deliveredby'];
}else{
	$field = 'RecievedBy';
	$value = $clean_data['recievedby'];
}

$dbAccess = json_decode(dbConn("UPDATE OrderStatus SET $field = ? WHERE OrderNo = '$ordNo'", array('s', $value), 'update'));
if($dbAccess->status = 1){
	//updateActivityLog('Update Order', 'Order '.$ordNo .' updated to Delivered successfully', $clean_data['userID']);
	/*$file = 'updateOrder.txt';
	$file_write = fopen($file, 'a');
	date_default_timezone_set("Africa/Nairobi");
	fwrite($file_write, createUpdateSql($fields_arr, 'Orders', 'Order_No', $ordNo).date('d/m/Y h:i:sa')."\n");
	fclose($file_write);
	file_put_contents($file, print_r($clean_data, true)."post_data \n", FILE_APPEND);
	file_put_contents($file, print_r(comparisonRecord('Orders', 'Order_No', $ordNo), true)."res_conn \n", FILE_APPEND);
	file_put_contents($file, print_r($final_arr, true)."\n", FILE_APPEND);*/
	
	$res->status = 1;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}else{
	$res->status = $dbAccess->status;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}


?>
