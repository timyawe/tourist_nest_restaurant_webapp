<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

//$detailsID = $json_data[0]['detailsID']; echo $detailsID;
$del_fail = [];
foreach ($json_data as $k => $v){
	$detailsID = $v['detailsID'];
	//print_r( $detailsID);
	if(json_decode(dbConn("DELETE FROM PurchaseDetails WHERE Details_No = '$detailsID' LIMIT 1", array(), 'delete'))->status != 1){
		array_push($del_fail, $detailsID);
	}
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
//$delete_sql = "DELETE FROM PurchaseDetails WHERE Details_No = '$detailsID' LIMIT 1";

//echo dbConn($delete_sql, array(), 'delete');
?>