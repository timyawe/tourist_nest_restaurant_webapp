<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/funcSanitise.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

$json_post_file = file_get_contents('php://input');
$json_data = json_decode($json_post_file, true);

$clean_data = array_map('funcSanitise', $json_data);
$pdtID = $clean_data['pdtID'];

$pdt_sql = "select Product_No, Description, Category, Status, UnitCostPrice, UnitQty, reception,restaurant, bar,UnitSalePrice, Salename, MeasureSold, PrepTime from products 
			left join (select unitcostprice, unitqty, reception,restaurant, bar, productno from itemsboughtextended) as ItemsBought on Product_No = ItemsBought.ProductNo
			left join (select UnitSalePrice, Salename, MeasureSold, PrepTime, ProductNo from items_sold ) as ItemsSold on Product_No = ItemsSold.ProductNo 
			where Product_No = '$pdtID'";
echo dbConn($pdt_sql, array(), 'select');
/*Select record, if present, from the three tables associated with product using pdtID */
/*$pdt_sql = "SELECT * FROM Products WHERE Product_No = '$pdtID'";
$purch_sql = "SELECT UnitCostPrice, UnitQty, StockLevel FROM Items_Bought WHERE ProductNo = '$pdtID'";
$sales_sql = "SELECT UnitSalePrice, SaleName, MeasureSold, PrepTime FROM Items_Sold WHERE ProductNo = '$pdtID'";


$pdt_res = json_decode(dbConn($pdt_sql, array(), 'select'))->message[0];
$p_res = json_decode(dbConn($purch_sql, array(), 'select'));
if($p_res->status === 1){$purch_res = $p_res->message[0];}
$s_res = json_decode(dbConn($sales_sql, array(), 'select'));
if($s_res->status === 1){$sales_res = $s_res->message[0];}

$pdt_res_arr = json_decode(json_encode($pdt_res),true);
if(isset($purch_res)){
	$purch_res_arr = json_decode(json_encode($purch_res),true);
}else{
	$purch_res_arr = [];
}
if(isset($sales_res)){
	$sales_res_arr = json_decode(json_encode($sales_res),true);
}else{
	$sales_res_arr = [];
}

$json_res = new stdClass();
$json_res->status = 1;
$json_res->message = array_merge($pdt_res_arr,$purch_res_arr,$sales_res_arr);

echo json_encode($json_res);*/

?>