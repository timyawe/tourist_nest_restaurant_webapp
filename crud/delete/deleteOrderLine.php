<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data = json_decode($_GET['deletedLines'], true);

$detailsID = $json_data['detailsID'];

$delete_sql = "DELETE FROM OrderDetails WHERE Details_No = $detailsID LIMIT 1";

echo dbConn($delete_sql, array(), 'delete');
?>