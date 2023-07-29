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

if(date('Y-m-d', strtotime($_date)) > date('Y-m-d', strtotime('2023-07-25'))){//used for wether to restrict items with payments before 7pm or not. 
	$drinks_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty, format(sum(cost),0) as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks'  GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
	$drinks_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty,'NYP' as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 0 /*delete*/  OR orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 1 and paiddate > '$_date' + INTERVAL 19 HOUR /*delete*/ GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
	$drinks_PB_reportsql = "SELECT details_no, concat(if(`SaleName`= '', `Description`,SaleName), ' ', 'P/B') as item,sum(qty) as Qty, format(sum(cost),0) as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE Orderdate < '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' GROUP BY orderdetails.productno ORDER BY sum(Cost) DESC";
	$drinks_reqreportsql = "SELECT details_no, `Description` AS itemBought, round(SUM(qtyRecieved)) AS QtyBought, format(if(isnull(sum(finalamount)),if(isnull(sum(purchaseamount)),sum(standardcost),sum(purchaseamount)),sum(finalamount)),0) AS AmountSpent FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Drinks' AND RequisitionType = 'External' GROUP BY ProductNo";
	$eats_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ format(sum(cost),0) as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 1 AND PaidDate < '$_date' + INTERVAL 19 HOUR GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
	$eats_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ 'NYP' as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate >'$_date'  - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 0 /*delete*/  OR orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 1 and paiddate > '$_date' + INTERVAL 19 HOUR /*delete*/ GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
	$eats_PB_reportsql = "SELECT details_no, concat(if(`SaleName`= '', `Description`,SaleName), ' ', 'P/B') as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ format(sum(cost),0) as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE Orderdate < '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' GROUP BY orderdetails.productno ORDER BY sum(Cost) DESC";
	$eats_reqreportsql = "SELECT details_no, `Description` AS itemBoughtEats, if(sum(qtyRecieved) > 1, concat(round(SUM(qtyRecieved)), ' ',  UnitQty, 'S'),concat(round(SUM(qtyRecieved)), ' ',  UnitQty)) AS QtyBoughtEats, format(sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))),0) AS AmountSpentEats FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo left join items_bought on purchasedetails.ProductNo = items_bought.ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Eats' OR Date(DateRecieved) = '$_date' AND products.Category = 'Kitchen' GROUP BY items_bought.ProductNo";
	
	$sales_sum_sql = "select sum(cost) AS SalesTotal from orderdetails join orders on order_no = orderno where PaidStatus = 1 AND PaidDate < '$_date' + INTERVAL 19 HOUR and OrderDate > '$_date' - interval 1 day + interval 19 hour and orderdate < '$_date' + interval 19 HOUR";
	$sales_pb_sum_sql = "select sum(cost) AS SalesTotal from orderdetails join orders on order_no = orderno where Orderdate < '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR";
	$reqs_sum_sql = "select sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))) AS PurchasesTotal FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo WHERE Date(DateRecieved) = '$_date'";
}else{
				
	$drinks_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty, format(sum(cost),0) as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 1 GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
	$drinks_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as item,sum(qty) as Qty,'NYP' as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks' AND PaidStatus = 0 GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
	$drinks_PB_reportsql = "SELECT details_no, concat(if(`SaleName`= '', `Description`,SaleName), ' ', 'P/B') as item,sum(qty) as Qty, format(sum(cost),0) as Amount FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE Orderdate < '2023-05-13'/*'$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR AND category = 'Drinks'*/ GROUP BY orderdetails.productno ORDER BY sum(Cost) DESC";//query made to specifically generate 0 rows to maintain orders paid after 7pm
	$drinks_reqreportsql = "SELECT details_no, `Description` AS itemBought, round(SUM(qtyRecieved)) AS QtyBought, format(if(isnull(sum(finalamount)),if(isnull(sum(purchaseamount)),sum(standardcost),sum(purchaseamount)),sum(finalamount)),0) AS AmountSpent FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Drinks' AND RequisitionType = 'External' GROUP BY ProductNo";
	$eats_salesreportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ format(sum(cost),0) as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 1 GROUP BY orderdetails.productno ORDER BY sum(cost) DESC";
	$eats_sales_nyp_reportsql = "SELECT details_no, if(`SaleName`= '', `Description`,SaleName) as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ 'NYP' as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE orderdate >'$_date'  - INTERVAL 1 DAY + INTERVAL 19 HOUR AND orderdate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats' AND PaidStatus = 0 GROUP BY orderdetails.productno ORDER BY sum(qty) DESC";
	$eats_PB_reportsql = "SELECT details_no, concat(if(`SaleName`= '', `Description`,SaleName), ' ', 'P/B') as itemSoldEats,sum(qty) as QtySoldEats, /*rate,*/ format(sum(cost),0) as AmountEats/*, OrderNo, orderdetails.productno, OrderDate*/ FROM orderdetails JOIN orders ON orderno = Order_No LEFT JOIN products ON Product_No = orderdetails.productno LEFT JOIN items_sold ON orderdetails.ProductNo = items_sold.ProductNo WHERE Orderdate < '2023-05-13'/*'$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR AND category = 'Eats'*/ GROUP BY orderdetails.productno ORDER BY sum(Cost) DESC";//query made to specifically generate 0 rows to maintain orders paid after 7pm
	$eats_reqreportsql = "SELECT details_no, `Description` AS itemBoughtEats, if(sum(qtyRecieved) > 1, concat(round(SUM(qtyRecieved)), ' ',  UnitQty, 'S'),concat(round(SUM(qtyRecieved)), ' ',  UnitQty)) AS QtyBoughtEats, format(sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))),0) AS AmountSpentEats FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo LEFT JOIN products ON Product_No = ProductNo left join items_bought on purchasedetails.ProductNo = items_bought.ProductNo WHERE Date(DateRecieved) = '$_date' AND products.Category = 'Eats' OR Date(DateRecieved) = '$_date' AND products.Category = 'Kitchen' GROUP BY items_bought.ProductNo";
	
	$sales_sum_sql = "select sum(cost) AS SalesTotal from orderdetails join orders on order_no = orderno where PaidStatus = 1 and OrderDate > '$_date' - interval 1 day + interval 19 hour and orderdate < '$_date' + interval 19 HOUR";
	$sales_pb_sum_sql = "select sum(cost) AS SalesTotal from orderdetails join orders on order_no = orderno where Orderdate < '2023-05-13'/*'$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidStatus = 1 AND PaidDate > '$_date' - INTERVAL 1 DAY + INTERVAL 19 HOUR AND PaidDate < '$_date' + INTERVAL 19 HOUR*/";
	$reqs_sum_sql = "select sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))) AS PurchasesTotal FROM purchasedetails JOIN purchaseorder ON Purchase_No = PurchaseNo WHERE Date(DateRecieved) = '$_date'";

}

$drinks_offersreportsql = "select offersdetails.ID, sum(qty) as DrinksQty/*, offersID*/, concat(if(salename = '',`Description`, salename), ' ', '(',recipientCategory,')') as DrinksItem, 'XXXXXX' AS DrinksAmount, offersdetails.productno/*, DateOccurred, RecipientCategory*/ from offersdetails left join offers on offers.ID = OffersID left join products on offersdetails.productNo = product_no left join items_sold on offersdetails.ProductNo = items_sold.ProductNo where isDeleted <> 1 and date(dateoccurred) = '$_date' and Category = 'Drinks' GROUP BY  ProductNo,RecipientCategory ORDER BY sum(qty) DESC";
$eats_offersreportsql = "select offersdetails.ID, sum(qty) as EatsQty/*, offersID*/, concat(if(salename = '',`Description`, salename), ' ', '(',recipientCategory,')') as EatsItem, 'XXXXXX' AS EatsAmount, offersdetails.productno/*, DateOccurred, RecipientCategory*/ from offersdetails left join offers on offers.ID = OffersID left join products on offersdetails.productNo = product_no left join items_sold on offersdetails.ProductNo = items_sold.ProductNo where isDeleted <> 1 and date(dateoccurred) = '$_date' and Category = 'Eats' GROUP BY  ProductNo,RecipientCategory ORDER BY sum(qty) DESC";

$mmpaymnt_sum_sql = "select sum(amount) AS M_MTotal from orderpaymentsextended where method <> 'Cash' and `date` = '$_date'";

$drinks_salesdbConn_res = json_decode(dbConn($drinks_salesreportsql, array(), 'select'));
$drinks_sales_nyp_dbConn_res = json_decode(dbConn($drinks_sales_nyp_reportsql, array(), 'select'));
$drinks_PB_dbConn_res = json_decode(dbConn($drinks_PB_reportsql, array(), 'select'));
$drinks_reqdbConn_res = json_decode(dbConn($drinks_reqreportsql, array(), 'select'));
$eats_salesdbConn_res = json_decode(dbConn($eats_salesreportsql, array(), 'select'));
$eats_sales_nyp_dbConn_res = json_decode(dbConn($eats_sales_nyp_reportsql, array(), 'select'));
$eats_PB_dbConn_res = json_decode(dbConn($eats_PB_reportsql, array(), 'select'));
$eats_reqdbConn_res = json_decode(dbConn($eats_reqreportsql, array(), 'select'));

$drinks_offers_dbConn_res = json_decode(dbConn($drinks_offersreportsql, array(), 'select'));
$eats_offers_dbConn_res = json_decode(dbConn($eats_offersreportsql, array(), 'select'));

$sales_sumdbConn_res = json_decode(dbConn($sales_sum_sql, array(), 'select'));
$sales_pb_sumdbConn_res = json_decode(dbConn($sales_pb_sum_sql, array(), 'select'));
$reqs_sumdbConn_res = json_decode(dbConn($reqs_sum_sql, array(), 'select'));
$mmpaymnt_sumdbConn_res = json_decode(dbConn($mmpaymnt_sum_sql, array(), 'select'));

$drinks_paid_arr = [];
$drinks_nyp_arr = [];
$drinks_pb_arr = [];

$eats_paid_arr = [];
$eats_nyp_arr = [];
$eats_pb_arr = [];

$sections_arr = [];
$offers_sections_arr = [];
$res_records = new stdClass();

$drinks_sales_total = 0;
if($drinks_salesdbConn_res->status == 1){
	array_push($drinks_paid_arr, $drinks_salesdbConn_res->message);
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
}else{
	array_push($drinks_pb_arr, $arr_d[0] = array());//add empty array to 0 index to assume 0 rows
}

if($drinks_PB_dbConn_res->status == 1){
	array_push($drinks_pb_arr, $drinks_PB_dbConn_res->message);
	$drinks_sales_total += generateTotals($drinks_PB_dbConn_res->message);
}else{
	array_push($drinks_pb_arr, $arr_pbd[0] = array());
}

if($drinks_sales_nyp_dbConn_res->status == 1){
	array_push($drinks_nyp_arr, $drinks_sales_nyp_dbConn_res->message);
}else{
	array_push($drinks_nyp_arr, $arr_nypd[0] = array());
}
array_push($sections_arr, array_merge_recursive($drinks_paid_arr[0], $drinks_pb_arr[0], $drinks_nyp_arr[0]));

/*if($drinks_salesdbConn_res->status == 1 && $drinks_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, array_merge_recursive($drinks_salesdbConn_res->message, $drinks_sales_nyp_dbConn_res->message));
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
	//echo $drinks_sales_total;
}
if($drinks_salesdbConn_res->status == 1){
	array_push($sections_arr, $drinks_salesdbConn_res->message);
	$drinks_sales_total = generateTotals($drinks_salesdbConn_res->message);
}
else if($drinks_sales_nyp_dbConn_res->status == 1){
	array_push($sections_arr, $drinks_sales_nyp_dbConn_res->message);
}*/

$drinks_reqs_total = 0;
if($drinks_reqdbConn_res->status == 1 ){
	array_push($sections_arr, $drinks_reqdbConn_res->message);
	foreach($drinks_reqdbConn_res->message as $v){
		$drinks_reqs_total += (int)str_replace(',','',$v->AmountSpent);
	}
	//$drinks_reqs_total = generateTotals($drinks_reqdbConn_res->message);
}

$eats_sales_total = 0;
if($eats_salesdbConn_res->status == 1){
	array_push($eats_paid_arr, $eats_salesdbConn_res->message);
	foreach($eats_salesdbConn_res->message as $v){
		$eats_sales_total += (int)str_replace(',','',$v->AmountEats);
	}
}else{
	array_push($eats_paid_arr, $arr_e[0] = array());
}

if($eats_PB_dbConn_res->status == 1){
	array_push($eats_pb_arr, $eats_PB_dbConn_res->message);
	foreach($eats_PB_dbConn_res->message as $v){
		$eats_sales_total += (int)str_replace(',','',$v->AmountEats);
	}
}else{
	array_push($eats_pb_arr, $arr_pbe[0] = array());
}

if($eats_sales_nyp_dbConn_res->status == 1){
	array_push($eats_nyp_arr, $eats_sales_nyp_dbConn_res->message);
}else{
	array_push($eats_nyp_arr, $arr_nype[0] = array());
}

array_push($sections_arr, array_merge_recursive($eats_paid_arr[0], $eats_pb_arr[0], $eats_nyp_arr[0]));

/*if($eats_salesdbConn_res->status == 1 && $eats_sales_nyp_dbConn_res->status == 1){
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
}*/

$eats_reqs_total = 0;
if($eats_reqdbConn_res->status == 1){
	array_push($sections_arr, $eats_reqdbConn_res->message);
	foreach($eats_reqdbConn_res->message as $v){
		$eats_reqs_total += (int)str_replace(',','',$v->AmountSpentEats);
	}
}

if($drinks_offers_dbConn_res->status == 1 /*&& $eats_offers_dbConn_res->status == 1*/){
	array_push($offers_sections_arr, /*array_merge_recursive(*/$drinks_offers_dbConn_res->message/*, $eats_offers_dbConn_res->message)*/);
}

if($eats_offers_dbConn_res->status == 1){
	array_push($offers_sections_arr, $eats_offers_dbConn_res->message);
}


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
	
	if(count($offers_sections_arr) > 0){	
		$res_records->status_offers = 1;
		$els_in_offers_arr = [];
		foreach($offers_sections_arr as $off_v){
			array_push($els_in_offers_arr, count($off_v));
		}
		$max_offers_els = max($els_in_offers_arr);
		
		foreach($offers_sections_arr as $offK => $offV){
			if(count($offV) == $max_offers_els){
				$arr_with_max_offers_els = $offV;
				unset($offers_sections_arr[$offK]);
				break;
			}
		}
		
		foreach($offers_sections_arr as $offsecV){
			foreach($arr_with_max_offers_els as $offmax_elsK => $offmax_elsV){
				foreach($offsecV as $offsec2K => $offsec2V){
					if($offmax_elsK == $offsec2K){
						foreach($offsec2V as $offsec2ObjK => $offsec2ObjV){
							$offmax_elsV->$offsec2ObjK = $offsec2ObjV;
						}
					}
				}
			}
		}
		
		$res_records->message_offers = $arr_with_max_offers_els;	
	}
	
	if($sales_sumdbConn_res->status == 1 ){
		$total_sales = $sales_sumdbConn_res->message[0]->SalesTotal + $sales_pb_sumdbConn_res->message[0]->SalesTotal;
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
