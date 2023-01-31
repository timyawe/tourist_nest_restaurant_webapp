<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$given_fail = [];//holds item lines which have failed to update

//print_r($clean_data);

$reqNo = $clean_data['reqNo'];
$status = $clean_data['PurchaseStatus'];
$curr_status = json_decode(dbConn("SELECT PurchaseStatus FROM PurchaseOrder WHERE Purchase_No = '$reqNo'", array(), 'select'))->message[0]->PurchaseStatus;

//if($status != ''){
	if($status != $curr_status ){
		dbConn("UPDATE PurchaseOrder SET PurchaseStatus = ? WHERE Purchase_No = '$reqNo'", array('s',$status), 'update');//echo $curr_status;
	}
//}

if(isset($clean_data['given_items'])){//print_r($clean_data['given_items']);
	foreach($clean_data['given_items'] as $v){
		$detNo = array_shift($v);
		$fields_arr = array_keys($v);
		array_unshift($v, createBindTypes($v));//print_r( $v);
		$res = json_decode(dbConn(createUpdateSql($fields_arr, 'internalrequisition_given', 'DetailsNo', $detNo), $v, 'update'));
		
		if($res->status === 0){
			array_push($given_fail, $detNo); 
		}
	}
}

$json_res = new stdClass();

if(!empty($given_fail)){
	$json_res->status = 0;
	$json_res->message = "Some of the Items failed to update, reload and try again";
	echo json_encode($json_res);
}else{
	$json_res->status = 1;
	$json_res->message = "Updated Succesfully";
	echo json_encode($json_res);
}


?>