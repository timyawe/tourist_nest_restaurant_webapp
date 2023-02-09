<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$reqID = $json_data['reqNo']; //$json_data['detailsID'];

$delete_sql = "DELETE FROM PurchaseOrder WHERE Purchase_No = '$reqID' LIMIT 1";

echo dbConn($delete_sql, array(), 'delete');
?>