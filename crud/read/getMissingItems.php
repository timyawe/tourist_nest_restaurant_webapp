<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

/*if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT * FROM Items_SpoiltExtended WHERE Station = '$station'";
}else{*/
	$sql = "SELECT * FROM Items_MissingExtended";
//}

$res_conn = json_decode(dbConn($sql, array(), 'select'));
if($res_conn->status === 1){
	foreach($res_conn->message as $v){
		$v->_date = date("d/m/Y", strtotime($v->_date));
	}
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}
?>
