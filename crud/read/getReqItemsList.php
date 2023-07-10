<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "select Product_No AS value, Description AS label, UnitCostPrice AS rate, Category,
			(if(isnull(reqsTotal),0,round(reqsTotal/measuresold)) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal) + if(isnull(missingTotal),0,missingTotal))) AS finish,
			$station as MinStockLevel, if(isnull(RecievedStatus), 1, RecievedStatus) as Recieved, lastRecieved
			from 
			((((((products join items_bought on ((products.Product_No = items_bought.ProductNo))) 
			left join 
				(select ProductNo ,sum(Qty) AS ordersTotal from orderdetails join orders on Order_No = OrderNo WHERE Station = '$station' group by ProductNo) AS `ordersqty` on((Product_No = ordersqty.ProductNo))) 
			left join 
				(select ProductNo, sum(QtyRecieved) AS reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no WHERE Station = '$station' group by ProductNo) AS `reqsqty` on((Product_No = reqsqty.ProductNo))) 
			left join 
				(select ProductNo, sum(Qty) AS offersTotal from offersdetails join offers on OffersID = offers.ID WHERE Station = '$station' group by ProductNo) AS `offersqty`  on((Product_No = offersqty.productNo))) 
			left join 
				(select ProductNo, sum(Qty) AS spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID WHERE Station = '$station' group by ProductNo)  AS `spoiltqty`  on((Product_No = spoiltqty.productNo))) 
			left join 
				(select ProductNo, sum(qty) as missingTotal from missingdetails join items_missing on MissingID = items_missing.ID WHERE Station = '$station' group by ProductNo) as `missingQty` on((Product_No = missingQty.productNo)))
			left join
				(select ProductNo, $station from itemsboughtextended) as stockLevelQty on product_no = stockLevelQty.productno
			left join
				(select productno, RecievedStatus from purchasedetailsextended join purchaseorder on purchaseNo = Purchase_No where RecievedStatus = 0 AND Station = '$station') as recievedStatus on Product_No = recievedStatus.productNo
			left join
				(select productno, MeasureSold from items_sold ) as msrSold on Product_No = msrSold.productno
			left join
				(select productno, max(date(DateRecieved)) as lastRecieved from purchasedetails join purchaseorder on purchaseNo = Purchase_No where RecievedStatus = 1 AND Station = '$station' GROUP BY ProductNo) as lastRecieved on Product_No = lastRecieved.productNo
			where (`nest_restaurant`.`products`.`Status` = 'Active')";
}else{
	//$sql = "SELECT * FROM ReqItemsList";
	$sql = "select products.product_no as `value`, `description` as label, if(rate != round(rate,0), round(rate,3),round(rate,0)) as rate, category/*, ifnull(qty,0) as requestedTotal*/ ,ifnull(qtyRecieved,0) as recievedTotal ,sum(qtyGiven) as givenTotal, ifnull(qtyRecieved,0) - ifnull(sum(qtygiven),0) as finish, MinStockLevel, if(isnull(RecievedStatus), 1, RecievedStatus) as Recieved 
		   from products 
		   left join (select ProductNo, sum(qty) as qty, sum(qtyRecieved) as qtyRecieved /*,qtyGiven ,GivenStatus, RecievedStatus*/ from purchasedetails join purchaseorder on Purchase_No = PurchaseNo where RequisitionType = 'External' group by ProductNo) as recievedInfo on products.product_no = recievedInfo.productno
		   left join (select Product_No, /*qty, qtyRecieved ,*/qtyGiven ,GivenStatus, RecievedStatus from internalrequisition_given_ext /*left join purchaseorder on Purchase_No = PurchaseNo where RequisitionType = 'External'*/) as givenInfo on products.product_no = givenInfo.product_no
		   left join (select productno, UnitCostPrice AS rate,store as MinStockLevel from items_bought join stocklevels on items_bought.ID = ItemsBoughtID) as stocklevelsInfo on stocklevelsInfo.ProductNo = products.product_no
		   where `Status` = 'Active' group by products.Product_No";
}
$res_records = json_decode(dbConn($sql, array(), 'select'));

if($res_records->status === 1){
	echo json_encode($res_records->message);
}
?>
