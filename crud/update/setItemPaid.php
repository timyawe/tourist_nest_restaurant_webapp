<?php
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

//$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $json_data['ordNo'];
$detailsNo = $json_data['Details_No'];
$item_total = $json_data['Total'];
if($json_data['PaidStatus'] == 0){
	$PaidStatus = 1;
}else{
	$PaidStatus = 0;
}
$res = new stdClass();
//echo $ordNo;echo $detailsNo;echo $PaidStatus;

if(!$PaidStatus){
	$dbAccess = json_decode(dbConn("UPDATE OrderDetails SET PaidStatus = ?, PaidDate = ? WHERE Details_No = $detailsNo", array('is','null'), 'update'));
}else{
	$ordpymt = intval(json_decode(dbConn("SELECT TotalPaid FROM OrderPayments_grouped WHERE OrderNo = '$ordNo'" , array(), 'select'))->message[0]->TotalPaid);
	$items_paid_total = intval(json_decode(dbConn("SELECT if(isnull(sum(total)),0,sum(total)) AS PaidTotal FROM OrderDetailsExtended WHERE OrderNo = '$ordNo' AND PaidStatus = 1" , array(), 'select'))->message[0]->PaidTotal);
	if(($ordpymt - $items_paid_total) < $item_total){
		$res->status = 0;
		$res->message = "Item not marked as paid, order payments not enough to cover item total. Adjust payments to continue";
		echo json_encode($res);
	}else{
		$dbAccess = json_decode(dbConn("UPDATE OrderDetails SET PaidStatus = ?, PaidDate = ? WHERE Details_No = $detailsNo", array('is',1,date('Y-m-d H:i:s')), 'update'));
	} 
	//var_dump($ordpymt);var_dump($items_paid_total);
}
//$dbAccess = json_decode(dbConn("UPDATE OrderDetails SET PaidStatus = ? WHERE Details_No = $detailsNo", array('i',$PaidStatus), 'update'));

if(isset($dbAccess) && $dbAccess->status == 1){
	$ordItems_sql = "SELECT qty, rate, total, item, preptime, Details_No, DeliveredStatus, PaidStatus FROM OrderDetailsExtended WHERE OrderNo = '$ordNo'";
	//$ordpymt_sql = "SELECT TotalPaid FROM OrderPayments_grouped WHERE OrderNo = '$ordNo'";
	$res_ordrec = json_decode(dbConn($ordItems_sql, array(), 'select'));
		$order_details = $res_ordrec->message;
		$res->status = 1;
		$res->ord_details = $order_details;
		//$json_ord->ordpymt = json_decode(dbConn($ordpymt_sql, array(), 'select'))->message;
		echo json_encode($res);
}
?>
