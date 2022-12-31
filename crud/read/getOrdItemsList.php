<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

$sql = "SELECT * FROM OrdItemsList";
$res_records = json_decode(dbConn($sql, array(), 'select'));

if($res_records->status === 1){
	echo json_encode($res_records->message);
}
?>