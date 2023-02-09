<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$ordID = $json_data['ordNo'];

/* Check if Order has payments */
$ordpaymts_json = json_decode(dbConn("SELECT paymtID FROM OrderPaymentsExtended WHERE OrderNo = '$ordID'", array(), 'select'));

$delete_sql = "DELETE FROM Orders WHERE Order_No = '$ordID' LIMIT 1";

$del_fail = []; 
if(json_decode(dbConn($delete_sql, array(), 'delete'))->status == 1){
	if($ordpaymts_json->status == 1){
		//$ordpaymts = json_decode($ordpaymts_json->message, true);
		foreach($ordpaymts_json->message as $v){
			$ID = $v->paymtID;
			if(json_decode(dbConn("DELETE FROM Payments WHERE Payment_ID = '$ID' LIMIT 1", array(), 'delete'))->status != 1){
				array_push($del_fail, $v);
			}
		}
	}
}

$json_res = new stdClass();

if(empty($del_fail)){
	$json_res->status = 1;
	$json_res->message = "Deleted Sucessfully";
	echo json_encode($json_res);
}
?>