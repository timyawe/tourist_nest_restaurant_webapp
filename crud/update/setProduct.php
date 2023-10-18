<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createBindTypes.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/createUpdateSql.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/comparisonRecord.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');//recieves JSON type data
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

$pdtID = $clean_data['pdtID'];

$pdt_template = array("description","category","status");
$itms_bought_templ = array("unitcostprice","unitqty","stocklevel");
$itms_sld_templ = array("unitsaleprice","salename","measuresold");

//Here the function that places the values of each table in the respective array template created above is called
$pdt_fields = createFields($clean_data, $pdt_template);
$itms_bought_fields = createFields($clean_data, $itms_bought_templ);
$itms_sld_fields = createFields($clean_data, $itms_sld_templ);

if($clean_data['stocklevel_edit'] == 1){
	$stocklevel_fields = createFields($clean_data, array('reception', 'restaurant', 'bar'));
	$itemsBoughtID = json_decode(dbConn("SELECT Items_bought.ID FROM items_bought join products on Product_No = ProductNo WHERE Product_No = '$pdtID'", array(), 'select'))->message[0]->ID;
	//print_r( $stocklevel_fields);
	$stocklevelfinal_arr = array_diff_assoc($stocklevel_fields, comparisonRecord('Stocklevels', 'ItemsBoughtID', $itemsBoughtID));//array_diff_assoc compares both key and value
	//print_r( $stocklevelfinal_arr);
	if(!empty($stocklevelfinal_arr)){
		$stocklevel_fields_arr = array_keys($stocklevel_fields);
		array_unshift($stocklevelfinal_arr, createBindTypes($stocklevelfinal_arr));
		if(json_decode(dbConn(createUpdateSql($stocklevel_fields_arr, 'Stocklevels', 'ItemsBoughtID', $itemsBoughtID),$stocklevelfinal_arr, 'update'))->status == 1){
			$isStockLevelEdited = 1;
		}else{
			$isStockLevelEdited = 0;
		}
	}else{
		$isStockLevelEdited = 0;
	}
}

if(!empty($pdt_fields)){
	$pdtfinal_arr = array_diff($pdt_fields, comparisonRecord('Products', 'Product_No', $pdtID));
	
	if(!empty($pdtfinal_arr)){
		$pdtfields_arr = array_keys($pdtfinal_arr);
		
		array_unshift($pdtfinal_arr, createBindTypes($pdtfinal_arr));
		
		$pdt_res = json_decode(dbConn(createUpdateSql($pdtfields_arr, 'Products', 'Product_No', $pdtID),$pdtfinal_arr, 'update'));
		
		if($pdt_res->status === 1){
			$isPdtEdited = 1;
			$pdtEdtMsg = $pdt_res->message;
		}else{
			$isPdtEdited = 0;
			$pdtEdtMsg = $pdt_res->message;
		}
	}else{
		$isPdtEdited = 2;
		$pdtEdtMsg = "No fields were edited";
	}
	
}
if(!empty($itms_bought_fields)){
	$pdt_bght_final_arr = array_diff($itms_bought_fields, comparisonRecord('Items_Bought', 'ProductNo', $pdtID));
	
	if(!empty($pdt_bght_final_arr)){
		$pdt_bght_fields_arr = array_keys($pdt_bght_final_arr);
		
		array_unshift($pdt_bght_final_arr, createBindTypes($pdt_bght_final_arr));
		
		$pdt_bght_res = json_decode(dbConn(createUpdateSql($pdt_bght_fields_arr, 'Items_Bought', 'ProductNo', $pdtID),$pdt_bght_final_arr, 'update'));
		
		if($pdt_bght_res->status === 1){
			$isPdtBghtEdited = 1;
			$pdtBghtEdtMsg = $pdt_bght_res->message;
		}else{
			$isPdtBghtEdited = 0;
			$pdtBghtEdtMsg = $pdt_bght_res->message;
		}
	}else{
		$isPdtBghtEdited = 2;
		$pdtBghtEdtMsg = "No fields were edited";
	}
}
if(!empty($itms_sld_fields)){
	$pdt_sld_final_arr = array_diff($itms_sld_fields, comparisonRecord('Items_Sold', 'ProductNo', $pdtID));
	//print_r($pdt_sld_final_arr);
	if(!empty($pdt_sld_final_arr)){
		$pdt_sld_fields_arr = array_keys($pdt_sld_final_arr);
		
		array_unshift($pdt_sld_final_arr, createBindTypes($pdt_sld_final_arr));
		
		$pdt_sld_res = json_decode(dbConn(createUpdateSql($pdt_sld_fields_arr, 'Items_Sold', 'ProductNo', $pdtID),$pdt_sld_final_arr, 'update'));
		
		if($pdt_sld_res->status === 1){
			$isPdtSldEdited = 1;
			$pdtSldEdtMsg = $pdt_sld_res->message;
		}else{
			$isPdtSldEdited = 0;
			$pdtSldEdtMsg = $pdt_sld_res->message;
		}
	}else{
		$isPdtSldEdited = 2;
		$pdtSldEdtMsg = "No fields were edited";
	}
}

$json_res = new stdClass();
if(isset($isPdtEdited) && $isPdtEdited == 1 || isset($isPdtBghtEdited) && $isPdtBghtEdited == 1 || isset($isPdtSldEdited) && $isPdtSldEdited == 1 || isset($isStockLevelEdited) && $isStockLevelEdited == 1){
	$json_res->suc_status = 1;
	$json_res->suc_message = "Updated Succesfully";
}else if(isset($isPdtEdited) && $isPdtEdited == 2 || isset($isPdtBghtEdited) && $isPdtBghtEdited == 2 || isset($isPdtSldEdited) && $isPdtSldEdited == 2){
	$json_res->info_status = 2;
	$json_res->info_message = "No fields were edited";
}else{
	$json_res->err_status = 0;
	$json_res->err_message = "One or more fields was not updated $isStockLevelEdited";
	$json_res->pdtmsg = $pdtEdtMsg;
	$json_res->pdtbmsg = $pdtBghtEdtMsg;
	$json_res->pdtsmsg = $pdtSldEdtMsg;
}

echo /*$isStockLevelEdited;*/json_encode($json_res);

function createFields($genarr, $temparr){
	$fieldsarr = [];
	foreach($genarr as $k => $v){
		if(in_array($k, $temparr)){
			$fieldsarr[$k] = $v;
		}
	}
	return $fieldsarr;
}

?>
