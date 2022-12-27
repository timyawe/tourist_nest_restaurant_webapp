<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';

$json_data_post = file_get_contents('php://input');

//Order form submitts order details in array format with objects 
//The objects are turned into assoc arrays by the json_decode with the true value as below
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
$req_tmplt = array("station", "category");
$req_fields = createFields($json_data, $req_tmplt);
$req_det_fields = $clean_data['details'];//The array is accessed and saved in variable

$req_sql = "INSERT INTO PurchaseOrder (Purchase_No, PurchaseStatus, Category, Station, UserID) VALUES (?,?,?,?,?)";
$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, Qty, Rate, StandardCost, PurchaseNo) VALUES (?,?,?,?,?)";
$p_key = genPK('PurchaseOrder', 'Purchase_No', 'REQ');//"REQ001";
$req_status = "Submitted";
$usrID = $clean_data['userID'];//1;

$reqbind_types = "ssssi";
$req_detbind_types = "sidds";

array_unshift($req_fields, $reqbind_types, $p_key, $req_status);
$req_fields['usrID'] = $usrID;
//print_r($ord_det_fields);

$reqconn_res = new stdClass();

$reqconn_res = json_decode(dbConn($req_sql, $req_fields));
if($reqconn_res->status === 1){
	echo mutateOrderDetails($req_det_fields,$req_detbind_types,$p_key,$req_details_sql);
}else{
	echo $reqconn_res;
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

function mutateOrderDetails($fieldsarr, $bindtypes, $pk, $sql){
	foreach($fieldsarr as $outerV){
		array_unshift($outerV, $bindtypes);
		$outerV['reqfkey'] = $pk;
		$ord_detconn_res = dbConn($sql, $outerV);
	}
	return $ord_detconn_res;
}
?>