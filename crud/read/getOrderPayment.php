<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);
$pymtID = $clean_data['pymtID'];
$ordpymt_sql = "SELECT amount, method, `date`, paymtID, OrderNo FROM OrderPaymentsExtended WHERE paymtID = '$pymtID'";

echo dbConn($ordpymt_sql, array(), "select")
/*
$res = json_decode(dbConn($ordpymt_sql, array(), "select"));
if($res->status == 1){
	$res_arr = json_decode(json_encode($res->message[0]),true);
	foreach($res_arr as $k => $v){
		if($k == 'date'){
			$res_arr['date'] = date("d/m/Y", strtotime($v));
		}
	}
	print_r($res_arr);
}*/
?>