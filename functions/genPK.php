<?php
function genPK($table, $feild, $custom_str, $date_feild){
//This function is used to generate a custom Primary Key for some table insertions
	$pkey_sql = "SELECT $feild FROM `$table` ORDER BY $date_feild DESC LIMIT 1";
	//$rows = json_decode(dbConn($pkey_sql, array(), "select"))->numrows;
	$id_obj = json_decode(dbConn($pkey_sql, array(), "select"));//->message[0];//->Order_No;
	if($id_obj->status !== 0){
		if($id_obj->status == 2){
			$pkey = $custom_str."001";
		}else{
			foreach($id_obj->message[0] as $k => $v){
				//$rows_incr = $rows +1;
				$rows_incr = intval(substr($v, 3)) + 1;
				if(strlen($rows_incr) === 2 || strlen($rows_incr) === 1){
					$paded_key = str_pad($rows_incr, 3, "00", STR_PAD_LEFT);
				}
			}
			if(isset($paded_key)){$pkey = "$custom_str".$paded_key;}else{$pkey = "$custom_str".$rows_incr;}
		}
	}
	
	return $pkey;
	
	/*//$pkey_sql = "SELECT $feild FROM `$table`";
	$pkey_sql = "SELECT $feild FROM `$table` ORDER BY $date_feild DESC LIMIT 1";
	//$rows = json_decode(dbConn($pkey_sql, array(), "select"))->numrows;
	$id_obj = json_decode(dbConn($pkey_sql, array(), "select"))->message[0];//->Order_No;
	foreach($id_obj as $k => $v){
		
	
	//$rows_incr = $rows +1;
	$rows_incr = intval(substr($v, 3)) + 1;
	if(strlen($rows_incr) === 2 || strlen($rows_incr) === 1){
		$paded_key = str_pad($rows_incr, 3, "00", STR_PAD_LEFT);
	}
	}
	if(isset($paded_key)){$pkey = "$custom_str".$paded_key;}else{$pkey = "$custom_str".$rows_incr;}
	return $pkey;*/
}

?>
