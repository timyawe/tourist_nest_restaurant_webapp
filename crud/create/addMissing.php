<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');

$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
$missing_fields = [];

foreach($clean_data as $k => $v){
	if(!is_array($v)){
		$missing_fields[$k] = $v;
	}
}

$missing_details = $clean_data['details'];

$missing_sql = "INSERT INTO Items_Missing (Station, UserID) VALUES (?,?)";
$missing_details_sql = "INSERT INTO MissingDetails (ProductNo, Qty, MissingID) VALUES (?,?,?)";
$missing_det_bindTypes = "sii";

array_unshift($missing_fields, createBindTypes($missing_fields));
//print_r($missing_fields); print_r($missing_details); 
$missing_res = json_decode(dbConn($missing_sql, $missing_fields, 'insert'));

if($missing_res->status == 1){
	$details_fail = [];
	$fkey = $missing_res->insertID;
	if(json_decode(mutateOrderDetails($missing_details, $missing_det_bindTypes, $fkey, $missing_details_sql))->status != 1){
		array_push($details_fail, 0);
	}
}

$json_res = new stdClass();
if(empty($details_fail)){
	updateActivityLog('Insert SpoiltItem', 'Offer #'. $fkey . ' added successfully', $clean_data['UserID']);
	$json_res->status = 1;
	$json_res->message = "Added Succesfully";
	echo json_encode($json_res);
}else{
	$json_res->status = 0;
	$json_res->message = "Failed";
	echo json_encode($json_res);
}

?>
