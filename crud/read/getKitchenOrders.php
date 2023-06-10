<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT * FROM OrdersExtended WHERE Station = '$station' AND Status = 'Pending' OR Station = '$station' AND Status = 'In Progress' ORDER BY time DESC";
}

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
