<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();
/*$res_records->ID = "GRD101";
$res_records->description = "Upper Western";
$res_records->capacity = 7;
$res_records->location = "Garden";
$res_records->status = "In Use";

$res_records->ID = "RES101";
$res_records->description = "Table 10";
$res_records->capacity = 4;
$res_records->location = "Restaurant";
$res_records->status = "In Use";
*/

$sql = "SELECT * FROM `Tables`";
$res_records = json_decode(dbConn($sql, array(), "select"));
if($res_records->status === 1){
	//$records = [$res_records->message];
	//echo json_encode($records);
	///echo json_encode($records = [$res_records->message]);
	foreach($res_records->message as $v){
		if($v->Status == 1){
			$v->Status = "In Use";
		}else{
			$v->Status = "Out of Use";
		}
	}
	echo json_encode($res_records->message);
}

//echo json_encode($records = [$res_records]);




?>