<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/functions/mutateOrderDetails.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_data_post = file_get_contents('php://input');
$json_data = json_decode($json_data_post, true);

$clean_data = array_map('funcSanitise', $json_data);
//print_r($clean_data);

$reqNo_pk = $clean_data['reqNo'];
$reqType = $clean_data['reqType'];
$reqDetails = $clean_data['addedLines'];

if($reqType === 'intEats' || $reqType === 'intKitchen' || $reqType === 'extDrinks'){
$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, PurchaseAmount, Qty, Rate, StandardCost,PurchaseNo) VALUES (?,?,?,?,?,?)";
$req_detbind_types = "sdidds";
}else{
	$req_details_sql = "INSERT INTO PurchaseDetails (ProductNo, PurchaseAmount, Qty,PurchaseNo) VALUES (?,?,?,?)";
	$req_detbind_types = "sdis";
/*array_unshift($reqDetails, $req_detbind_types);
print_r($reqDetails);*/
}

echo mutateReqDetails($reqDetails, $req_detbind_types, $reqNo_pk, $req_details_sql);

//The function that adds the necessary "things" to the arrays of the order details, necessary for sql prepare and bind
//The function then calls the insertion function (dbConn) to insert each array i.e order detail
//It then returns the result of the insertion (success or failure)
function mutateReqDetails($fieldsarr, $bindtypes, $pk, $sql){
	foreach($fieldsarr as $outerV){
		array_unshift($outerV, $bindtypes);
		$outerV['fkey'] = $pk;
		$ord_detconn_res = dbConn($sql, $outerV, "insert");
		if(json_decode($ord_detconn_res)->status == 1){
			dbConn("INSERT INTO InternalRequisition_Given (DetailsNo) VALUES (?)", array('i', json_decode($ord_detconn_res)->insertID), "insert");
		}
	}
	return $ord_detconn_res;
}
?>
