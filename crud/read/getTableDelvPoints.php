<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db/conn.php';
$res_records = new stdClass();

$sql = "SELECT Description as pntName FROM `Tables` WHERE Status = 1";

echo dbConn($sql, array(), "select");
?>