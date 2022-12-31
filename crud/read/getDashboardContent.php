<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$ords_res = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$allords_sql = "SELECT Order_No FROM Orders WHERE  Station = '$station'";
	$delvords_sql = "SELECT Order_No FROM Orders WHERE OrderStatus = 'Delivered' AND Station = '$station'";
	$pendords_sql = "SELECT Order_No FROM Orders WHERE OrderStatus = 'Pending' AND Station = '$station'";
}else{
	$allords_sql = "SELECT Order_No FROM Orders WHERE  Station = '$station'";
	$delvords_sql = "SELECT Order_No FROM Orders WHERE OrderStatus = 'Delivered' AND Station = '$station'";
	$pendords_sql = "SELECT Order_No FROM Orders WHERE OrderStatus = 'Pending' AND Station = '$station'";
}



$allord_res_records = json_decode(dbConn($allords_sql, array(), 'select'));
$delvords_res_records = json_decode(dbConn($delvords_sql, array(), 'select'));
$pendords_res_records = json_decode(dbConn($pendords_sql, array(), 'select'));

if($allord_res_records->status === 1 || $allord_res_records->status === 2){
	$ords_res->allorders = $allord_res_records->numrows;
	//echo json_encode($allord_res_records->numrows);
}

if($delvords_res_records->status === 1 || $delvords_res_records->status === 2){
	$ords_res->deliveredorders = $delvords_res_records->numrows;
	//echo json_encode($delvords_res_records->numrows);
}

if($pendords_res_records->status === 1 || $pendords_res_records->status === 2){
	$ords_res->pendingorders = $pendords_res_records->numrows;
	//echo json_encode($pendords_res_records->numrows);
}
echo json_encode($ords_res);
?>