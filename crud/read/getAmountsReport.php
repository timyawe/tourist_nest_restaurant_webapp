<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

//print_r($_GET['_data']);
$json_data = json_decode($_GET['_data'], true);
//print_r($json_data);
if($json_data['station'] != 'All'){
	//print_r($json_data['station']);
	$station = $json_data['station'];
}

if(isset($json_data['amounts_date'])){
	//print_r($json_data['station']);
	$_date = $json_data['amounts_date'];
}

$drinks_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty, format(sum(cost),0) as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 1 GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
$drinks_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty,'NYP' as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 0 GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
$drinks_PB_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty,'NYP' as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE PaidStatus = 1 AND PaidDate > orderdate + INTERVAL 19 HOUR AND category = 'Drinks' OR PaidStatus = 1 AND PaidDate > orderdate AND category = 'Drinks' GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
$drinks_reqreportsql = "SELECT details_no, `Description` AS itemBought, round(SUM(qtyRecieved)) AS QtyBought, format(if(isnull(sum(finalamount)),if(isnull(sum(purchaseamount)),sum(standardcost),sum(purchaseamount)),sum(finalamount)),0) AS AmountSpent FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Drinks' AND RequisitionType = 'External' GROUP BY ProductNo";
$eats_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ format(sum(cost),0) as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 1 GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
$eats_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ 'NYP' as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate >'$_date'  - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 0 GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
$eats_PB_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ 'NYP' as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE PaidStatus = 1 AND PaidDate > orderdate + INTERVAL 19 HOUR AND category = 'Eats' OR PaidStatus = 1 AND PaidDate > orderdate AND category = 'Eats' GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
$eats_reqreportsql = "SELECT details_no, `Description` AS itemBoughtEats, if(sum(qtyRecieved) > 1, concat(round(SUM(qtyRecieved)), ' ',  UnitQty, 'S'),concat(round(SUM(qtyRecieved)), ' ',  UnitQty)) AS QtyBoughtEats, format(sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))),0) AS AmountSpentEats FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo left join items_bought on purchasedetails.ProductNo = items_bought.ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Eats' OR Date(DateRecieved) = '$_date' AND products.Category = 'Kitchen' GROUP BY items_bought.ProductNo";

$sales_sum_sql = "select sum(cost) AS SalesTotal from orderdetails join orders on order_no = orderno where PaidStatus = 1 and OrderDate > '$_date' - interval 1 day + interval 19 hour and orderdate < '$_date' + interval 19 HOUR";
$reqs_sum_sql = "select sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))) AS PurchasesTotal FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo WHERE Date(DateRecieved) = '$_date'";
$mmpaymnt_sum_sql = "select sum(amount) AS M_MTotal from orderpaymentsextended where method <> 'Cash' and `date` = '$_date'";

$drinks_salesdbConn_res = json_decode(dbConn($drinks_salesreportsql, array(), 'select'));
$drinks_sales_nyp_dbConn_res = json_decode(dbConn($drinks_sales_nyp_reportsql, array(), 'select'));
$drinks_PB_dbConn_res = json_decode(dbConn($drinks_PB_reportsql, array(), 'select'));
$drinks_reqdbConn_res = json_decode(dbConn($drinks_reqreportsql, array(), 'select'));
$eats_salesdbConn_res = json_decode(dbConn($eats_salesreportsql, array(), 'select'));
$eats_sales_nyp_dbConn_res = json_decode(dbConn($eats_sales_nyp_reportsql, array(), 'select'));
$eats_PB_dbConn_res = json_decode(dbConn($eats_PB_reportsql, array(), 'select'));
$eats_reqdbConn_res = json_decode(dbConn($eats_reqreportsql, array(), 'select'));

$sales_sumdbConn_res = json_decode(dbConn($sales_sum_sql, array(), 'select'));
$reqs_sumdbConn_res = json_decode(dbConn($reqs_sum_sql, array(), 'select'));
$mmpaymnt_sumdbConn_res = json_decode(dbConn($mmpaymnt_sum_sql, array(), 'select'));

$sections_arr = [];
$res_records = new stdClass();

$drinks_sales_arr_collection = array();
$drinks_sales_arr_final;
$drinks_sales_total = 0;

/*if($drinks_salesdbConn_res->status == 1){
	$drinks_sales_arr_final = array_merge_recursive($drinks_sales_arr_collection, $drinks_salesdbConn_res->message);
}

if($drinks_sales_nyp_dbConn_res->status == 1){
	$drinks_sales_arr_final = array_merge_recursive($drinks_sales_arr_collection, $drinks_sales_nyp_dbConn_res->message);
}

if(count($drinks_sales_arr_collection) > 0){
	array_push($sections_arr, $drinks_sales_arr_final);
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
}
print_r($drinks_sales_arr_final);*/
if($drinks_salesdbConn_res->status == 1 && $drinks_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, array_merge_recursive($drinks_salesdbConn_res->message, $drinks_sales_nyp_dbConn_res->message));
	//print_r($drinks_salesdbConn_res->message);
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
	//echo $drinks_sales_total;
}
if($drinks_salesdbConn_res->status == 1){
	array_push($sections_arr, $drinks_salesdbConn_res->message);
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
}
else if($drinks_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, $drinks_sales_nyp_dbConn_res->message);
}

$drinks_reqs_total = 0;
if($drinks_reqdbConn_res->status == 1 ){
	array_push($sections_arr, $drinks_reqdbConn_res->message);
	foreach($drinks_reqdbConn_res->message as $v){
		$drinks_reqs_total += (int)str_replace(',','',$v->AmountSpent);
	}
	//$drinks_reqs_total = generateTotals($drinks_reqdbConn_res->message);
}

$eats_sales_total = 0;
if($eats_salesdbConn_res->status == 1 && $eats_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, array_merge_recursive($eats_salesdbConn_res->message, $eats_sales_nyp_dbConn_res->message));
	foreach($eats_salesdbConn_res->message as $v){
		$eats_sales_total += (int)str_replace(',','',$v->AmountEats);
	}
}else
if($eats_salesdbConn_res->status == 1){
	array_push($sections_arr, $eats_salesdbConn_res->message);
	foreach($eats_salesdbConn_res->message as $v){
		$eats_sales_total += (int)str_replace(',','',$v->AmountEats);
	}
}
else if($eats_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, $eats_sales_nyp_dbConn_res->message);
}

$eats_reqs_total = 0;
if($eats_reqdbConn_res->status == 1){
	array_push($sections_arr, $eats_reqdbConn_res->message);
	foreach($eats_reqdbConn_res->message as $v){
		$eats_reqs_total += (int)str_replace(',','',$v->AmountSpentEats);
	}
}

//print_r($sections_arr);
if(count($sections_arr) > 0){
	$res_records->status = 1;
	$els_in_arr = [];
	foreach($sections_arr as $v){
		array_push($els_in_arr, count($v));
	}
	$max_els = max($els_in_arr);
	
	foreach($sections_arr as $k => $v){
		if(count($v) == $max_els){
			$arr_with_max_els = $v;
			unset($sections_arr[$k]);
			break;
		}
	}
	
	//print_r($arr_with_max_els);
	//print_r($sections_arr);
	foreach($sections_arr as $secV){
		foreach($arr_with_max_els as $max_elsK => $max_elsV){
			foreach($secV as $sec2K => $sec2V){
				if($max_elsK == $sec2K){
					foreach($sec2V as $sec2ObjK => $sec2ObjV){
						$max_elsV->$sec2ObjK = $sec2ObjV;
					}//print_r($sec2V);
				}
				//print_r($sec2V);
			}
			//print_r($max_elsV);
		}
		//print_r($secV);
	}
	//print_r($arr_with_max_els);
	if($sales_sumdbConn_res->status == 1 ){
		$total_sales = $sales_sumdbConn_res->message[0]->SalesTotal;
	}else{
		$total_sales = 0;
	}
	if($reqs_sumdbConn_res->status == 1 ){
		$total_reqs = $reqs_sumdbConn_res->message[0]->PurchasesTotal;
	}else{
		$total_reqs = 0;
	}
	if($mmpaymnt_sumdbConn_res->status == 1 ){
		$total_mm = $mmpaymnt_sumdbConn_res->message[0]->M_MTotal;
	}else{
		$total_mm = 0;
	}
	$summary_bal = $total_sales - $total_reqs/* - $total_mm*/;
	
	$res_records->message = $arr_with_max_els;
	$res_records->drinks_sales_total = number_format($drinks_sales_total,0);
	$res_records->drinks_reqs_total = number_format($drinks_reqs_total,0);
	$res_records->eats_sales_total = number_format($eats_sales_total,0);
	$res_records->eats_reqs_total = number_format($eats_reqs_total,0);
	$res_records->total_sales = number_format($total_sales,0);
	$res_records->total_reqs = number_format($total_reqs,0);
	$res_records->total_mm = $total_mm;
	$res_records->summary_bal = number_format($summary_bal,0);
	
	echo json_encode($res_records);
}

function generateTotals($arr){
	$totalSum = 0;
	foreach($arr as $v){
		$totalSum += (int)str_replace(',','',$v->Amount);
	}
	return $totalSum;
}
/*if($drinks_salesdbConn_res->status == 1 && $drinks_reqdbConn_res->status == 1){
	$res_records->status = 1;
	$drinks_arr = array_merge($drinks_salesdbConn_res->message, $drinks_reqdbConn_res->message);
	//$res_records->message = $drinks_arr;//json_decode(dbConn($drinks_salesreportsql, array(), 'select'))->message;
	//$res_records->sql = $reportsql; ifnull(sum(finalamount),ifnull(sum(purchaseamount),sum(standardcost))) as Amount
	//echo json_encode($res_records);
	///print_r($drinks_salesdbConn_res->message);
	///print_r($drinks_reqdbConn_res->message);
	
	foreach($drinks_reqdbConn_res->message as $reqK => $reqV){
		foreach($drinks_salesdbConn_res->message as $salK => $salV){
			if($reqK == $salK){
				//(object) array_merge((array) $reqV, (array) $salV); not working as expected
				foreach($salV as $salObjK => $salObjV){
					$reqV->$salObjK = $salObjV;
				}
			}
		}
	}
	///print_r($drinks_reqdbConn_res->message);
	$res_records->message = $drinks_reqdbConn_res->message;
	echo json_encode($res_records);
}else{
	$res_records->message = json_decode(dbConn($drinks_salesreportsql, array(), 'select'))->message;
	echo json_encode($res_records);
}*/
//select details_no, if(`SaleName`= '', `Description`,SaleName) as Item,sum(qty), rate, sum(cost), OrderNo, orderdetails.productno, OrderDate from orderdetails join orders on orderno = Order_No left join products on Product_No = orderdetails.productno left join items_sold on orderdetails.ProductNo = items_sold.ProductNo where orderdate > ('2023-06-14') - interval 1 day + interval 19 hour and orderdate < '2023-06-14' + interval 19 hour and category = 'Drinks' group by productno order by sum(cost) desc
?>
