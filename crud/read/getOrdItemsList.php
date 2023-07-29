<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "select Product_No AS value, if((SaleName = ''),Description,SaleName) AS label, UnitSalePrice AS rate, UnitCostPrice as PurchaseRate,
			(if(isnull(reqsTotal),0,reqsTotal/MeasureSold) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal) + if(isnull(missingTotal),0,missingTotal))) AS finish, if(isnull(items_bought.ProductNo), 1,0) as onlySold,MeasureSold,reqsTotal
			from 
			((((((products join items_sold on ((products.Product_No = items_sold.ProductNo)))
			left join items_bought on Product_no = items_bought.ProductNo
			left join 
				(select ProductNo ,sum(Qty) AS ordersTotal from orderdetails join orders on Order_No = OrderNo WHERE Station = '$station' group by ProductNo) AS `ordersqty` on((Product_No = ordersqty.ProductNo))) 
			left join 
				(select ProductNo, sum(QtyRecieved) AS reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no WHERE Station = '$station' group by ProductNo) AS `reqsqty` on((Product_No = reqsqty.ProductNo))) 
			left join 
				(select ProductNo, sum(Qty) AS offersTotal from offersdetails join offers on OffersID = offers.ID WHERE Station = '$station' AND isDeleted <> 1 group by ProductNo) AS `offersqty` on((Product_No = offersqty.productNo))) 
			left join 
				(select ProductNo, sum(Qty) AS spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID WHERE Station = '$station' group by ProductNo)  AS `spoiltqty` on((Product_No = spoiltqty.productNo))) 
			left join 
				(select ProductNo, sum(qty) as missingTotal from missingdetails join items_missing on MissingID = items_missing.ID WHERE Station = '$station' group by ProductNo) as `missingQty` on((Product_No = missingQty.productNo)))
			where (`nest_restaurant`.`products`.`Status` = 'Active')";
}else{
	$sql = "SELECT * FROM OrdItemsList";
}
$sql_res_records = json_decode(dbConn($sql, array(), 'select'));

if($sql_res_records->status === 1){
	echo json_encode($res_records->message = $sql_res_records->message);
}
?>
