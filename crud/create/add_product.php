<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/genPK.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);

foreach($clean_data as $k => $v){
	if($k == 'main_product' && $v == ''){//used to set empty value to null for database declaration of null instead of empty string
		$clean_data[$k] = null;
	}
}

/* >>A product can belong to two or three tables depending on wether its only sold and not bought.<< */

//Here three arrays are created to hold the expected key values for each of the tables associated with a product. */ 
$pdt_template = array('description','category','status','main_product');
$itms_bought_templ = array('unitcostprice','unitqty');
$itms_sld_templ = array('unitsaleprice','salename','measuresold', 'preptime');

//Here the the function that places the values of each table in the respective array template created above is called
$pdt_fields = createFields($clean_data, $pdt_template);
$itms_bought_fields = createFields($clean_data, $itms_bought_templ);
$itms_sld_fields = createFields($clean_data, $itms_sld_templ);

$pdt_sql = "INSERT INTO Products (Product_No, Description, Category, Status, Main_Product) VALUES (?,?,?,?,?)";
$purch_sql = "INSERT INTO Items_Bought (UnitCostPrice, UnitQty, ProductNo) VALUES (?,?,?)";
$sales_sql = "INSERT INTO Items_Sold (SaleName, UnitSalePrice, MeasureSold, PrepTime, ProductNo) VALUES (?,?,?,?,?)";
$p_key = genPK('Products', 'Product_No', 'PDT', 'ID');
$pdtbind_types = "sssss";
$purchbind_types = "dss";
$salesbind_types = "sddis";

array_unshift($pdt_fields,$pdtbind_types, $p_key);
array_unshift($itms_bought_fields, $purchbind_types);
array_unshift($itms_sld_fields, $salesbind_types);
$itms_bought_fields["pkey"] = $p_key;
$itms_sld_fields["pkey"] = $p_key;

if(!array_key_exists("item_sold_check", $clean_data) && !array_key_exists("item_bought_check", $clean_data)){//echo "Nice";
	//print_r($clean_data);
	$pdtconn_result = json_decode(dbConn($pdt_sql,$pdt_fields, 'insert'));
	if($pdtconn_result->status !== 0){
		$purchconn_res = json_decode(dbConn($purch_sql,$itms_bought_fields, 'insert'));
		if($purchconn_res->status !== 0){
			$items_bought_key = $purchconn_res->insertID;
			$stocklevel_rec = $clean_data['reception'];
			$stocklevel_res = $clean_data['restaurant'];
			$stocklevel_bar = $clean_data['bar'];
			$stocklevelconn_fields = array('iiii', $stocklevel_rec,$stocklevel_res,$stocklevel_bar, $items_bought_key);
			$stocklevels_sql = "INSERT INTO StockLevels (Reception, Restaurant, Bar, ItemsBoughtID) VALUES (?,?,?,?)";
			$stocklevelconn_res = json_decode(dbConn($stocklevels_sql, $stocklevelconn_fields, 'insert'));
			if($stocklevelconn_res->status !== 0){
				echo dbConn($sales_sql,$itms_sld_fields, 'insert');
			}
		}else{
			echo $purchconn_res;
		}
	}else{
		echo $pdtconn_result;//check it out
	}
}
if(array_key_exists("item_sold_check", $clean_data)){//print_r($clean_data);
	$pdtconn_result = json_decode(dbConn($pdt_sql,$pdt_fields, 'insert'));
	if($pdtconn_result->status !== 0){
		echo dbConn($sales_sql,$itms_sld_fields, 'insert');
	}else{
		echo $pdtconn_result;
	}//print_r($itms_sld_fields);
}

if(array_key_exists("item_bought_check", $clean_data)){//print_r($clean_data);
	$pdtconn_result = json_decode(dbConn($pdt_sql,$pdt_fields, 'insert'));
	if($pdtconn_result->status !== 0){
		$purchconn_res = json_decode(dbConn($purch_sql,$itms_bought_fields, 'insert'));
		if($purchconn_res->status !== 0){
			$items_bought_key = $purchconn_res->insertID;
			$stocklevel_rec = $clean_data['reception'];
			$stocklevel_res = $clean_data['restaurant'];
			$stocklevel_bar = $clean_data['bar'];
			$stocklevelconn_fields = array('iiii', $stocklevel_rec,$stocklevel_res,$stocklevel_bar, $items_bought_key);
			$stocklevels_sql = "INSERT INTO StockLevels (Reception, Restaurant, Bar, ItemsBoughtID) VALUES (?,?,?,?)";
			/*$stocklevelconn_res = json_decode(*/echo dbConn($stocklevels_sql, $stocklevelconn_fields, 'insert');/*);
			if($stocklevelconn_res->status !== 0){
				echo $stocklevelconn_res;// dbConn($sales_sql,$itms_sld_fields, 'insert');
			}*/
		}else{
			echo $pdtconn_result;
		}
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
