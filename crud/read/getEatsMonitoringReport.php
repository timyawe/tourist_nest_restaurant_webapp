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

$items = json_decode(dbConn("SELECT Product_No, Description,ifnull(UnitCostPrice,0) AS UnitCostPrice FROM Products LEFT JOIN Items_Bought ON Product_No = ProductNo WHERE Category = 'Eats'", array(), 'select'))->message;

//print_r( $items);
$items_desc = [];
$itemsbf_qty = [];
$itemsbf_amount = [];
$items_particularsdetails = [];
$itemsqtytotals_reqs = [];
$itemsamttotals_reqs = [];
$itemsqtytotals_sales = [];
$itemsamttotals_sales = [];
$itemsqtytotals_offers = [];
$itemsamttotals_offers = [];
$itemsqtytotals_spoilt = [];
$itemsamttotals_spoilt = [];
$itemsfinish_qty = [];
$items_obj = new stdClass();
$res_records = new stdClass();
foreach($items as $v){
	array_push($items_desc, strtoupper($v->Description));
	$costprice = $v->UnitCostPrice;
	$pdtno = $v->Product_No;
	
	$bf_sql = "select Product_No AS value, Description AS label, UnitCostPrice AS rate, Category,UnitQty,measuresold,
			round(if(isnull(reqsTotal),0,round(reqsTotal/measuresold)) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal) + if(isnull(missingTotal),0,missingTotal)),0) AS finish
			from 
			((((((products left join items_bought on ((products.Product_No = items_bought.ProductNo))) 
			left join 
				(select IFNULL(`orderdetails`.`MainProduct_No`, `orderdetails`.`ProductNo`) AS ProductNo ,sum(Qty) AS ordersTotal from orderdetails join orders on Order_No = OrderNo WHERE /*Station = '$station'*/ year(OrderDate) = $year AND month(OrderDate) < $month group by IFNULL(`orderdetails`.`MainProduct_No`, `orderdetails`.`ProductNo`)) AS `ordersqty` on((Product_No = ordersqty.ProductNo))) 
			left join 
				(select ProductNo, sum(QtyRecieved) AS reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no WHERE requisitionType = 'External' AND /*Station = '$station'*/ year(DateRecieved) = $year AND month(DateRecieved) < $month group by ProductNo) AS `reqsqty` on((Product_No = reqsqty.ProductNo))) 
			left join 
				(select ProductNo, sum(Qty) AS offersTotal from offersdetails join offers on OffersID = offers.ID WHERE /*Station = '$station'*/ year(DateOccurred) = $year AND month(DateOccurred) < $month group by ProductNo) AS `offersqty`  on((Product_No = offersqty.productNo))) 
			left join 
				(select ProductNo, sum(Qty) AS spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID WHERE /*Station = '$station'*/ year(DateOccurred) = $year AND month(DateOccurred) < $month group by ProductNo)  AS `spoiltqty`  on((Product_No = spoiltqty.productNo))) 
			left join 
				(select ProductNo, sum(qty) as missingTotal from missingdetails join items_missing on MissingID = items_missing.ID WHERE /*Station = '$station'*/ year(DateOccurred) = $year AND month(DateOccurred) < $month group by ProductNo) as `missingQty` on((Product_No = missingQty.productNo)))
			left join
				(select ProductNo, $station from itemsboughtextended) as stockLevelQty on product_no = stockLevelQty.productno
			left join
				(select productno, RecievedStatus from purchasedetailsextended join purchaseorder on purchaseNo = Purchase_No where RecievedStatus = 0 AND Station = '$station') as recievedStatus on Product_No = recievedStatus.productNo
			left join
				(select productno, MeasureSold from items_sold ) as msrSold on Product_No = msrSold.productno
			left join
				(select productno, max(date(DateRecieved)) as lastRecieved from purchasedetails join purchaseorder on purchaseNo = Purchase_No where RecievedStatus = 1 AND Station = '$station' GROUP BY ProductNo) as lastRecieved on Product_No = lastRecieved.productNo
			where `products`.`Status` = 'Active' AND Product_No = '$pdtno'";

	
	//$bf_sql = $bf_sql." AND Product_No = '$pdtno'";echo $bf_sql;
	$bf_dbConn_res = json_decode(dbConn($bf_sql, array(), 'select'))->message[0];//print_r($bf_dbConn_res);
	if($bf_dbConn_res->finish < 0){
		$bf_qty = 0;
	}else{
		$bf_qty	= $bf_dbConn_res->finish;
	}
	array_push($itemsbf_qty, $bf_qty);
	$bfamt = $bf_dbConn_res->finish * $costprice * $bf_dbConn_res->MeasureSold;//echo $bfamt."\n";
	array_push($itemsbf_amount, number_format($bfamt,0));
	
	if($pdtno != 'PDT033'){
		$particularsDetails = json_decode(dbConn("select date_format(orderdate, '%D') as dateData, ifnull(soldQty,0) as soldQty,ifnull(round(BoughtQty,0),'-') AS BoughtQty, ifnull(format(round(BoughtAmount,0),0),'-') AS BoughtAmount, ifnull(format(soldAmount,0),0) AS soldAmount,ifnull(offersQty,'-') as offersQty, ifnull(format(offersAmount,0),'-') AS offersAmount,ifnull(spoiltQty,'-') as spoiltQty,ifnull(format(spoiltAmount,0),'-') AS spoiltAmount
																	from orders 
																	left join (select orderno, sum(qty) as soldQty, sum(cost) AS soldAmount, date(orderdate) as SalesOrderDate from orderdetails JOIN orders on orderNo = order_No where IFNULL(`orderdetails`.`MainProduct_No`, `orderdetails`.`ProductNo`) = '$pdtno' and year(orderdate) = $year and month(orderdate) = $month  group by date(orderdate)) as salesinfo on date(orderdate) = salesinfo.SalesOrderDate 
																	left join (select date(daterecieved) as PurchRecDate, sum(qtyrecieved/measuresold) as BoughtQty, sum(qtyrecieved) * $costprice AS BoughtAmount from purchasedetails join purchaseorder on PurchaseNo = Purchase_No left join items_sold on PurchaseDetails.ProductNo = Items_Sold.ProductNo where PurchaseDetails.productno = '$pdtno' and year(daterecieved) = $year and month(daterecieved) = $month and RequisitionType = 'External' group by date(daterecieved)) as purchinfo on date(orderdate) = purchinfo.PurchRecDate
																	left join (select sum(qty) as offersQty, sum(qty)* $costprice AS offersAmount, date(dateOccurred) as offersDate from offersdetails JOIN offers on OffersID = Offers.ID where productno = '$pdtno' and year(dateOccurred) = $year and month(dateOccurred) = $month  group by date(dateOccurred)) as offersinfo on date(orderdate) = offersinfo.offersDate 
																	left join (select sum(qty) as spoiltQty, sum(qty)* $costprice AS spoiltAmount, date(dateOccurred) as spoiltDate from spoiltdetails JOIN items_spoilt on SpoiltID = items_spoilt.ID where productno = '$pdtno' and year(dateOccurred) = $year and month(dateOccurred) = $month  group by date(dateOccurred)) as spoiltinfo on date(orderdate) = spoiltinfo.spoiltDate where year(orderdate) = $year and month(orderdate) = $month group by date(orderdate) order by date(orderdate)", array(), 'select'))->message;
	}else{
		$particularsDetails = json_decode(dbConn("select date_format(orderdate, '%D') as dateData, ifnull(soldQty,0) as soldQty,ifnull(round(BoughtQty,0),'-') AS BoughtQty, ifnull(format(round(BoughtAmount,0),0),'-') AS BoughtAmount, ifnull(format(soldAmount,0),0) AS soldAmount,ifnull(offersQty,'-') as offersQty, ifnull(format(offersAmount,0),'-') AS offersAmount,ifnull(spoiltQty,'-') as spoiltQty,ifnull(format(spoiltAmount,0),'-') AS spoiltAmount
																	from orders 
																	left join (select orderno, sum(qty) as soldQty, sum(cost) AS soldAmount, date(orderdate) as SalesOrderDate from orderdetails JOIN orders on orderNo = order_No where IFNULL(`orderdetails`.`MainProduct_No`, `orderdetails`.`ProductNo`) = '$pdtno' and year(orderdate) = $year and month(orderdate) = $month  group by date(orderdate)) as salesinfo on date(orderdate) = salesinfo.SalesOrderDate 
																	left join (select date(daterecieved) as PurchRecDate, sum(qtyrecieved/0.24) as BoughtQty, if(isnull(sum(finalamount)),if(isnull(sum(purchaseamount)),sum(standardcost),sum(purchaseamount)),sum(finalamount)) AS BoughtAmount from purchasedetails join purchaseorder on PurchaseNo = Purchase_No left join items_bought on PurchaseDetails.ProductNo = items_bought.ProductNo where PurchaseDetails.productno = 'PDT043' and year(daterecieved) = $year and month(daterecieved) = $month and RequisitionType = 'External' group by date(daterecieved)) as purchinfo on date(orderdate) = purchinfo.PurchRecDate
																	left join (select sum(qty) as offersQty, sum(qty)* $costprice AS offersAmount, date(dateOccurred) as offersDate from offersdetails JOIN offers on OffersID = Offers.ID where productno = '$pdtno' and year(dateOccurred) = $year and month(dateOccurred) = $month  group by date(dateOccurred)) as offersinfo on date(orderdate) = offersinfo.offersDate 
																	left join (select sum(qty) as spoiltQty, sum(qty)* $costprice AS spoiltAmount, date(dateOccurred) as spoiltDate from spoiltdetails JOIN items_spoilt on SpoiltID = items_spoilt.ID where productno = '$pdtno' and year(dateOccurred) = $year and month(dateOccurred) = $month  group by date(dateOccurred)) as spoiltinfo on date(orderdate) = spoiltinfo.spoiltDate where year(orderdate) = $year and month(orderdate) = $month group by date(orderdate) order by date(orderdate)", array(), 'select'))->message;
	}
	
	array_push($items_particularsdetails ,$particularsDetails/*json_encode($items_obj)*/);
	
	if($pdtno != 'PDT033'){
		$totals_reqs = json_decode(dbConn("select ifnull(round(sum(qtyrecieved/measuresold),0),0) AS totalQtyBought, format(ifnull(sum(finalamount),0) + ifnull(sum(purchaseamount),0) + ifnull(sum(standardcost),0),0) AS totalAmountBought FROM PurchaseDetails JOIN PurchaseOrder ON PurchaseNo = Purchase_No left join items_sold on PurchaseDetails.ProductNo = Items_Sold.ProductNo WHERE year(DateRecieved) = $year AND month(DateRecieved) = $month AND PurchaseDetails.ProductNo = '$pdtno' AND RequisitionType = 'External'", array(), 'select'))->message[0];
		array_push($itemsqtytotals_reqs, $totals_reqs->totalQtyBought + $bf_qty );
		array_push($itemsamttotals_reqs, $totals_reqs->totalAmountBought );
	}else{
		$totals_reqs = json_decode(dbConn("select ifnull(round(sum(qtyrecieved/0.25),0),0) AS totalQtyBought, ifnull(format(if(isnull(sum(finalamount)),if(isnull(sum(purchaseamount)),sum(standardcost),sum(purchaseamount)),sum(finalamount)),0),0) AS totalAmountBought FROM PurchaseDetails JOIN PurchaseOrder ON PurchaseNo = Purchase_No left join items_bought on PurchaseDetails.ProductNo = items_bought.ProductNo WHERE year(DateRecieved) = $year AND month(DateRecieved) = $month AND PurchaseDetails.ProductNo = 'PDT043' AND RequisitionType = 'External'", array(), 'select'))->message[0];
		array_push($itemsqtytotals_reqs, $totals_reqs->totalQtyBought + $bf_qty );
		array_push($itemsamttotals_reqs, $totals_reqs->totalAmountBought );
	}
	
	$totals_sales = json_decode(dbConn("select ifnull(sum(qty),0) AS totalQtySold, ifnull(format(sum(cost),0),0) AS totalAmountSold FROM OrderDetails JOIN Orders ON OrderNo = Order_No WHERE year(OrderDate) = $year AND month(OrderDate) = $month AND IFNULL(`orderdetails`.`MainProduct_No`, `orderdetails`.`ProductNo`) = '$pdtno'", array(), 'select'))->message[0];
	array_push($itemsqtytotals_sales, $totals_sales->totalQtySold);
	array_push($itemsamttotals_sales, $totals_sales->totalAmountSold);
	
	$totals_offers = json_decode(dbConn("select ifnull(sum(qty),0) AS totalQtyOffered, ifnull(format(sum(qty*measuresold) * $costprice,0),0) AS totalAmountOffered FROM OffersDetails JOIN Offers ON OffersID = Offers.ID left join items_sold on OffersDetails.ProductNo = Items_Sold.ProductNo WHERE year(DateOccurred) = $year AND month(DateOccurred) = $month AND OffersDetails.ProductNO = '$pdtno'", array(), 'select'))->message[0];
	array_push($itemsqtytotals_offers, $totals_offers->totalQtyOffered);
	array_push($itemsamttotals_offers, $totals_offers->totalAmountOffered);
	
	$totals_spoilt = json_decode(dbConn("select ifnull(sum(qty),0) AS totalQtySpoilt, ifnull(format(sum(qty*measuresold) * $costprice,0),0) AS totalAmountSpoilt FROM SpoiltDetails JOIN Items_Spoilt ON SpoiltID = Items_Spoilt.ID left join items_sold on SpoiltDetails.ProductNo = Items_Sold.ProductNo WHERE year(DateOccurred) = $year AND month(DateOccurred) = $month AND SpoiltDetails.ProductNO = '$pdtno'", array(), 'select'))->message[0];
	array_push($itemsqtytotals_spoilt, $totals_spoilt->totalQtySpoilt);
	array_push($itemsamttotals_spoilt, $totals_spoilt->totalAmountSpoilt);
	
	array_push($itemsfinish_qty, ($totals_reqs->totalQtyBought + $bf_qty) - ($totals_sales->totalQtySold + $totals_offers->totalQtyOffered + $totals_spoilt->totalQtySpoilt) );
}
//print_r($items_col);
$res_records->status = 1;
$res_records->items_desc = $items_desc;
$res_records->itemsbf_qty = $itemsbf_qty;
$res_records->itemsbf_amount = $itemsbf_amount;
$res_records->message = $items_particularsdetails;
$res_records->itemsqtytotals_reqs = $itemsqtytotals_reqs;
$res_records->itemsamttotals_reqs = $itemsamttotals_reqs;
$res_records->itemsqtytotals_sales = $itemsqtytotals_sales;
$res_records->itemsamttotals_sales = $itemsamttotals_sales;
$res_records->itemsqtytotals_offers = $itemsqtytotals_offers;
$res_records->itemsamttotals_offers = $itemsamttotals_offers;
$res_records->itemsqtytotals_spoilt = $itemsqtytotals_spoilt;
$res_records->itemsamttotals_spoilt = $itemsamttotals_spoilt;
$res_records->itemsfinish_qty = $itemsfinish_qty;
echo json_encode($res_records);
?>
