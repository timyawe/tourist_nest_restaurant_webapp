<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(!$_GET['station']){
	$station = $_GET['station'];
	$sql = "SELECT * FROM Items_SpoiltExtended WHERE Station = '$station' AND Year(_date) = Year(current_date()) AND Month(_date) = Month(current_date()) ORDER BY _date DESC";
}else{
	$sql = "SELECT * FROM Items_SpoiltExtended WHERE Year(_date) = Year(current_date()) AND Month(_date) = Month(current_date()) ORDER BY _date DESC";
}

$res_conn = json_decode(dbConn($sql, array(), 'select'));
if($res_conn->status === 1){
	foreach($res_conn->message as $v){
		//$v->amount = number_format($v->amount);
		//$v->payment = number_format($v->payment);
		$v->_date = date("d/m/Y", strtotime($v->_date));
	}
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}
?>
