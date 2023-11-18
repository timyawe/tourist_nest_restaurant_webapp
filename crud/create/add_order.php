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

$ord_tmplt = array("station", "reciepient", "to", "delv_point","special_price");
$ord_fields = createFields($json_data, $ord_tmplt);
$ord_det_fields = $clean_data['details']; //The array is accessed and saved in variable

if(in_array('specialprice',$ord_fields)){
	if($ord_fields['to'] != 'Go'){
		$ord_sql = "INSERT INTO Orders (Order_No, OrderStatus, Station, Reciepient,`To`, DeliveryPoint, hasSpecialPrice, UserID) VALUES (?,?,?,?,?,?,?,?)";
		$ordbind_types = "ssssssii";
	}else{
		$ord_sql = "INSERT INTO Orders (Order_No, OrderStatus, Station, Reciepient,`To`, hasSpecialPrice, UserID) VALUES (?,?,?,?,?,?,?)";
		$ordbind_types = "sssssii";
	}
}else{
	if($ord_fields['to'] != 'Go'){//print_r($ord_fields);
		$ord_sql = "INSERT INTO Orders (Order_No, OrderStatus, Station, Reciepient,`To`, DeliveryPoint, UserID) VALUES (?,?,?,?,?,?,?)";
		$ordbind_types = "ssssssi";
	}else{
		$ord_sql = "INSERT INTO Orders (Order_No, OrderStatus, Station, Reciepient,`To`, UserID) VALUES (?,?,?,?,?,?)";
		$ordbind_types = "sssssi";
	}
}
$ord_details_sql = "INSERT INTO OrderDetails (ProductNo, MainProduct_No, PurchaseRate, Qty, Rate, Cost, OrderNo) VALUES (?,?,?,?,?,?,?)";
$ord_detbind_types = "ssdidds";

$sp_price_ord_details_sql = "INSERT INTO OrderDetails (ProductNo, MainProduct_No, PurchaseRate, Qty, Rate, Cost, SpecialPrice, OrderNo) VALUES (?,?,?,?,?,?,?,?)";
$sp_price_ord_detbind_types = "ssdiddds";

$ord_status = "Pending";
$usrID = $clean_data['userID'];

/*$ordconn_res = new stdClass();
$orddet_res = new stdClass();*/

$json_res = new stdClass();

$p_key = genPK('Orders', 'Order_No', 'ORD', 'OrderDate');
array_unshift($ord_fields, $ordbind_types, $p_key, $ord_status);
$ord_fields['usrID'] = $usrID;

$ordconn_res = json_decode(dbConn($ord_sql, $ord_fields, "insert"));
if($ordconn_res->status === 1){
	foreach($ord_det_fields as $V){
		if(array_key_exists('SpecialPrice',$V)){
			$new_ord_dets = []; 
			array_push($new_ord_dets, $V);
			//$orddet_res = json_decode(mutateOrderDetails($ord_det_fields,$ord_detbind_types,$p_key,$ord_details_sql));
			$orddet_res = json_decode(mutateOrderDetails($new_ord_dets,$sp_price_ord_detbind_types,$p_key,$sp_price_ord_details_sql));
		}else{
			$new_ord_dets = []; 
			array_push($new_ord_dets, $V);
			$orddet_res = json_decode(mutateOrderDetails($new_ord_dets,$ord_detbind_types,$p_key,$ord_details_sql));
		}
	}
	updateActivityLog('Insert Order', 'Order #'.$p_key.' added successfully', $usrID);
	$json_res->ordNo = $p_key;
	$json_res->status = $orddet_res->status;
	$json_res->message = "Added Succesfully, please wait...";//$orddet_res->message;
	echo json_encode($json_res);
	
}else{
	echo $ordconn_res;
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
