<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);

$ordNo_pk = $clean_data['ordNo'];
$ordDetails = $clean_data['addedLines'];

$ord_details_sql = "INSERT INTO OrderDetails (ProductNo, MainProduct_No, PurchaseRate, Qty, Rate, Cost, OrderNo) VALUES (?,?,?,?,?,?,?)";
$ord_detbind_types = "ssdidds";

//echo mutateOrderDetails($ordDetails, $ord_detbind_types, $ordNo_pk, $ord_details_sql);
$ordDetsConnRes = json_decode(mutateOrderDetails($ordDetails, $ord_detbind_types, $ordNo_pk, $ord_details_sql));

$json_res = new stdClass();
if($ordDetsConnRes->status == 1){
	dbConn("UPDATE Orders SET OrderStatus = ? WHERE Order_No = '$ordNo_pk'", array('s','Pending'), 'update');
	$json_res->status = $ordDetsConnRes->status;
	$json_res->message = $ordDetsConnRes->message;
	echo json_encode($json_res);
}else{
	echo $ordDetsConnRes;
}
	
?>
