<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$ordNo = $clean_data['ordNo'];
$ord_sql = "SELECT Station, `To`, DeliveryPoint, OrderDate, OrderStatus, DeliveredTime, if(OrderDate < '2023-06-05 19:00:00',Orders.RecievedBy,OrderStatus.RecievedBy) AS RecievedBy, if(OrderDate < '2023-06-05 19:00:00',Orders.DeliveredBy,OrderStatus.DeliveredBy) AS DeliveredBy, FirstName FROM Orders JOIN Users on UserID = ID left JOIN OrderStatus on Order_No = OrderNo WHERE Order_No = '$ordNo'";
$ordItems_sql = "SELECT qty, rate, total, item, preptime, Details_No, DeliveredStatus FROM OrderDetailsExtended WHERE OrderNo = '$ordNo'";
$json_ord = new stdClass();

$res_records = json_decode(dbConn($ord_sql, array(), 'select'));

if($res_records->status === 1){
	$order = $res_records->message;
	$res_ordrec = json_decode(dbConn($ordItems_sql, array(), 'select'));
	if($res_ordrec->status === 1){
		foreach($order as $v){
			//$v->OrderDate = date("d/m/Y h:i A", strtotime($v->OrderDate));
			//$v->DeliveredTime = date("d/m/Y h:i A", strtotime($v->DeliveredTime));
		}
		$order_details = $res_ordrec->message;
		$json_ord->status = 1;
		$json_ord->order = $order;
		$json_ord->ord_details = $order_details;
		
		echo json_encode($json_ord);
	}
}

?>
