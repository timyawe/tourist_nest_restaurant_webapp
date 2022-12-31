<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);

$ordNo_pk = $clean_data['ordNo'];
$ordDetails = $clean_data['addedLines'];

$ord_details_sql = "INSERT INTO OrderDetails (ProductNo, Qty, Rate, Cost, OrderNo) VALUES (?,?,?,?,?)";
$ord_detbind_types = "sidds";

echo mutateOrderDetails($ordDetails, $ord_detbind_types, $ordNo_pk, $ord_details_sql);
?>