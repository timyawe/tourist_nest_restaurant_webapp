<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$reqNo = $clean_data['reqNo'];
$reqtype = $clean_data['type'];

$req_sql = "SELECT PurchaseStatus, Station, `Category`, RequisitionType FROM PurchaseOrder WHERE Purchase_No = '$reqNo'";
if($reqtype == "External"){
	$reqItems_sql = "SELECT qty,UnitQty, rate, total, item, RecievedStatus AS isChecked, QtyRecieved AS qty_recvd, PurchaseAmount, FinalAmount, DetailsNo FROM PurchaseDetailsExtended WHERE PurchaseNo = '$reqNo'";
}else{
	$reqItems_sql = "SELECT qty, item, GivenStatus AS isGiven, QtyGiven, RecievedStatus AS isRecieved, QtyRecieved AS qty_recvd, DetailsNo FROM internalrequisition_given_ext WHERE PurchaseNo = '$reqNo'";
}
$json_req = new stdClass();

$res_records = json_decode(dbConn($req_sql, array(), 'select'));

if($res_records->status === 1){
	$requisition = $res_records->message;
	$res_reqrec = json_decode(dbConn($reqItems_sql, array(), 'select'));
	if($res_reqrec->status === 1){
		$requisition_details = $res_reqrec->message;
		foreach($requisition_details as $v){
			if($v->qty < 1){
				$v->qty = number_format($v->qty, 2);
			}else{
				$v->qty = number_format($v->qty, 0);
			}
		}
		$json_req->status = 1;
		$json_req->requisition = $requisition;
		$json_req->req_details = $requisition_details;
		
		echo json_encode($json_req);
	}
}
?>
