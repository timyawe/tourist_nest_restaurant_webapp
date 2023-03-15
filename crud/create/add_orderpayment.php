<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$pymt_sql = "INSERT INTO Payments (PaymentDate, Amount, PaymentMethod) VALUES (?,?,?)";
$pymtIn_sql = "INSERT INTO PaymentsIn (Source, PaymentID) VALUES (?,?)";
$ordpymt_sql = "INSERT INTO OrderPayments (PaymentsInID, OrderID) VALUES (?,?)";

$bind_types = "sds";
$pymtIn_bindtypes = "si";
$ordpymt_bindtypes = "is";
$pymt_src = "Order";

foreach($clean_data as $k => $v){
	/*if($k == "pdate"){
		$d = date("Y-m-d", strtotime($v));
		$clean_data['pdate'] = $d;
	}*/
	if($k == "ordNo"){
		$ordNo = $v;
		unset($clean_data['ordNo']);
	}
	
	if($k == "userID"){
		$userID = $v;
		unset($clean_data['userID']);
	}
}
array_unshift($clean_data, $bind_types);

$pymtconn_res = new stdClass();
/*$pymtIn_res = new stdClass();*/

$pymtconn_dbAccess = json_decode(dbConn($pymt_sql, $clean_data, 'insert'));
if($pymtconn_dbAccess->status === 1){
	$pymtID = $pymtconn_dbAccess->insertID;
	$pymtIn_data = array($pymtIn_bindtypes, $pymt_src, $pymtID);
	
	$pymtInconn_dbAccess = json_decode(dbConn($pymtIn_sql, $pymtIn_data, 'insert'));
	if($pymtInconn_dbAccess->status === 1){
		$pymtInID = $pymtInconn_dbAccess->insertID;
		if(json_decode(dbConn($ordpymt_sql, array($ordpymt_bindtypes, $pymtInID, $ordNo), 'insert'))->status == 1){
			updateActivityLog('Insert Payment', 'Payment #'. $pymtID. ' for order #' .$ordNo . ' added successfully', $userID);
			$pymtconn_res->status = 1;
			$pymtconn_res->message = "Added Successfully, please wait...";
			echo json_encode($pymtconn_res);
		}			
	}
}
?>