<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

//print_r($_GET['_data']);
$json_data = json_decode($_GET['_data'], true);
//print_r($json_data);
if($json_data['station'] != 'All'){
	//print_r($json_data['station']);
	$station = $json_data['station'];
}

if(array_key_exists('from_date', $json_data) && array_key_exists('to_date', $json_data)){
	$from_date = $json_data['from_date'];
	$to_date = $json_data['to_date'];
}

if(array_key_exists('item_name', $json_data)){
	$item_name = $json_data['item_name'];
}

if(array_key_exists('item_cat', $json_data)){
	$item_cat = $json_data['item_cat'];
}

$subquery_fields = [];
$subquery_fields['orders'] = "if(isnull(ordersTotal),0,ordersTotal) as orders";
$subquery_fields['requisitions'] = "if(isnull(reqsTotal),0,reqsTotal) as requisitions";
$subquery_fields['offers'] = "if(isnull(offersTotal),0,offersTotal) as offers";
$subquery_fields['spoilt'] = "if(isnull(spoiltTotal),0,spoiltTotal) as spoilt";

$filter_arr = [];
if(isset($from_date) && isset($to_date)){
	$filter_arr['date'] = "BETWEEN '$from_date' AND '$to_date' ";
}
if(isset($station)){
	$filter_arr['station'] = "Station = '$station' ";
}

if(!empty($filter_arr)){
	if(array_key_exists('date', $filter_arr) && array_key_exists('station', $filter_arr)){
		$ordwhere_clause = " where OrderDate ". implode("AND ", $filter_arr);
		$reqwhere_clause = " where DateRecieved ". implode("AND ", $filter_arr);
		$offrwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		$sptwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
	}
	
	if(!array_key_exists('date', $filter_arr) && array_key_exists('station', $filter_arr)){
		$ordwhere_clause = " where ".$filter_arr['station']. " AND month(OrderDate) = ". date('n');
		$reqwhere_clause = " where ".$filter_arr['station']. " AND month(DateRecieved) = ". date('n');
		$offrwhere_clause = " where ".$filter_arr['station']. " AND month(DateOccurred) = ". date('n');
		$sptwhere_clause = " where ".$filter_arr['station']. " AND month(DateOccurred) = ". date('n');
	}else{
		$ordwhere_clause = " where OrderDate ". implode("AND ", $filter_arr);
		$reqwhere_clause = " where DateRecieved ". implode("AND ", $filter_arr);
		$offrwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		$sptwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
	}
}else{
	$ordwhere_clause = " where month(OrderDate) = ". date('n');
	$reqwhere_clause = " where month(DateRecieved) = ". date('n');
	$offrwhere_clause = " where month(DateOccurred) = ". date('n');
	$sptwhere_clause = " where month(DateOccurred) = ". date('n');
}

$subqueries = [];
$subqueries['orders'] = " left join (select productno, sum(qty) as ordersTotal from orderdetails join orders on Order_No = OrderNo $ordwhere_clause group by productno) as ordersQty on Product_No = ordersQty.productno ";
$subqueries['requisitions'] = "left join (select ProductNo, sum(qtyrecieved) as reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no $reqwhere_clause group by ProductNo) as reqsQty on Product_No = reqsQty.productNo ";
$subqueries['offers'] = "left JOIN (select ProductNo, sum(qty) as offersTotal from offersdetails join offers on OffersID = offers.ID $offrwhere_clause group by ProductNo) as offersQty on Product_No = offersQty.productNo ";
$subqueries['spoilt'] = "left join (select ProductNo, sum(qty) as spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID $sptwhere_clause group by ProductNo) as spoiltQty on Product_No = spoiltQty.productNo ";

if(array_key_exists('rep_cols', $json_data)){
	if(is_array($json_data['rep_cols'])){
		$rep_cols = [];
		foreach($subquery_fields as $k => $v){
			if(in_array($k, $json_data['rep_cols'])){
				$rep_cols[$k] = $v;
			}
		}
		$rep_subqueries = [];
		foreach($subqueries as $k => $v){
			if(in_array($k, $json_data['rep_cols'])){
				$rep_subqueries[$k] = $v;
			}
		}
	}
}

$reportsql = "select product_no, if(salename='' or IsNull(salename),description,SaleName) as item, ". implode(",", $rep_cols). " from products left join items_sold on Product_No = ProductNo ". implode("",$rep_subqueries);
if(isset($item_name)){$reportsql = $reportsql. " where Product_No = '$item_name'";}
if(isset($item_cat)){$reportsql = $reportsql. " where Category = '$item_cat'";}
//echo $reportsql;

$res_records = new stdClass();
if(json_decode(dbConn($reportsql, array(), 'select'))->status == 1){
	$res_records->status = 1;
	$res_records->message = json_decode(dbConn($reportsql, array(), 'select'))->message;
	//$res_records->sql = $reportsql;
	echo json_encode($res_records);
}else{
	$res_records->message = json_decode(dbConn($reportsql, array(), 'select'))->message;
	echo json_encode($res_records);
}
?>