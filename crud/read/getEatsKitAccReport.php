<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$json_data = json_decode($_GET['_data'], true);

if($json_data['station'] != 'All'){
	//print_r($json_data['station']);
	$station = $json_data['station'];
}

if($json_data['item_acc_date_filter'] == 'monthly'){
	$month = $json_data['item_acc_date_filter_month'];
	$year = $json_data['item_acc_date_filter_year'];
	
	$purch_date_filter = " month(DateRecieved) = $month AND year(DateRecieved) = $year ";
	$sales_orderdate_filter = " month(OrderDate) = $month AND year(OrderDate) = $year ";
	$sales_paiddate_filter = " month(PaidDate) = $month AND year(PaidDate) = $year ";
	$offers_date_filter = " month(DateOccurred) = $month AND year(DateOccurred) = $year ";
	$spoilt_date_filter = " month(DateOccurred) = $month AND year(DateOccurred) = $year ";
}

$accSql = "select if(category = 'Eats',if(salename = ' ', `Description`,salename),`Description`) as item, if(category = 'Kitchen',if(QtyBought > 1,concat(ifnull(QtyBought,0),' ', UnitQty, 'S'), concat(ifnull(QtyBought,0),' ', UnitQty)), ifnull(QtyBought,0) ) AS QtyBought, ifnull(AmountBought,0) AS AmountBought, ifnull(QtySold,0) AS QtySold, ifnull(QtySoldSale,0) AS QtySoldSale, ifnull(QtySoldCost,0) AS QtySoldCost, ifnull(QtyOffered,0) AS QtyOffered, ifnull(QtyOfferedCost,0) AS QtyOfferedCost, ifnull(QtySpoilt,0) AS QtySpoilt, ifnull(QtySpoiltCost,0) AS QtySpoiltCost from Products 
			left join items_sold on Product_No = items_sold.ProductNo left join items_bought on Product_No = items_bought.ProductNo
			left join 
				(select ProductNo, round(sum(qtyRecieved),0) as QtyBought, format(sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))),0) /*sum(qtyRecieved) * rate*/ as AmountBought from purchasedetails left join purchaseorder on PurchaseNo = Purchase_No where RequisitionType = 'External' and $purch_date_filter and Station = '$station' GROUP BY ProductNo) as BoughtInfo on BoughtInfo.ProductNo = Product_No
			left join
				(select orderdetails.ProductNo, sum(qty) as QtySold, format(sum(cost),0) as QtySoldSale, format(/*sum(*/if(isnull(PurchaseRate), (sum(qty) * UnitCostPrice) * MeasureSold, (sum(qty) * PurchaseRate) * MeasureSold)/*)*/,0) as QtySoldCost from orderdetails left join orders on OrderNo = Order_No left join items_bought on orderdetails.ProductNo = items_bought.ProductNo left join items_sold on orderdetails.ProductNo = items_sold.ProductNo where PaidStatus = 1 AND if(isnull(PaidDate), $sales_orderdate_filter,$sales_paiddate_filter) and Station = '$station' Group by orderdetails.ProductNo ) as SoldInfo on SoldInfo.ProductNo = Product_No
			left join
				(select offersdetails.ProductNo, sum(qty) as QtyOffered, format(if(isnull(PurchaseRate), (sum(qty) * UnitCostPrice) * MeasureSold, (sum(qty) * PurchaseRate) * MeasureSold),2) as QtyOfferedCost from offersdetails left join offers on Offers.ID = OffersID left join items_bought on offersdetails.ProductNo = items_bought.ProductNo left join items_sold on offersdetails.ProductNo = items_sold.ProductNo where isDeleted = 0 and $offers_date_filter and Station = '$station' group by offersdetails.ProductNo) as offersInfo on OffersInfo.ProductNo = Product_No
			left join
				(select spoiltdetails.ProductNo, sum(qty) as QtySpoilt, format((sum(qty) * UnitCostPrice) * MeasureSold,2) as QtySpoiltCost from spoiltdetails left join items_spoilt on spoiltID = items_spoilt.ID left join items_bought on spoiltdetails.ProductNo = items_bought.ProductNo left join items_sold on spoiltdetails.ProductNo = items_sold.ProductNo where $spoilt_date_filter and Station = '$station' group by spoiltdetails.ProductNo) as SpoiltInfo on SpoiltInfo.ProductNo = Product_No
            where category = 'Eats' OR category = 'Kitchen'";

$eats_bought_sumsql = "select sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))) as BoughtTotalEats from purchasedetails left join purchaseorder on PurchaseNo = Purchase_No left join products on ProductNo = Product_No where RequisitionType = 'External' and $purch_date_filter AND Products.Category = 'Eats'";
$kit_bought_sumsql = "select sum(if(isnull((finalamount)),if(isnull((purchaseamount)),(standardcost),(purchaseamount)),(finalamount))) as BoughtTotalKitchen from purchasedetails left join purchaseorder on PurchaseNo = Purchase_No left join products on ProductNo = Product_No where RequisitionType = 'External' and $purch_date_filter AND Products.Category = 'Kitchen'";
$sold_sale_cost_sumsql = "select sum(cost) as SoldSaleTotal, if(isnull(PurchaseRate), (sum(qty) * UnitCostPrice) * MeasureSold, (sum(qty) * PurchaseRate) * MeasureSold) AS SoldCostTotal from orderdetails left join orders on OrderNo = Order_No left join products on orderdetails.ProductNo = Product_No left join items_bought on orderdetails.ProductNo = items_bought.ProductNo left join items_sold on orderdetails.ProductNo = items_sold.ProductNo where PaidStatus = 1 AND if(isnull(PaidDate), $sales_orderdate_filter, $sales_paiddate_filter) and Station = '$station' AND Products.Category = 'Eats'";
$sold_cost_sumsql = "select sum(if(isnull(PurchaseRate), ((qty) * UnitCostPrice) * MeasureSold, ((qty) * PurchaseRate) * MeasureSold)) AS SoldCostTotal from orderdetails left join orders on OrderNo = Order_No left join products on orderdetails.ProductNo = Product_No left join items_bought on orderdetails.ProductNo = items_bought.ProductNo left join items_sold on orderdetails.ProductNo = items_sold.ProductNo where PaidStatus = 1 AND if(isnull(PaidDate), $sales_orderdate_filter, $sales_paiddate_filter) and Station = '$station' AND Products.Category = 'Eats' group by if(isnull(PaidDate), $sales_orderdate_filter, $sales_paiddate_filter)";
$offerscost_sumsql = "select sum(if(isnull(PurchaseRate), ((qty) * UnitCostPrice) * MeasureSold, ((qty) * PurchaseRate) * MeasureSold)) as OfferedCostTotal from offersdetails left join offers on Offers.ID = OffersID left join products on offersdetails.ProductNo = Product_No left join items_bought on offersdetails.ProductNo = items_bought.ProductNo left join items_sold on offersdetails.ProductNo = items_sold.ProductNo where isDeleted = 0 and $offers_date_filter and Station = '$station' AND Products.Category = 'Eats' group by $offers_date_filter";
$spoiltcost_sumsql = "select sum((qty * UnitCostPrice) * MeasureSold) as SpoiltCostTotal from spoiltdetails left join products on spoiltdetails.ProductNo = Product_No left join items_spoilt on spoiltID = items_spoilt.ID left join items_bought on spoiltdetails.ProductNo = items_bought.ProductNo left join items_sold on spoiltdetails.ProductNo = items_sold.ProductNo where $spoilt_date_filter and Station = '$station' AND Products.Category = 'Eats' group by $spoilt_date_filter -- month(DateOccurred) = 07";

$res_records = new stdClass();
$acc_dbConn_res = json_decode(dbConn($accSql, array(), 'select'));

$acc_eats_bought_sum_dbConn_res = json_decode(dbConn($eats_bought_sumsql, array(), 'select'));
$acc_kit_bought_sum_dbConn_res = json_decode(dbConn($kit_bought_sumsql, array(), 'select'));
$acc_sold_sum_dbConn_res = json_decode(dbConn($sold_sale_cost_sumsql, array(), 'select'));
$acc_sold_cost_sum_dbConn_res = json_decode(dbConn($sold_cost_sumsql, array(), 'select'));
$acc_offered_sum_dbConn_res = json_decode(dbConn($offerscost_sumsql, array(), 'select'));
$acc_spoilt_sum_dbConn_res = json_decode(dbConn($spoiltcost_sumsql, array(), 'select'));

if($acc_dbConn_res->status == 1){
	$res_records->status = 1;
	$res_records->message = $acc_dbConn_res->message;
	
	$res_records->bought_total = number_format($acc_eats_bought_sum_dbConn_res->message[0]->BoughtTotalEats + $acc_kit_bought_sum_dbConn_res->message[0]->BoughtTotalKitchen,0);
	$res_records->kit_bought_subtotal = number_format($acc_kit_bought_sum_dbConn_res->message[0]->BoughtTotalKitchen,0);
	$res_records->sold_sale_total = number_format($acc_sold_sum_dbConn_res->message[0]->SoldSaleTotal,0);
	$res_records->sold_cost_total = number_format($acc_sold_cost_sum_dbConn_res->message[0]->SoldCostTotal,0);
	$res_records->offered_cost_total = number_format($acc_offered_sum_dbConn_res->message[0]->OfferedCostTotal,0);
	$res_records->spoilt_cost_total = number_format($acc_spoilt_sum_dbConn_res->message[0]->SpoiltCostTotal,0);
}	
echo json_encode($res_records);

?>
