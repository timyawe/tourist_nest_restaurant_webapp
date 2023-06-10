<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$ords_res = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$allords_sql = "SELECT * FROM OrdersExtended WHERE  Station = '$station' AND Date(time) = curdate()";
	$delvords_sql = "SELECT * FROM OrdersExtended WHERE Status = 'Delivered' AND Station = '$station' AND Date(time) = curdate()";
	$pendords_sql = "SELECT * FROM OrdersExtended WHERE Status = 'Pending' AND Station = '$station' AND Date(time) = curdate() OR Status = 'In Progress' AND Station = '$station' AND Date(time) = curdate() ";
}else{
	$allords_sql = "SELECT * FROM OrdersExtended WHERE  time = curdate()";
	$delvords_sql = "SELECT * FROM OrdersExtended WHERE Status = 'Delivered' AND Date(time) = curdate()";
	$pendords_sql = "SELECT * FROM OrdersExtended WHERE Status = 'Pending' AND Date(time) = curdate() OR Status = 'In Progress' AND Date(time) = curdate()";
}



$allord_res_records = json_decode(dbConn($allords_sql, array(), 'select'));
$delvords_res_records = json_decode(dbConn($delvords_sql, array(), 'select'));
$pendords_res_records = json_decode(dbConn($pendords_sql, array(), 'select'));

if($allord_res_records->status === 1 || $allord_res_records->status === 2){
	$ords_res->allorders = $allord_res_records->numrows;
	$ords_res->allorders_records = $allord_res_records->message;
	//echo json_encode($allord_res_records->numrows);
}

if($delvords_res_records->status === 1 || $delvords_res_records->status === 2){
	$ords_res->deliveredorders = $delvords_res_records->numrows;
	$ords_res->deliveredorders_records = $delvords_res_records->message;
	//echo json_encode($delvords_res_records->numrows);
}

if($pendords_res_records->status === 1 || $pendords_res_records->status === 2){
	$ords_res->pendingorders = $pendords_res_records->numrows;
	$ords_res->pendingorders_records = $pendords_res_records->message;
	//echo json_encode($pendords_res_records->numrows);
}
echo json_encode($ords_res);
?>
