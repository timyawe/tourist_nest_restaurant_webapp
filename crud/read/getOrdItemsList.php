<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

if(isset($_GET['station'])){
	$station = $_GET['station'];
	$sql = "select Product_No AS value, if((SaleName = ''),Description,SaleName) AS label, UnitSalePrice AS rate,
			(if(isnull(reqsTotal),0,reqsTotal) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal))) AS finish 
			from 
			(((((products join items_sold on ((products.Product_No = items_sold.ProductNo))) 
			left join 
				(select ProductNo ,sum(Qty) AS ordersTotal from orderdetails join orders on Order_No = OrderNo WHERE Station = '$station' group by ProductNo) AS `ordersqty` on((Product_No = ordersqty.ProductNo))) 
			left join 
				(select ProductNo, sum(QtyRecieved) AS reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no WHERE Station = '$station' group by ProductNo) AS `reqsqty` on((Product_No = reqsqty.ProductNo))) 
			left join 
				(select ProductNo, sum(Qty) AS offersTotal from offersdetails join offers on OffersID = offers.ID WHERE Station = '$station' group by ProductNo) AS `offersqty` on((Product_No = offersqty.productNo))) 
			left join 
				(select ProductNo, sum(Qty) AS spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID WHERE Station = '$station' group by ProductNo)  AS `spoiltqty` on((Product_No = spoiltqty.productNo))) 
			where (`nest_restaurant`.`products`.`Status` = 'Active')";
}else{
	$sql = "SELECT * FROM OrdItemsList";
}
$sql_res_records = json_decode(dbConn($sql, array(), 'select'));

if($sql_res_records->status === 1){
	echo json_encode($res_records->message = $sql_res_records->message);
}
?>