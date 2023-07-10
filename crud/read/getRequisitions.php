<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "SELECT * FROM PurchaseOrdersExtended WHERE Station = '$station' AND Status <> 'Closed'";
}else{
	//$sql = "SELECT * FROM PurchaseOrdersExtended WHERE Status <> 'Closed'";
	$sql = "SELECT reqNo, _date, category, station, `status`, staff, `type`, amount, ifnull(givenstatus,1) isFullyGiven FROM nest_restaurant.purchaseordersextended 
				left join (select purchaseno, givenstatus from internalrequisition_given_ext left join purchaseorder on internalrequisition_given_ext.purchaseno = purchaseorder.Purchase_No where givenstatus = 0 and RequisitionType = 'Internal') as given on reqno = given.purchaseno
			where `status` <> 'Closed' GROUP BY reqNo";
}

$res_conn = json_decode(dbConn($sql, array(), 'select'));
if($res_conn->status === 1){
	foreach($res_conn->message as $v){
		$v->amount = number_format($v->amount);
		//$v->payment = number_format($v->payment);
		if(date("d/m/Y", strtotime($v->_date)) != date('d/m/Y')){
			$v->_date = date("d/m/Y", strtotime($v->_date));
		}else{
			$v->_date = date("h:i A", strtotime($v->_date));
		}
	}
	$res_records->status = $res_conn->status;
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}else{
	$res_records->status = $res_conn->status;
	$res_records->message = $res_conn->message;
	echo json_encode($res_records);
}
?>
