<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);
$clean_data = array_map('funcSanitise', $json_data);

$del_fail = [];//print_r($clean_data['ordNo']);
$deleted_total = 0;
foreach ($clean_data['deletedLines'] as $k => $v){
	$detailsID = $v['detailsID'];
	//print_r( $detailsID);
	if(json_decode(dbConn("DELETE FROM OrderDetails WHERE Details_No = '$detailsID' LIMIT 1", array(), 'delete'))->status != 1){
		array_push($del_fail, $detailsID);
	}else{
		if($v['paidStatus'] == 1){
			$deleted_total += $v['total'];
		}
	}
}

if($deleted_total > 0){
	$ordNo = $clean_data['ordNo'];
	$paymntID = json_decode(dbConn("SELECT paymtID FROM OrderPaymentsExtended WHERE OrderNo = $ordNo AND amount >= $deleted_total", array(), 'select'))->message;
	dbConn("UPDATE Payments SET Amount = Amount - ? WHERE Payment_ID = $paymntID", array('i', $deleted_total), 'update');
}

$json_res = new stdClass();
if(empty($del_fail)){
	$json_res->status = 1;
	$json_res->message = "Deleted Successfully";
	echo json_encode($json_res);
}else{
	$json_res->status = 1;
	$json_res->message = "One or more items was not deleted";
	echo json_encode($json_res);
}
?>
