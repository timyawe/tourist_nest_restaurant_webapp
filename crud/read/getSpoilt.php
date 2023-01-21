<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$spoiltID = $_GET['spoiltID'];

$spt_sql = "SELECT Station FROM Items_Spoilt WHERE ID=$spoiltID";
$spt_details_sql = "SELECT * FROM spoiltdetails_ext WHERE SpoiltID = $spoiltID";

$json_res = new stdClass();
$spt_res = json_decode(dbConn($spt_sql, array(), 'select'));

if($spt_res->status == 1){
	$spt_dets_res = json_decode(dbConn($spt_details_sql, array(), 'select'));
	if($spt_dets_res->status == 1){
		$json_res->status = 1;
		$json_res->extra = $spt_res->message;
		$json_res->extra_details = $spt_dets_res->message;
		
		echo json_encode($json_res);
	}
}

?>