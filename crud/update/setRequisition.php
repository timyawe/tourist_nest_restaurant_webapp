<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/comparisonRecord.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

//$ordNo = $clean_data['ordNo'];
$res = new stdClass();
$reqType = $clean_data['reqType'];
$userID = $clean_data['userID'];
print_r($clean_data['reqDetails']);
foreach($clean_data['reqDetails']  as $k => $v){
	if($v == null){
		unset($clean_data['reqDetails'][$k]);
	}
}
//print_r($clean_data['reqDetails']);echo $userID;
$edited = [];
$not_edited = [];
if($reqType == "External"){
	foreach($clean_data['reqDetails']  as $k => $v){
		$edited_row = array_diff($v, comparisonRecord('PurchaseDetails', 'Details_No', $v['Details_No']));print_r($edited_row);
		/*if(!empty($edited_row)){
			$fields_arr = array_keys($edited_row);
			array_unshift($edited_row, createBindTypes($edited_row));
			$dbAccess = json_decode(dbConn(createUpdateSql($fields_arr, 'PurchaseDetails', 'Details_No', $v['Details_No']), $edited_row, 'update'));
			if($dbAccess->status == 1){
				array_push($edited, $v['Details_No']);
				//print_r($fields_arr);
				//print_r($edited_row);
			}else{
				array_push($not_edited, $v['Details_No']);
			}
		}else{
			array_push($not_edited, $v['Details_No']);
		}*/
	}
}

if(count($edited) == $clean_data['reqDetails']){
	$res->status = 1;
	$res->message = "Updated Successfully, please wait...";
}else{
	$res->status = 0;
	$res->message = "Some or all items were not edited, try again";
}
//echo json_encode($res);
/*if(!empty($final_arr)){
	$fields_arr = array_keys($final_arr);
	array_unshift($final_arr, createBindTypes($final_arr));
	$dbAccess = json_decode(dbConn(createUpdateSql($fields_arr, 'Orders', 'Order_No', $ordNo), $final_arr, 'update'));
	if($dbAccess->status = 1){
		//updateActivityLog('Update Order', 'Order '.$ordNo .' updated to Delivered successfully', $clean_data['userID']);
		
		$res->status = 1;
		$res->message = $dbAccess->message;
		echo json_encode($res);
	}else{
		$res->status = $dbAccess->status;
		$res->message = $dbAccess->message;
		echo json_encode($res);
	}
}*/

?>
