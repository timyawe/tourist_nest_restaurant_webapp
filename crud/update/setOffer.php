<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$offID = $clean_data['offerID'];
$res = new stdClass();

$dbAccess = json_decode(dbConn("UPDATE Offers SET isDelivered = ? WHERE ID = '$offID'", array('i', $clean_data['isDelivered']), 'update'));
if($dbAccess->status = 1){
	//updateActivityLog('Update Order', 'Order '.$ordNo .' updated to Delivered successfully', $clean_data['userID']);
	$res->status = 1;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}else{
	$res->status = $dbAccess->status;
	$res->message = $dbAccess->message;
	echo json_encode($res);
}

?>
