<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$res_records = new stdClass();

$amnt = /*array_map('funcSanitise', */$_GET['pamnt'];//);
$ordNo = $_GET['ordNo'];

$ordpymt_sql = "SELECT TotalPaid FROM OrderPayments_grouped WHERE OrderNo = '$ordNo'";
$ordbill_sql = "SELECT Bill FROM OrderBill_grouped WHERE OrderNo = '$ordNo'";

$res_records_bill = json_decode(dbConn($ordbill_sql, array(), "select"))->message[0]->Bill;
$res_record_pymt = json_decode(dbConn($ordpymt_sql, array(), "select"))->message[0]->TotalPaid;
$order_bal = $res_records_bill - $res_record_pymt;
//print_r($res_records_bill);print_r($res_record_pymt);
if($amnt > $order_bal){
	$res_records->status = 0;
	$res_records->message = "Amount entered is higher than order bill, change amount and try again. Current balance is ". number_format($order_bal);
}else{
	$res_records->status = 1;
}
echo json_encode($res_records);
?>