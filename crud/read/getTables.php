<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

$sql = "SELECT * FROM `Tables`";
$res_records = json_decode(dbConn($sql, array(), 'select'));
if($res_records->status === 1){
	
	foreach($res_records->message as $v){
		if($v->Status == 1){
			$v->Status = "In Use";
		}else{
			$v->Status = "Out of Use";
		}
	}
	echo json_encode($res_records->message);
}

?>