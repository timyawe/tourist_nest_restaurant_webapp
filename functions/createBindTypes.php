<?php
function createBindTypes($arr){
	$bind_types = "";
	foreach($arr as $v){
		if(is_numeric($v)){
			$bind_types .= "i";
		}else if(is_string($v)){
			$bind_types .= "s";
		}else if(is_float($v) || is_double($v)){
			$bind_types .= "d";
		}
	}
	return $bind_types;
}
?>
