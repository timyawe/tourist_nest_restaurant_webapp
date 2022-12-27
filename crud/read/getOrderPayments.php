<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);
$ordNo = $clean_data['ordNo'];
$ordpymts_recs_sql = "SELECT amount, method, `date`, paymtID FROM OrderPaymentsExtended WHERE OrderNo = '$ordNo'";
$ordpymt_sql = "SELECT TotalPaid FROM OrderPayments_grouped WHERE OrderNo = '$ordNo'";
$ordbill_sql = "SELECT Bill FROM OrderBill_grouped WHERE OrderNo = '$ordNo'";

$json_ordpymt = new stdClass();

$res_records_bill = json_decode(dbConn($ordbill_sql, array(), "select"));
$res_record_pymt = json_decode(dbConn($ordpymt_sql, array(), "select"));

if($res_records_bill->status === 1){
	$orderbill = $res_records_bill->message;
	$totalpaid = $res_record_pymt->message;
	$res_ordpymtrec = json_decode(dbConn($ordpymts_recs_sql, array(), "select"));
	if($res_ordpymtrec->status === 1){
		$order_payments = $res_ordpymtrec->message;
		$json_ordpymt->status = 1;
		$json_ordpymt->orderbill = $orderbill;
		$json_ordpymt->totalpaid = $totalpaid;
		$json_ordpymt->ord_paymts = $order_payments;
		
		echo json_encode(/*$res_records->message*/$json_ordpymt);
	}else if($res_ordpymtrec->status === 2){
		$json_ordpymt->orderbill = $orderbill;
		$json_ordpymt->totalpaid = $totalpaid;
		echo json_encode($json_ordpymt);
	}
}
?>