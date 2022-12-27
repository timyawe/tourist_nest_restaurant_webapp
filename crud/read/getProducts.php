<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
//$res_records = new stdClass();

$sql = "SELECT * FROM Products";
$sqlres_records = json_decode(dbConn($sql, array(), "select"));

if($sqlres_records->status === 1){
	echo json_encode($sqlres_records->message);
}
?>