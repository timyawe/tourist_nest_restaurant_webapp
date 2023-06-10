<?php
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

//$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $json_data['ordNo'];
$detailsNo = $json_data['detailsNo'];
$status = $json_data['status'];
$res = new stdClass();

$dbAccess = json_decode(dbConn("UPDATE OrderDetails SET DeliveredStatus = ? WHERE Details_No = $detailsNo", array('i',$status), 'update'));

if($dbAccess->status == 1){
	$ordItems_sql = "SELECT qty, rate, total, item, preptime, Details_No, DeliveredStatus FROM OrderDetailsExtended WHERE OrderNo = '$ordNo'";
	$res_ordrec = json_decode(dbConn($ordItems_sql, array(), 'select'));
		$order_details = $res_ordrec->message;
		$res->status = 1;
		$res->ord_details = $order_details;
		
		echo json_encode($res);
}
?>
