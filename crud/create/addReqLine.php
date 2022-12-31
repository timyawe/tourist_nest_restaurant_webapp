<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);

$reqNo_pk = $clean_data['reqNo'];
$reqDetails = $clean_data['addedLines'];

$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, Qty, Rate, StandardCost, PurchaseNo) VALUES (?,?,?,?,?)";
$req_detbind_types = "sidds";

echo mutateOrderDetails($reqDetails, $req_detbind_types, $reqNo_pk, $req_details_sql);
?>