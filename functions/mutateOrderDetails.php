<?php
//The function that adds the necessary "things" to the arrays of the order details, necessary for sql prepare and bind
//The function then calls the insertion function (dbConn) to insert each array i.e order detail
//It then returns the result of the insertion (success or failure)
function mutateOrderDetails($fieldsarr, $bindtypes, $pk, $sql){
	foreach($fieldsarr as $outerV){
		array_unshift($outerV, $bindtypes);
		$outerV['fkey'] = $pk;
		$ord_detconn_res = dbConn($sql, $outerV, "insert");
	}
	return $ord_detconn_res;
}
?>