<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);

$reqNo_pk = $clean_data['reqNo'];
$reqType = $clean_data['reqType'];
$reqDetails = $clean_data['addedLines'];

if($reqType === 'intEats' || $reqType === 'extDrinks'){
$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, PurchaseAmount, Qty, Rate, StandardCost,PurchaseNo) VALUES (?,?,?,?,?,?)";
$req_detbind_types = "sdidds";
}else{
	$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, PurchaseAmount, Qty,PurchaseNo) VALUES (?,?,?,?)";
	$req_detbind_types = "sdis";
/*array_unshift($reqDetails, $req_detbind_types);
print_r($reqDetails);*/
}

echo mutateOrderDetails($reqDetails, $req_detbind_types, $reqNo_pk, $req_details_sql);
?>
