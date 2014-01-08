<?php
require_once 'EasyCrud.php';


if( isset($_REQUEST['field']) && 
	isset($_REQUEST['table']) &&
	isset($_REQUEST['column'])&&
	isset($_REQUEST['path'])){

	$field = $_REQUEST['field'];
	$table = $_REQUEST['table'];
	$column = $_REQUEST['column'];
	$path = $_REQUEST['path'];
	
	$ec = new EasyCrud($table, $table, $path ,true);
	$ec->setInputToFill($field);
	$ec->setInputPrimaryKey($column);
	$ec->printTable();
	
	
}else{
	echo "NO";
}
?>
