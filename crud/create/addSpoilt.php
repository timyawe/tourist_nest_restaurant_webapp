<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');

$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
$spt_fields = [];

foreach($clean_data as $k => $v){
	if(!is_array($v)){
		$spt_fields[$k] = $v;
	}
}

$spt_details = $clean_data['details'];

$spt_sql = "INSERT INTO Items_Spoilt (Station, UserID) VALUES (?,?)";
$spt_details_sql = "INSERT INTO SpoiltDetails (ProductNo, Qty, SpoiltID) VALUES (?,?,?)";
$spt_det_bindTypes = "sii";

array_unshift($spt_fields, createBindTypes($spt_fields));

$spt_res = json_decode(dbConn($spt_sql, $spt_fields, 'insert'));

if($spt_res->status == 1){
	$details_fail = [];
	$fkey = $spt_res->insertID;
	if(json_decode(mutateOrderDetails($spt_details, $spt_det_bindTypes, $fkey, $spt_details_sql))->status != 1){
		array_push($details_fail, 0);
	}
}

$json_res = new stdClass();
if(empty($details_fail)){
	$json_res->status = 1;
	$json_res->message = "Added Succesfully";
	echo json_encode($json_res);
}else{
	$json_res->status = 0;
	$json_res->message = "Failed";
	echo json_encode($json_res);
}

?>