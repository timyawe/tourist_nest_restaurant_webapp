<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/updateActivityLog.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$offDetID = $json_data['offerDetailsID'];
$isDeleted = $json_data['isDeleted'];
$userID = $json_data['userID'];
//echo $offDetID . $isDeleted;

$res = new stdClass();
if(json_decode(dbConn("UPDATE OffersDetails SET isDeleted = ? WHERE ID = $offDetID LIMIT 1", array('i', $isDeleted), 'update'))->status == 1){
	//updateActivityLog('Update OffersDetails', 'OfferDetails '.$offDetID .' marked as deleted', $userID);
	$res->status = 1;
	echo json_encode($res);
}

?>
