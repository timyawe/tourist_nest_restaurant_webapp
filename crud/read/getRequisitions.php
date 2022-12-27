<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT * FROM PurchaseOrdersExtended WHERE Station = '$station'";
}else{
	$sql = "SELECT * FROM PurchaseOrdersExtended";
}

$res_records = json_decode(dbConn($sql, array(), "select"));
if($res_records->status === 1){
	foreach($res_records->message as $v){
		$v->amount = number_format($v->amount);
		//$v->payment = number_format($v->payment);
		$v->_date = date("d/m/Y", strtotime($v->_date));
	}
	echo json_encode($res_records->message);
}
?>