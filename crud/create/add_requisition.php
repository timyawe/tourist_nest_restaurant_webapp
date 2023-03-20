<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';


$json_data_post = file_get_contents('php://input');

//Order form submitts order details in array format with objects 
//The objects are turned into assoc arrays by the json_decode with the true value as below
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
$req_tmplt = array('station', 'category', 'requisitiontype' );
$req_fields = createFields($json_data, $req_tmplt);
$req_det_fields = $clean_data['details'];//The array is accessed and saved in variable

$req_sql = "INSERT INTO PurchaseOrder (Purchase_No, PurchaseStatus, Category, Station, RequisitionType, UserID) VALUES (?,?,?,?,?,?)";
$reqbind_types = "sssssi";

if($clean_data['requisitiontype'] == 'External'){
	$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, PurchaseAmount, Qty, Rate, StandardCost, PurchaseNo) VALUES (?,?,?,?,?,?)";
	$req_detbind_types = "sdidds";
}else{
	$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, Qty, PurchaseNo) VALUES (?,?,?)";
	$req_detbind_types = "sis";
}

$given_sql = "INSERT INTO InternalRequisition_Given (DetailsNo) VALUES (?)";

$p_key = genPK('PurchaseOrder', 'Purchase_No', 'REQ');
$req_status = "Submitted";
$usrID = $clean_data['userID'];

array_unshift($req_fields, $reqbind_types, $p_key, $req_status);
$req_fields['usrID'] = $usrID;
//print_r($json_data);print_r($req_fields);
$res = new stdClass();
$details_added = [];

$reqconn_res = json_decode(dbConn($req_sql, $req_fields, 'insert'));
if($reqconn_res->status === 1){
	//echo mutateOrderDetails($req_det_fields,$req_detbind_types,$p_key,$req_details_sql);
	foreach($req_det_fields as $outerV){
		array_unshift($outerV, $req_detbind_types);
		$outerV['fkey'] = $p_key;
		$ord_detconn_res = json_decode(dbConn($req_details_sql, $outerV, 'insert'));
		if($ord_detconn_res->status === 1){
			dbConn($given_sql, array('i', $ord_detconn_res->insertID), 'insert');
			array_push($details_added, $ord_detconn_res->insertID);
		}
	}
}else{
	echo $reqconn_res;
}

if(!empty($details_added)){
	updateActivityLog('Insert Requisition', 'Requisition #'. $p_key.' added successfully', $usrID);
	$res->status = 1;
	$res->message = "Updated Successfully, please wait...";
	echo json_encode($res);
}

function createFields($genarr, $temparr){
	$fieldsarr = [];
	foreach($genarr as $k => $v){
		if(in_array($k, $temparr)){
			$fieldsarr[$k] = $v;
		}
	}
	return $fieldsarr;
}

?>