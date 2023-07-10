<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';

//print_r($_GET['_data']);
$json_data = json_decode($_GET['_data'], true);
//print_r($json_data);
if($json_data['station'] != 'All'){
	//print_r($json_data['station']);
	$station = $json_data['station'];
}

if(array_key_exists('from_period', $json_data)){
	$from_period = $json_data['from_period'];
}


if(array_key_exists('to_period', $json_data)){
	$to_period = $json_data['to_period'];
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
$subquery_fields['requisitions'] = "if(isnull(reqsTotal),0,round(reqsTotal/MeasureSold)) as requisitions ";//"if(isnull(reqsTotal),0,if(reqsTotal > 1,round(reqsTotal), round(reqsTotal,2))) as requisitions";
$subquery_fields['offers'] = "if(isnull(offersTotal),0,offersTotal) as offers";
$subquery_fields['spoilt'] = "if(isnull(spoiltTotal),0,spoiltTotal) as spoilt";
$subquery_fields['missing'] = "if(isnull(missingTotal),0,round(missingTotal)) as missing";

$filter_arr = [];
if(isset($from_date) && isset($to_date)){
	if(isset($from_period) && isset($to_period)){
		$filter_arr['date'] = "BETWEEN '$from_date $from_period' AND '$to_date $to_period' ";
	}
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
		$missingwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		
		$ordwhere_clause_prev = " where ".$filter_arr['station']. " AND OrderDate < '$from_date $from_period' ";
		$reqwhere_clause_prev = " where ".$filter_arr['station']. " AND DateRecieved < '$from_date $from_period' ";
		$offrwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
		$sptwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
		$missingwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
	}
	
	if(!array_key_exists('date', $filter_arr) && array_key_exists('station', $filter_arr)){
		$ordwhere_clause = " where ".$filter_arr['station']. " AND month(OrderDate) = ". date('n');
		$reqwhere_clause = " where ".$filter_arr['station']. " AND month(DateRecieved) = ". date('n');
		$offrwhere_clause = " where ".$filter_arr['station']. " AND month(DateOccurred) = ". date('n');
		$sptwhere_clause = " where ".$filter_arr['station']. " AND month(DateOccurred) = ". date('n');
		$missingwhere_clause = " where ".$filter_arr['station']. " AND month(DateOccurred) = ". date('n');
		
		$ordwhere_clause_prev = " where ".$filter_arr['station']. " AND month(OrderDate) < ". date('n');
		$reqwhere_clause_prev = " where ".$filter_arr['station']. " AND month(DateRecieved) < ". date('n');
		$offrwhere_clause_prev = " where ".$filter_arr['station']. " AND month(DateOccurred) < ". date('n');
		$sptwhere_clause_prev = " where ".$filter_arr['station']. " AND month(DateOccurred) < ". date('n');
		$missingwhere_clause_prev = " where ".$filter_arr['station']. " AND month(DateOccurred) < ". date('n');
	}else{
		$ordwhere_clause = " where OrderDate ". implode("AND ", $filter_arr);
		$reqwhere_clause = " where DateRecieved ". implode("AND ", $filter_arr);
		$offrwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		$sptwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		$missingwhere_clause = " where DateOccurred ". implode("AND ", $filter_arr);
		
		$ordwhere_clause_prev = " where ".$filter_arr['station']. " AND OrderDate < '$from_date $from_period' ";
		$reqwhere_clause_prev = " where ".$filter_arr['station']. " AND DateRecieved < '$from_date $from_period' ";
		$offrwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
		$sptwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
		$missingwhere_clause_prev = " where ".$filter_arr['station']. " AND DateOccurred < '$from_date $from_period' ";
	}
}else{
	$ordwhere_clause = " where month(OrderDate) = ". date('n');
	$reqwhere_clause = " where month(DateRecieved) = ". date('n');
	$offrwhere_clause = " where month(DateOccurred) = ". date('n');
	$sptwhere_clause = " where month(DateOccurred) = ". date('n');
	$missingwhere_clause = " where month(DateOccurred) = ". date('n');
	
	$ordwhere_clause_prev = " where month(OrderDate) < ". date('n');
	$reqwhere_clause_prev = " where month(DateRecieved) < ". date('n');
	$offrwhere_clause_prev = " where month(DateOccurred) < ". date('n');
	$sptwhere_clause_prev = " where month(DateOccurred) < ". date('n');
	$missingwhere_clause_prev = " where month(DateOccurred) < ". date('n');
}

$offrwhere_clause .= " AND isDeleted <> 1 ";
$offrwhere_clause_prev .= " AND isDeleted <> 1 ";

$prev_subqueries = [];
$prev_subqueries['orders'] = " left join (select productno, sum(qty) as prevOrdersTotal from orderdetails join orders on Order_No = OrderNo $ordwhere_clause_prev group by productno) as prevOrdersQty on Product_No = prevOrdersQty.productno ";
$prev_subqueries['requisitions'] = "left join (select ProductNo, sum(qtyrecieved) as prevReqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no $reqwhere_clause_prev group by ProductNo) as prevReqsQty on Product_No = prevReqsQty.productNo ";
$prev_subqueries['offers'] = "left JOIN (select ProductNo, sum(qty) as prevOffersTotal from offersdetails join offers on OffersID = offers.ID $offrwhere_clause_prev group by ProductNo) as prevOffersQty on Product_No = prevOffersQty.productNo ";
$prev_subqueries['spoilt'] = "left join (select ProductNo, sum(qty) as prevSpoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID $sptwhere_clause_prev group by ProductNo) as prevSpoiltQty on Product_No = prevSpoiltQty.productNo ";
$prev_subqueries['missing'] = "left join (select ProductNo, sum(qty) as prevMissingTotal from missingdetails join items_missing on MissingID = items_missing.ID $missingwhere_clause_prev group by ProductNo) as prevMissingQty on Product_No = prevMissingQty.productNo ";


$subqueries = [];
$subqueries['orders'] = " left join (select productno, sum(qty) as ordersTotal from orderdetails join orders on Order_No = OrderNo $ordwhere_clause group by productno) as ordersQty on Product_No = ordersQty.productno ";
$subqueries['requisitions'] = "left join (select ProductNo, sum(qtyrecieved) as reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no $reqwhere_clause group by ProductNo) as reqsQty on Product_No = reqsQty.productNo ";
$subqueries['offers'] = "left JOIN (select ProductNo, sum(qty) as offersTotal from offersdetails join offers on OffersID = offers.ID $offrwhere_clause group by ProductNo) as offersQty on Product_No = offersQty.productNo ";
$subqueries['spoilt'] = "left join (select ProductNo, sum(qty) as spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID $sptwhere_clause group by ProductNo) as spoiltQty on Product_No = spoiltQty.productNo ";
$subqueries['missing'] = "left join (select ProductNo, sum(qty) as missingTotal from missingdetails join items_missing on MissingID = items_missing.ID $missingwhere_clause group by ProductNo) as missingQty on Product_No = missingQty.productNo ";
/*if(count($json_data['rep_cols']) == 4){
	array_unshift($json_data['rep_cols'], "start");
	$subqueries['start'] = " left join (select products.product_no, if(isnull(reqsTotal),0,reqsTotal) as prevReqs,if(isnull(ordersTotal),0,ordersTotal) as prevOrders,if(isnull(offersTotal),0,offersTotal) as prevOffers,if(isnull(spoiltTotal),0,spoiltTotal) as prevSpoilt, if(isnull(reqsTotal),0,round(reqsTotal/MeasureSold)) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal)) AS prevFinish
 from products left join items_sold on Product_No = ProductNo
 left join (select productno, sum(qty) as ordersTotal from orderdetails join orders on Order_No = OrderNo where orderdate < '2023-05-19 19:00:00' group by productno) as ordersQty on Product_No = ordersQty.productno
 left join (select ProductNo, sum(qtyrecieved) as reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no where DateRecieved < '2023-05-19 19:00:00' group by ProductNo) as reqsQty on Product_No = reqsQty.productNo
 left join (select ProductNo, sum(qty) as offersTotal from offersdetails join offers on OffersID = offers.ID where DateOccurred < '2023-05-19 19:00:00' group by ProductNo) as offersQty on Product_No = offersQty.productNo
 left join (select ProductNo, sum(qty) as spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID where DateOccurred < '2023-05-19 19:00:00' group by ProductNo) as spoiltQty on Product_No = spoiltQty.productNo) as Start on Product_No = Start.product_no";

}*/

if(array_key_exists('rep_cols', $json_data)){
	if(is_array($json_data['rep_cols'])){
		$rep_cols = [];
		foreach($subquery_fields as $k => $v){
			if(in_array($k, $json_data['rep_cols'])){
				$rep_cols[$k] = $v;
			}
		}
		$rep_prev_subqueries = [];
		foreach($prev_subqueries as $k => $v){
			if(in_array($k, $json_data['rep_cols'])){
				$rep_prev_subqueries[$k] = $v;
			}
		}
		
		foreach($subqueries as $k => $v){
			if(in_array($k, $json_data['rep_cols'])){
				$rep_subqueries[$k] = $v;
			}
		}
	}
}

function injectStartStr($arr){
	if(count($arr) == 5){
		return "if(if(isnull(prevReqsTotal),0,round(prevReqsTotal/MeasureSold)) - ((if(isnull(prevOrdersTotal),0,prevOrdersTotal) + if(isnull(prevOffersTotal),0,prevOffersTotal)) + if(isnull(prevSpoiltTotal),0,prevSpoiltTotal) + if(isnull(prevMissingTotal),0,round(prevMissingTotal))) < 0,0,if(isnull(prevReqsTotal),0,round(prevReqsTotal/MeasureSold)) - ((if(isnull(prevOrdersTotal),0,prevOrdersTotal) + if(isnull(prevOffersTotal),0,prevOffersTotal)) + if(isnull(prevSpoiltTotal),0,prevSpoiltTotal) + if(isnull(prevMissingTotal),0,round(prevMissingTotal)))) AS Start, ";
	}
}

function injectFinishStr($arr){
	if(count($arr) == 5){
		return ",((if(if(isnull(prevReqsTotal),0,round(prevReqsTotal/MeasureSold)) - ((if(isnull(prevOrdersTotal),0,prevOrdersTotal) + if(isnull(prevOffersTotal),0,prevOffersTotal)) + if(isnull(prevSpoiltTotal),0,prevSpoiltTotal) + if(isnull(prevMissingTotal),0,round(prevMissingTotal))) < 0,0,if(isnull(prevReqsTotal),0,round(prevReqsTotal/MeasureSold)) - ((if(isnull(prevOrdersTotal),0,prevOrdersTotal) + if(isnull(prevOffersTotal),0,prevOffersTotal)) + if(isnull(prevSpoiltTotal),0,prevSpoiltTotal) + if(isnull(prevMissingTotal),0,round(prevMissingTotal)))) + if(isnull(reqsTotal),0,round(reqsTotal/MeasureSold))) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal) + if(isnull(missingTotal),0,round(missingTotal)))) AS Finish";
	}
}

function injectPrevSubqueries($arr, $subquery_arr){
	if(count($arr) == 5){
		return implode("",$subquery_arr);
	}
}

$reportsql = "select product_no, if(salename='' or IsNull(salename),description,SaleName) as item, ". injectStartStr($json_data['rep_cols']) . implode(",", $rep_cols). injectFinishStr($json_data['rep_cols']) ." from products left join items_sold on Product_No = ProductNo ". injectPrevSubqueries($json_data['rep_cols'], $rep_prev_subqueries) . implode("",$rep_subqueries);
if(isset($item_name)){$reportsql = $reportsql. " where Product_No = '$item_name'";}
if(isset($item_cat)){$reportsql = $reportsql. " where Category = '$item_cat'";}else{$reportsql .= " where Category != 'Kitchen'";}
$reportsql .= " ORDER BY Category, Product_No";
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

/*
select product_no, if(isnull(reqsTotal),0,reqsTotal) as prevReqs,if(isnull(ordersTotal),0,ordersTotal) as prevOrders,if(isnull(offersTotal),0,offersTotal) as prevOffers,if(isnull(spoiltTotal),0,spoiltTotal) as prevSpoilt, if(isnull(reqsTotal),0,round(reqsTotal/MeasureSold)) - ((if(isnull(ordersTotal),0,ordersTotal) + if(isnull(offersTotal),0,offersTotal)) + if(isnull(spoiltTotal),0,spoiltTotal)) AS prevFinish
 from products left join items_sold on Product_No = ProductNo
 left join (select productno, sum(qty) as ordersTotal from orderdetails join orders on Order_No = OrderNo where orderdate < '2023-05-19 19:00:00' group by productno) as ordersQty on Product_No = ordersQty.productno
 left join (select ProductNo, sum(qtyrecieved) as reqsTotal from purchasedetails join purchaseorder on purchaseNo = purchase_no where DateRecieved < '2023-05-19 19:00:00' group by ProductNo) as reqsQty on Product_No = reqsQty.productNo
 left join (select ProductNo, sum(qty) as offersTotal from offersdetails join offers on OffersID = offers.ID where DateOccurred < '2023-05-19 19:00:00' group by ProductNo) as offersQty on Product_No = offersQty.productNo
 left join (select ProductNo, sum(qty) as spoiltTotal from spoiltdetails join items_spoilt on SpoiltID = items_spoilt.ID where DateOccurred < '2023-05-19 19:00:00' group by ProductNo) as spoiltQty on Product_No = spoiltQty.productNo

*/
?>
