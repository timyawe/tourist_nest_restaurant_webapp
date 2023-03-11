<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "select Product_No AS value, Description AS label, UnitCostPrice AS rate, Category,
			(if(isnull(reqsTotal),0,reqsTotal) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal))) AS finish,
			$station as MinStockLevel, if(isnull(RecievedStatus), 1, RecievedStatus) as Recieved
			from 
			(((((products join items_bought on ((products.Product_No = items_bought.ProductNo))) 
			left join 
				(select ProductNo ,sum(Qty) AS ordersTotal from orderdetails join orders on Order_No = OrderNo WHERE Station = '$station' group by ProductNo) AS `ordersqty` on((Product_No = ordersqty.ProductNo))) 
			left join 
				(select ProductNo, sum(QtyRecieved) AS reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no WHERE Station = '$station' group by ProductNo) AS `reqsqty` on((Product_No = reqsqty.ProductNo))) 
			left join 
				(select ProductNo, sum(Qty) AS offersTotal from offersdetails join offers on OffersID = offers.ID WHERE Station = '$station' group by ProductNo) AS `offersqty`  on((Product_No = offersqty.productNo))) 
			left join 
				(select ProductNo, sum(Qty) AS spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID WHERE Station = '$station' group by ProductNo)  AS `spoiltqty`  on((Product_No = spoiltqty.productNo))) 
			left join
				(select ProductNo, $station from itemsboughtextended) as stockLevelQty on product_no = stockLevelQty.productno
			left join
				(select productno, RecievedStatus from purchasedetailsextended join purchaseorder on purchaseNo = Purchase_No where RecievedStatus = 0 AND Station = '$station') as lastRecieved on Product_No = lastRecieved.productNo
			where (`nest_restaurant`.`products`.`Status` = 'Active')";
}else{
	$sql = "SELECT * FROM ReqItemsList";
}
$res_records = json_decode(dbConn($sql, array(), 'select'));

if($res_records->status === 1){
	echo json_encode($res_records->message);
}
?>