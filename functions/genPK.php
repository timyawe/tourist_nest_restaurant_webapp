<?php
function genPK($table, $feild, $custom_str){
//This function is used to generate a custom Primary Key for some table insertions
	$pkey_sql = "SELECT $feild FROM `$table`";
	$rows = json_decode(dbConn($pkey_sql, array(), "select"))->numrows;
	
	$rows_incr = $rows +1;
	if(strlen($rows_incr) === 2 || strlen($rows_incr) === 1){
		$paded_key = str_pad($rows_incr, 3, "00", STR_PAD_LEFT);
	}
	
	if(isset($paded_key)){$pkey = "$custom_str".$paded_key;}else{$pkey = "$custom_str".$rows_incr;}
	return $pkey;
}

?>