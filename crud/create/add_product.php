<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

/* >>A product can belong to two or three tables depending on wether its only sold and not bought.<< */

//Here three arrays are created to hold the expected key values for each of the tables associated with a product. */ 
$pdt_template = array('description','category','status');
$itms_bought_templ = array('unitcostprice','unitqty','stocklevel');
$itms_sld_templ = array('unitsaleprice','salename','measuresold', 'preptime');
/*$pdt_fields = [];
$itms_bought_fields = [];
$itms_sld_fields = [];*/

//Here the the function that places the values of each table in the respective array template created above is called
$pdt_fields = createFields($clean_data, $pdt_template/*, $pdt_fields*/);
$itms_bought_fields = createFields($clean_data, $itms_bought_templ/*, $itms_bought_fields*/);
$itms_sld_fields = createFields($clean_data, $itms_sld_templ/*, $itms_sld_fields*/);

/*print_r($pdt_fields);
print_r($itms_bought_fields);
print_r($itms_sld_fields);*/

$pdt_sql = "INSERT INTO Products (Product_No, Description, Category, Status) VALUES (?,?,?,?)";
$purch_sql = "INSERT INTO Items_Bought (UnitCostPrice, StockLevel, UnitQty, ProductNo) VALUES (?,?,?,?)";
$sales_sql = "INSERT INTO Items_Sold (SaleName, UnitSalePrice, MeasureSold, PrepTime, ProductNo) VALUES (?,?,?,?,?)";
$p_key = genPK('Products', 'Product_No', 'PDT');//"PDT008";
$pdtbind_types = "ssss";
$purchbind_types = "diss";
$salesbind_types = "sddis";

array_unshift($pdt_fields,$pdtbind_types, $p_key);
array_unshift($itms_bought_fields, $purchbind_types/*, $auto_pkey*/);
array_unshift($itms_sld_fields, $salesbind_types);
$itms_bought_fields["pkey"] = $p_key;//array_push($itms_bought_fields, $p_key);
$itms_sld_fields["pkey"] = $p_key;//array_push($itms_sld_fields, $p_key);

/*print_r($pdt_fields);
print_r($itms_bought_fields);
print_r($itms_sld_fields);*/

if(!array_key_exists("item_sold_check", $clean_data)){
	//$pdtconn_result = new stdClass();
	//$purchconn_res = new stdClass();
	
	$pdtconn_result = json_decode(dbConn($pdt_sql,$pdt_fields, "insert"));
	if($pdtconn_result->status !== 0){
		$purchconn_res = json_decode(dbConn($purch_sql,$itms_bought_fields, "insert"));
		if($purchconn_res->status !== 0){
			echo dbConn($sales_sql,$itms_sld_fields, "insert");
		}else{
			echo $purchconn_res;
		}
	}else{
		echo $pdtconn_result;//check it out
	}
}else{
	$pdtconn_result = json_decode(dbConn($pdt_sql,$pdt_fields, "insert"));
	if($pdtconn_result->status !== 0){
		echo dbConn($sales_sql,$itms_sld_fields, "insert");
	}else{
		echo $pdtconn_result;
	}
}

function createFields($genarr, $temparr/*, $fieldsarr*/){
	$fieldsarr = [];
	foreach($genarr as $k => $v){
		if(/*!$v == "" && */in_array($k, $temparr)){
			$fieldsarr[$k] = $v;
		}
	}
	return $fieldsarr;
}
?>
