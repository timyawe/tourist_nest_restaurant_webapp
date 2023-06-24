<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$orders_sql = "SELECT * FROM OrdersExtended WHERE Station = '$station' AND Status = 'Pending' OR Station = '$station' AND Status = 'In Progress' ORDER BY time DESC";
	$offers_sql = "SELECT * FROM OffersExtended WHERE Station = '$station' AND RecipientCategory = 'Eng' AND isDelivered = 0 OR Station = '$station' AND RecipientCategory = 'Visitor' AND isDelivered = 0 ORDER BY _date DESC";
}

$orders_conn_res = json_decode(dbConn($orders_sql, array(), 'select'));
$offers_conn_res = json_decode(dbConn($offers_sql, array(), 'select'));
if($orders_conn_res->status === 1){
	foreach($orders_conn_res->message as $v){
		$v->bill = number_format($v->bill);
		$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->time)) != date('d/m/Y')){
			$v->time = date("d/m/Y h:i A", strtotime($v->time));
		}else{
			$v->time = date("h:i A", strtotime($v->time));
		}
	}
	$res_records->status_orders = $orders_conn_res->status;
	$res_records->message = $orders_conn_res->message;
	
}
if($offers_conn_res->status == 1){
	
	foreach($offers_conn_res->message as $v){
		if(date("d/m/Y", strtotime($v->_date)) != date('d/m/Y')){
			$v->_date = date("d/m/Y h:i A", strtotime($v->_date));
		}else{
			$v->_date = date("h:i A", strtotime($v->_date));
		}
	}
	$res_records->status_offers = $offers_conn_res->status;
	$res_records->offers = $offers_conn_res->message;
	
}/*else{
	$res_records->status = $orders_conn_res->status;
	$res_records->message = $orders_conn_res->message;
	echo json_encode($res_records);
}*/

echo json_encode($res_records);
?>
