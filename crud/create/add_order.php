<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';


$json_data_post = file_get_contents('php://input');

//Order form submitts order details in array format with objects 
//The objects are turned into assoc arrays by the json_decode with the true value as below
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);

$ord_tmplt = array("station", "to", "delv_point");
$ord_fields = createFields($json_data, $ord_tmplt);
$ord_det_fields = $clean_data['details']; //The array is accessed and saved in variable

$ord_sql = "INSERT INTO Orders (Order_No, OrderStatus, Station, `To`, DeliveryPoint, UserID) VALUES (?,?,?,?,?,?)";
$ord_details_sql = "INSERT INTO OrderDetails (ProductNo, Qty, Rate, Cost, OrderNo) VALUES (?,?,?,?,?)";

$ord_status = "Pending";
$usrID = $clean_data['userID'];
$ordbind_types = "sssssi";
$ord_detbind_types = "sidds";

$ordconn_res = new stdClass();
$orddet_res = new stdClass();

$json_res = new stdClass();

$p_key = genPK('Orders', 'Order_No', 'ORD');
array_unshift($ord_fields, $ordbind_types, $p_key, $ord_status);
$ord_fields['usrID'] = $usrID;

$ordconn_res = json_decode(dbConn($ord_sql, $ord_fields, "insert"));
if($ordconn_res->status === 1){
	$orddet_res = json_decode(mutateOrderDetails($ord_det_fields,$ord_detbind_types,$p_key,$ord_details_sql));
	
	$json_res->ordNo = $p_key;
	$json_res->status = $orddet_res->status;
	$json_res->message = $orddet_res->message;
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