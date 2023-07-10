<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$offerID = $_GET['offerID'];

$off_sql = "SELECT RecipientCategory, Station FROM Offers WHERE ID=$offerID";
$off_details_sql = "SELECT * FROM OffersDetails_Ext WHERE OffersID = $offerID";

$json_res = new stdClass();
$off_res = json_decode(dbConn($off_sql, array(), 'select'));

if($off_res->status == 1){
	$off_dets_res = json_decode(dbConn($off_details_sql, array(), 'select'));
	if($off_dets_res->status == 1){
		$json_res->status = 1;
		$json_res->extra = $off_res->message;
		foreach($off_dets_res->message as $v){
			$v->isDeleted =  (int)$v->isDeleted;
		}
		$json_res->extra_details = $off_dets_res->message;
		
		echo json_encode($json_res);
	}
}

?>
