<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$missingID = $_GET['missingID'];

$missing_sql = "SELECT Station FROM Items_Missing WHERE ID=$missingID";
$missing_details_sql = "SELECT * FROM missingdetails_ext WHERE missingID = $missingID";

$json_res = new stdClass();
$missing_res = json_decode(dbConn($missing_sql, array(), 'select'));

if($missing_res->status == 1){
	$missing_dets_res = json_decode(dbConn($missing_details_sql, array(), 'select'));
	if($missing_dets_res->status == 1){
		$json_res->status = 1;
		$json_res->extra = $missing_res->message;
		$json_res->extra_details = $missing_dets_res->message;
		
		echo json_encode($json_res);
	}
}

?>
