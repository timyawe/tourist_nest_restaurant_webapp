<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	if(isset($_GET['ordStatus'])){
		$status = $_GET['ordStatus'];
		if($status == 'pending'){
			$sql = "SELECT * FROM OrdersExtended WHERE Station = '$station' AND Status = '$status' OR Station = '$station' AND Status = 'In Progress' ORDER BY time DESC";//user is filtering orders based on status
		}else{
			$sql = "SELECT * FROM OrdersExtended WHERE Station = '$station' AND Status = '$status' ORDER BY time DESC";
		}
	}else{
		$sql = "SELECT * FROM OrdersExtended WHERE Station = '$station' AND time > curdate() - INTERVAL 1 DAY + INTERVAL 19 HOUR AND Status <> 'Closed' ORDER BY time DESC";//user has clicked on all orders btn
	}
}else{
	if(isset($_GET['ordStatus'])){
		$status = $_GET['ordStatus'];
		if($status == 'pending'){
			$sql = "SELECT * FROM OrdersExtended WHERE Status = '$status' OR Status = 'In Progress' ORDER BY time DESC";
		}else{
			$sql = "SELECT * FROM OrdersExtended WHERE Status = '$status' ORDER BY time DESC";
		}
	}else{
		$sql = "SELECT * FROM OrdersExtended WHERE Status <> 'Closed' ORDER BY time DESC";
	}
	//$sql = "SELECT * FROM OrdersExtended WHERE Status <> 'Closed'";
}
//SELECT ordNo,station,`to`,`time`, `status`,`bill`,payment FROM nest_restaurant.ordersextended left join orderdetails on ordNo = OrderNo where Station = 'Reception' AND  ProductNo = 'PDT001' AND `time` > curdate() - INTERVAL 1 DAY + INTERVAL 19 HOUR group by ordNo;
$conn_res = json_decode(dbConn($sql, array(), 'select'));
if($conn_res->status === 1){
	foreach($conn_res->message as $v){
		$v->bill = number_format($v->bill);
		$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->time)) != date('d/m/Y')){
			$v->time = date("d/m/Y h:i A", strtotime($v->time));
		}else{
			$v->time = date("h:i A", strtotime($v->time));
		}
	}
	$res_records->status = $conn_res->status;
	$res_records->message = $conn_res->message;
	echo json_encode($res_records);
}else{
	$res_records->status = $conn_res->status;
	$res_records->message = $conn_res->message;
	echo json_encode($res_records);
}
?>
