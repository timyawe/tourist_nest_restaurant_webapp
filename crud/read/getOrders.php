<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT * FROM OrdersExtended WHERE Station = '$station'";
}else{
	$sql = "SELECT * FROM OrdersExtended";
}

$res_records = json_decode(dbConn($sql, array(), "select"));
if($res_records->status === 1){
	foreach($res_records->message as $v){
		$v->bill = number_format($v->bill);
		$v->payment = number_format($v->payment);
		$v->time = date("d/m/Y h:i A", strtotime($v->time));
	}
	echo json_encode($res_records->message);
}
?>