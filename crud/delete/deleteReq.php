<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$reqID = $json_data['reqNo']; //$json_data['detailsID'];

$delete_sql = "DELETE FROM PurchaseOrder WHERE Purchase_No = '$reqID' LIMIT 1";
$json_res = new stdClass();
if(json_decode(dbConn($delete_sql, array(), 'delete'))->status == 1){
	updateActivityLog('Delete Requisition', 'Requisition '.$reqID. ' deleted successfully', $json_data['userID']);
	$json_res->status = 1;
	$json_res->message = "Deleted Sucessfully";
	echo json_encode($json_res);
}
	
?>