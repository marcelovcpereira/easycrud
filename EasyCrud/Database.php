<?php

class Database{
	
	/*******************************************************************************
	 * DEFAULT DATABASE CONFIGURATION - PLEASE EDIT IT TO YOUR DATABASE CONNECTION,* 
         * You can connect passing DB parameters on the fly.                           *
	 *******************************************************************************/
	private static $server = "localhost";
	private static $port = "3306";
	private static $database = "projeto";
	private static $user = "root";
	private static $pass = "";

	/**
         * You can connect at runtime with any other database instance.
	 * Just pass the new Server configuration
         * @param type $server servername
         * @param type $port port number
         * @param type $database database scheme
         * @param type $user username
         * @param type $pass user password
         */ 
	public static function connect($server="",$port="",$database="",$user="",$pass="")
	{
                //If the user calls just connect() it will use the default
                //parameter values.
                if ($server === ""){
                    $server = Database::$server;
                    $port = Database::$port;
                    $database = Database::$database;
                    $user = Database::$user;
                    $pass = Database::$pass;
                }
               
                
		$link = mysql_connect($server . ':' .$port, $user, $pass);
			if (!$link) {
				die('Could not connect: ' . mysql_error());
			}else{
				mysql_select_db($database);
			}		
	}
	
	
	public static function getSchemaName(){ return Database::$database; }
	

        /**
         * Receives a table name and returns it's columns as an array list
         * @param type $tableName Name of the table
         * @return type array listing the columns for the given table
         */
	public static function getTableColumns($tableName){
		Database::connect();
		$returnArray = Array();
		$query = 'SHOW COLUMNS FROM ' . $tableName;
		$result = mysql_query($query);
		while($row = mysql_fetch_assoc($result)){
			$returnArray[] = $row;
		}
		return $returnArray;
	}
	
        
	public static function getFieldsNames($tableName){
		$fields = Database::getTableColumns($tableName);	
		$returnArray = Array();
		for($i = 0; $i < count($fields); $i++){		
			$returnArray[$i] = $fields[$i]['Field'];			
		}
		return $returnArray;
	}
	
	/**
	 * Generic function for insertion of a new row on a table. 
	 * @param table Name of the table
	 * @param $names Array of the tables' column names . 
	 * @param $values Array of values for the table columns
	 * example: insert('user', {'name','age'}, {'Carl','23'}); 
	 * The example above will insert a new row in a table named 'user'.
	 * The query will be "insert into account (name,age) values ('Carl','23');"
	 */
	public static function insert($table,$names,$values){
		Database::connect();
		$queryString = "INSERT INTO " . $table . " (" . implode(',',$names) . ") VALUES (" . implode(',',$values) . ");";
		
		$result = mysql_query($queryString);
		$_SESSION['dbGoodMessage'] = $result;
		if( $result ){			
			$_SESSION['databaseMessage'] = "New row Added";
		}else{			
			$_SESSION['databaseMessage'] = "Insert Failure:" . mysql_error() . '<br/>SQL:' . $queryString;
		}
	}
	
	/**
	 * Generic function for deletion of row(s) of a table. 
	 * @param table Name of the table
	 * @param $pks Name and Value of the primary keys of the table. The values indicate which row(s) will be deleted
	 * example: delete('account', {'id','11','number','25710'}); 
	 * The example above will delete a row in a table named 'account'.
	 * The query will be "delete from account where id = 11 and number = 25710;"
	 */
	public static function delete($table,$pks){
		Database::connect();
		$queryString = "DELETE FROM " . $table . " WHERE ";
		foreach( $pks as $pk ){
			$queryString .= $pk[0] . "= '" . $pk[1] . "' AND ";
		}
		$queryString .= "1=1;";
		
		$result = mysql_query($queryString);
		echo "1";
		$_SESSION['dbGoodMessage'] = $result;
		echo "2";
		if( $result ){	
			echo "3";
			$_SESSION['databaseMessage'] = "Row Successfully Deleted";
		}else{		
			echo "4";	
			$_SESSION['databaseMessage'] = "Delete Failure:" . mysql_error() . '<br/>On SQL:' . $queryString;
		}
	}

	public static function getTableForeignKeys($schemaName,$tableName){
		$returnArray = Array();
		Database::connect();
		
		
		$query = "select COLUMN_NAME,REFERENCED_TABLE_SCHEMA,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME from INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
		WHERE
		CONSTRAINT_NAME in
		(select CONSTRAINT_NAME from INFORMATION_SCHEMA.TABLE_CONSTRAINTS
		WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND
		TABLE_NAME = '$tableName' and
		TABLE_SCHEMA = '$schemaName') and
		CONSTRAINT_SCHEMA = '$schemaName' and
		TABLE_NAME = '$tableName'";

		$result = mysql_query($query);

		if( $result ){
			while($row = mysql_fetch_assoc($result)){
				$returnArray[] = $row;
			}
			return $returnArray;	
		}else{	return mysql_error();}
		
	}
	
	public static function insertWithUploadFiles($table,$fieldsNames,$fieldsValues,$filesNames,$filesContent){
		Database::connect();
		$query = "INSERT INTO " . $table . "(" . $fieldsNames . "," . $filesNames . ") values (";
		for($i = 0; $i< count($fieldsValues); $i++){
			if( $i == 0 ){
				$query .=  "'" .$fieldsValues[$i] . "'";
			}else{
				$query .= ",'".$fieldsValues[$i] . "'";
			}
			
		}
		
		for($i = 0; $i< count($filesContent); $i++){
			$query .= ",'" . $filesContent[$i]."'";		
		}
		$query .= ");";
		
		$result = mysql_query($query);
		$_SESSION['dbGoodMessage'] = $result;
		if( $result ){			
			$_SESSION['databaseMessage'] = "New row Added";
		}else{			
			$_SESSION['databaseMessage'] = "Insert Failure:" . mysql_error() . '<br/>SQL:' . $query;
		}
		
	}
}
?>
