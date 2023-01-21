<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');

$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
$off_fields = [];

foreach($clean_data as $k => $v){
	if(!is_array($v)){
		$off_fields[$k] = $v;
	}
}

$off_details = $clean_data['details'];

$off_sql = "INSERT INTO Offers (Station, RecipientCategory, UserID) VALUES (?,?,?)";
$off_details_sql = "INSERT INTO OffersDetails (ProductNo, Qty, OffersID) VALUES (?,?,?)";
$off_det_bindTypes = "sii";

array_unshift($off_fields, createBindTypes($off_fields));

$off_res = json_decode(dbConn($off_sql, $off_fields, 'insert'));

if($off_res->status == 1){
	$details_fail = [];
	$fkey = $off_res->insertID;
	if(json_decode(mutateOrderDetails($off_details, $off_det_bindTypes, $fkey, $off_details_sql))->status != 1){
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