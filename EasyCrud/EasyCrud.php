<?

/**--------------------------------------------------------
 * -EasyCrud.php v1.0 - by Marcelo Victor Carneiro Pereira-
 * --------------------------------------------------------
 * 
 * 
 * 
 * 
 * 
 * 
 */
 
session_start();
require_once 'Field.php';
require_once 'Database.php';
require_once 'Lister.php';
require_once 'Util.php';

		
//If the insert has files to upload, so it will come in a post request.		
if( isset($_POST['uploadingFile']) && $_POST['action'] == "INSERT_FILE"){
	$isUploading = $_POST['uploadingFile'];

	if( $isUploading ){
		$table = $_POST['table'];
		$action = $_POST['action'];
		$filesNames = explode(',',$_POST['files']);
		$fieldsNames = explode(',',$_POST['fieldsNames']);
		$fieldsValues = Array();
		$filesArray = Array();
		$filesContent = Array();
		
		
		foreach($fieldsNames as $name){
			$fieldsValues[] = $_POST[$name];
		}
		foreach($filesNames as $fn){
			//If the user specified a file for this field
			if( isset($_FILES[$fn]['tmp_name']) && 
				isset($_FILES[$fn]['name']) &&
				isset($_FILES[$fn]['size']) && 
				isset($_FILES[$fn]['type']) &&
				$_FILES[$fn]['size'] > 0 ){
				$filesArray[] = $_FILES[$fn];
				$tmpName  = $_FILES[$fn]['tmp_name'];						
				$fileTitle = $_FILES[$fn]['name'];
				$fileSize = $_FILES[$fn]['size'];
				$fileType = $_FILES[$fn]['type'];
				$fp      = fopen($tmpName, 'r');
				$content = fread($fp, filesize($tmpName));
				$content = addslashes($content);
				fclose($fp);			
				$eCDelimiter = "__EASYCRUD__";
				$filesContent[] = $fileTitle . $eCDelimiter . $content . $eCDelimiter . $fileType . $eCDelimiter . $fileSize;
			}else{ $filesContent[] = ''; }
		}
		
		
		Database::insertWithUploadFiles($table,$_POST['fieldsNames'],$fieldsValues,$_POST['files'],$filesContent);

	}
	
	unset($_POST['uploadingFile']);
	unset($_POST['action']);

}
		
//Resolving a request to show the table		
if( isset($_REQUEST['printTable']) && $_REQUEST['printTable'] != '' ){			
			
			unset($_REQUEST['printTable']);	
			
			echo $_SESSION['refreshedTable'];
			
			echo EasyCrud::printMessages();
			
						
			unset($_SESSION['refreshedTable']);
			exit();		
}

//Resolving request to show a FK Search Table
if( isset($_REQUEST['action']) && $_REQUEST['action'] == "FK_SEARCH" ){
	if( isset($_REQUEST['field']) && 
		isset($_REQUEST['table']) &&
		isset($_REQUEST['column'])&&
		isset($_REQUEST['path'])){
			$field = $_REQUEST['field'];
			$table = $_REQUEST['table'];
			$column = $_REQUEST['column'];
			$path = $_REQUEST['path'];
			$criteria = $_REQUEST['criteria'];
			$order = $_REQUEST['order'];
			$orderType = $_REQUEST['orderType'];
			$pageNumber = $_REQUEST['pageNumber'];
			
			//If true, return only the Table
			//If false, return the search button, search form and the table
			$tableOnly = $_REQUEST['tableOnly'];
			
			
			$ec = new EasyCrud($table, $table, $path ,true);
			$ec->setInputToFill($field);
			$ec->setInputPrimaryKey($column);
			
			//updating attributes
			if( $criteria != "" && $criteria != null && $criteria != 'null'){
				$ec->criteria = $criteria;
			}
			if( $order != "" && $order != null && $order != 'null' ){
				$ec->orderBy = $order;
			}
			if( $orderType != "" && $orderType != null && $orderType != 'null' ){
				$ec->orderType = $orderType;
			}
			if( $pageNumber != "" && $pageNumber != null && $pageNumber != 'null' ){
				$ec->pageNumber = $pageNumber;
			}
			
			
			if( $tableOnly == 'true' ){
				echo $ec->refreshTable();
			}else{
				$ec->printTable();
			}
			exit();
			
		}else{
			echo "Error, missing REQUEST Variables";
		}
}




class EasyCrud{

	
	//Table being CRUDed
	var $tableName; 
	
	//Object name representation of Table
	var $objectName;
	
	//Number of rows per page - Default: 10
	var $pageSize = 10; 
	
	//Object Lister
	var $lister = NULL;
	
	var $columnsNames = NULL;
	
	//All table fields. This array is indexed by the columns names. Ex:  $fields['id'] or $fields['color'],
	//considering that 'id' and 'color' are columns of the current Table.
	//The elements of this array are "Field" typed. See Field.php.
	var $fields = Array();
	
	var $tableStyleSheet = 'EasyCrud.css';
	
	var $easyCrudPath;
	
	var $criteria = NULL;	
	var $pageNumber = 1;	
	var $orderBy = NULL;
	var $orderType = "ASC";
	
	var $viewOnly = false;
	var $inputToFill = null;
	var $inputPrimaryKey = null;
	
	public function __construct($tableName, $objectName,$path="/", $view = false){		
		$this->tableName = $tableName;
		$this->objectName = $objectName;
		$this->easyCrudPath	= $path;
		$this->viewOnly = $view;
		$this->setEncoding();
		$this->initializeFields();	
		$this->columnsNames = $this->getTableColumnsNames();
		$this->includeScripts();
		
		if( !$this->viewOnly ){	
			$this->createDeleteDialog();					
		}
	}
	public function includeScripts(){
		
		$this->includeJQuery();
		$this->includeEasyCrudScript();
		$this->includeTableStyle();
	}
	
	public function setOrderType($ot){
		if( $ot != "" && $ot != null && $ot != 'null' ){
				$this->orderType = $ot;
		}
	}
	
	public function setPageNumber($pn){
		if( $pn != "" && $pn != null && $pn != 'null' && $pn > 0){
				$this->pageNumber = $pn;
		}
	}
	
	public function setEncoding(){
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html;\" />";
	}
	
	public function createDeleteDialog(){
		echo "<div id=\"deleteConfirm_". $this->tableName ."\"  title=\"Delete this Row from the table ".$this->tableName."?\" style=\"display:none;\">
				<span class=\"ui-icon ui-icon-alert\" style=\"float:left; margin:0 7px 20px 0;\"></span><span>This row will be permanently deleted and cannot be recovered. Are you sure?</span>
			  </div>";	
	}	
	
	public function includeTableStyle(){
		echo "<link href=\"" . $this->easyCrudPath . "css/" . $this->tableStyleSheet ."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />";
	}
	
	public function includeJQuery(){
		echo "<script type=\"text/javascript\" src=\"". $this->easyCrudPath . "jquery/js/jquery-1.8.4.js\"></script>";
		echo "<script type=\"text/javascript\" src=\"". $this->easyCrudPath . "jquery/js/jquery-ui-1.8.4.js\"></script>";
		echo "<link href=\"". $this->easyCrudPath . "jquery/css/ui-lightness/jquery-ui-1.8.4.custom.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />";
	}
	
	public function includeEasyCrudScript(){
		echo "<script type=\"text/javascript\" src=\"". $this->easyCrudPath . "js/EasyCrud.js\"></script>";
		echo "<script> easyCrudPath = \"{$this->easyCrudPath}\"; </script>";
	}
	
	public function initializeFields(){		
		$columns = Database::getTableColumns($this->tableName);
		for($i = 0; $i < count($columns); $i++){
			$column = $columns[$i];
			$this->fields[ $column['Field'] ] = new Field( $column['Field'], $column['Type'], true, $column['Key'],$column['Null'],$column['Extra'] );	
		}			
		$this->setTableForeignKeys();
	}

	//Set the Alias name for the column
	public function setAlias($columnName,$alias){
		$this->fields[$columnName]->setAlias($alias);
		
	}
	
	public function setPrimaryKeyVisibility($bool){
		foreach($this->fields as $field){
			if( $field->isPrimaryKey() ){
				$field->setVisible($bool);
			}
		}
	}
	
	//Creates a callback function to process the field value before its
	//insertion on table.
	public function setInsertCallbackFunction($columnName,$func){
		$field = $this->fields[$columnName];
		$field->setInsertCallbackFunction($func);
		
	}
	
	//Creates a callback function to process the field value before showing
	//it on the table.
	public function setShowCallbackFunction($columnName,$func){
		$field = $this->fields[$columnName];
		$field->setShowCallbackFunction($func);
		
	}
	
	//Makes the fieldName a password field
	public function setPasswordField($fieldName){
		$this->fields[$fieldName]->setPassword(true);
	}
	
	
	public function getInsertFieldsNames(){
		$returnArray = Array();
		foreach($this->fields as $field){
			//The Field can't be a PK..or ...it's a PK, but not a AutoIncrement one
			if(!$field->isPrimaryKey() || ($field->isPrimaryKey() && !$field->isAutoIncrement()) ){
				$returnArray[] = $field->getName();
			}
		}
		return $returnArray;
	}
	
	public function getInsertFieldsValues(){
		$returnArray = Array();
		foreach($this->fields as $field){
			if( !$field->isPrimaryKey() ){
				$value = "";
				if( $field->isDate() ){
					$value = "str_to_date('" . $field->getProcessedValue($_REQUEST[$field->getName()]) . "','%d/%m/%Y')";
				}else{
					$value = "'" . $field->getProcessedValue($_REQUEST[$field->getName()]) . "'";
				}
				$returnArray[] = $value;
			}
		}
		return $returnArray;
	}
	
	public function checkInsertFieldsValues(){
		$returnMessage = "";
		
		foreach($this->fields as $field){
			$value = "";
			if( !$field->isPrimaryKey() ){	
				$value = trim($_REQUEST[$field->getName()]);
				//If the field cannot be NULL...
				if( !$field->isNullable() ){
					//...and the user didn't fill out...
					if( $value == NULL || $value == "" ){
						//...the message is showed
						$returnMessage .= "<b> " . $field->getAlias() . "</b> cannot be empty. <br/>";
					}
				}
			}else{
				if( !$field->isAutoIncrement() ){
					$value = trim($_REQUEST[$field->getName()]);
					if( $value == null || $value == "" ){
						$returnMessage .= "<b>" . $field->getAlias() . "</b> cannot be empty. <br/>";
					}
				}
			}
		}
		return $returnMessage;	
	}
		
	private function executeRequest(){
		$action = $_REQUEST['action'];
		$table = $_REQUEST['table'];		
		$readonly = $_REQUEST['readonly'];
		
		if( $this->tableName == $table ){			
			unset($_REQUEST['action']);	
			
			if( $action == "INSERT" ){	
				//Get the key columns names
				$columnsNames = $this->getInsertFieldsNames();
				
				//Checks if the values are valid
				$verifyValues = $this->checkInsertFieldsValues();
				
				//Get the params values for each table column
				$columnsValues = $this->getInsertFieldsValues();
				
				//If there were no problem with the fields values... 
				if( $verifyValues == "" ){
					//...Try to execute the insert command					
					$this->executeInsert($table,$columnsNames,$columnsValues);
				}else{
					//Inform that the operation failed					
					$this->generateInsertFailureMessage($verifyValues,false);									
				}
			}else if( $action == "ORDER" ){								
				
				$this->orderBy = $_REQUEST['orderColumn'];
				$this->criteria = $_REQUEST['searchCriteria'];
				$this->pageNumber = $_REQUEST['pageNumber'];
				$this->orderType = $_REQUEST['orderType'];
				
			}else if( $action == "DELETE" ){
				$pksString = $_REQUEST['pks'];
				$temp = explode(',',$pksString);
				$pks = Array();
				foreach($temp as $t){
					$pks[] = explode('=',$t);
				}
				$this->executeDelete($table,$pks);
			}
			$_SESSION['refreshedTable'] = $this->refreshTable();
		}
	}	
	
	public function changeOrderType(){	
		if( $this->orderType == "ASC" ){
			$this->orderType = "DESC";
		}else{
			$this->orderType = "ASC";
		}	
		if( $_SESSION['orderType'] == "ASC" ){
			$_SESSION['orderType'] = "DESC";
		}else{
			$_SESSION['orderType'] = "ASC";
		}		
	}
	
	public static function printMessages(){
		$returnString = "";
			
		if( isset($_SESSION['databaseMessage']) ){		
			EasyCrud::generateInsertFailureMessage($_SESSION['databaseMessage'],$_SESSION['dbGoodMessage']);
			unset($_SESSION['databaseMessage']);
			unset($_SESSION['dbGoodMessage']);
		}
			
			
		if( isset($_SESSION['EasyCrudMessage']) ){
			$returnString .= $_SESSION['EasyCrudMessage'];
			unset($_SESSION['EasyCrudMessage']);
		}
				
		unset($_SESSION['databaseMessage']);
		unset($_SESSION['bdGoodMessage']);
		return $returnString;
	}
	
	public static function generateInsertFailureMessage($msg,$good){
		$class = "";
		$message = "";
		if( $good ){
			$class = "<div class=\"goodMessage\">";
			$message = $class . $msg . "</div>";
		}else{
			$class = "<div class=\"badMessage\">";
			$message = $class . "<div class=\"databaseMsg\">The row was not inserted! ";
			$message .= "<a href=\"javascript: changeVisibility('error_".$_REQUEST['table']."');\">Show Errors</a><div class=\"errorTR\" id=\"error_".$_REQUEST['table']."\" style=\"display:none;\">";
			$message .= $msg . "</div></div></div>";
		}		
		
		$_SESSION['EasyCrudMessage'] = $message;		
	}
	
	private function executeInsert($table,$columnsNames,$columnsValues){
		Database::insert($table,$columnsNames,$columnsValues);
	}
	
	private function executeDelete($table,$pks){
		Database::delete($table,$pks);
	}
	
	private function getPrimaryKeyNames($fields){
		$returnArray = Array();
		$index = 0;
		for($i = 0; $i < count($fields); $i++){
			if( $fields[$i]['Key'] == "PRI" ){
				$returnArray[$index++] = $fields[$i];
			}
			
		}
		return $returnArray;
	}
	
	private function getParams($fieldsNames){
		$returnArray = Array();
		for($i = 0; $i < count($fieldsNames); $i++){
			$returnArray[$i] = $_REQUEST[$fieldsNames[$i]];
		}
		
		return $returnArray;
	}
	
	private function getFieldsNames($fields){
		$returnArray = Array();
		for($i = 0; $i < count($fields); $i++){		
			$returnArray[$i] = $fields[$i]['Field'];			
		}
		return $returnArray;
	}
		
	private function removePrimaryKey($fields){
		$returnArray = Array();
		$index = 0;
		for($i = 0; $i < count($fields); $i++){
			if( $fields[$i]['Key'] != "PRI" ){
				$returnArray[$index++] = $fields[$i];
			}			
		}
		return $returnArray;
	}
	
	public function setInputToFill($fN){
		$this->inputToFill = $fN;
	}
	
	//Prints the table
	public function printTable(){		
		$returnString = "";
		if( isset($_REQUEST['action']) && $_REQUEST['action'] != '' && $_REQUEST['action'] != "INSERT_FILE"){				
			$this->executeRequest();								
		}		
		
		
		//Prints the button that toggles the search form visibility
		$returnString .= $this->printSeachButton();
		
		if( !$this->viewOnly ){	
			//Prints the button that toggles the insert form visibility
			$returnString .= $this->printInsertButton();		
		}	
		//Prints the search form
		$returnString .= $this->printSearchForm();
		
		if( !$this->viewOnly ){	
			//Prints the "Insert New Row" form
			$returnString .= $this->printInsertForm();
		}	
		
		$divId = $this->tableName . "_div";
		if( $this->viewOnly ){
			$divId = "fk_" . $this->tableName . "_div";
		}
		//Creates the DIV that contains the table
		$returnString .= "<div id=\"$divId\">";
		
			//Prints the table inside the DIV
			$returnString .= $this->refreshTable();
			
		//Closes the DIV that contains the table
		$returnString .= "</div>";
		
		if( $this->viewOnly ){
			$returnString .= $this->printCloseFKSearhcDiv();
		}
		
		$returnString .= "<br/>";
	
		echo $returnString;
	}
	
	public function printCloseFKSearhcDiv(){
		$returnString = "";
		$returnString .= "<input type=\"button\" value=\"Close\" onclick=\"changeVisibility('search_fk_".$this->tableName . "_" . $this->inputPrimaryKey."')\" />";
		return $returnString;	
	}
	
	public function printSeachButton(){
		$divName = $this->tableName;
		if( $this->viewOnly ){
			$divName = "fk_" . $divName;
		}
		$returnString = "";
		$returnString .= "<input type=\"button\" value=\"Search ".$this->objectName."\" onclick=\"toggleSearchForm('". $divName ."')\" />";
		return $returnString;
	}
	
	public function printInsertButton(){
		$returnString = "";
		$returnString .= "<input type=\"button\" value=\"New ".$this->objectName."\" onclick=\"toggleInsertForm('".$this->tableName ."')\" />";
		return $returnString;
		
	}
	
	public function printPagination($lister){
		/**Variables*/
		
		//Actual page number
		$pageNumber = (int)$this->pageNumber;
		
		//Total number of pages
		$pages = $this->lister->numberOfPages;
		
		$rows = (int)$this->lister->numberOfObjects;
		
		//Number of the previous page
		$prPage = $pageNumber-1;
		
		//number of the next page
		$nePage = $pageNumber+1;
		
		//criteria string			
		$criteria = ($this->criteria == null || $this->criteria == "")? "" : $this->criteria;
		$criteria = addslashes($criteria);
		
		//ordered column
		$order = ($this->orderBy == null || $this->orderBy == "")? "" : $this->orderBy;
		
		//type of ordering (ASC or DESC)
		$orderType = $this->orderType;
		
		//request receptor URL
		$url = $_SERVER['PHP_SELF'];	
			
		//Name of the table		
		$tableName = $this->tableName;
		
		//Name of the div that contains the table code
		$divID = $tableName . "_div";		
		
		//Name of the CSS class for the FIRST , < , > and LAST pagination links
		$firstClass ="paginationClass";
		$prevClass ="paginationClass";
		$nextClass ="paginationClass";
		$lastClass ="paginationClass";	
		
		//Updating IDs in case its a readonly table (FK table search)
		if( $this->viewOnly ){
				$divID = "fk_" . $divID;
		}
		
		//Actions that will be triggered when user click on FIRST, < , > and LAST pagination links
		if( !$this->viewOnly ){
			$firstOnClick = "sendSearchRequest('$url','$tableName','$criteria','$order','$orderType','1','$divID');";
			$prevOnClick = "sendSearchRequest('$url','$tableName','$criteria','$order','$orderType','$prPage','$divID');";
			$nextOnClick = "sendSearchRequest('$url','$tableName','$criteria','$order','$orderType','$nePage','$divID');";
			$lastOnClick = "sendSearchRequest('$url','$tableName','$criteria','$order','$orderType','$pages','$divID');";
		}else{
			//path,fieldName,tableName,columnName,criteria,divName,tableOnly
			$fieldName = $this->inputToFill;
			$columnName = $this->inputPrimaryKey;
			$firstOnClick = "sendFKSearchRequest('$url','$fieldName','$tableName','$columnName','$criteria','$order','$orderType','1','$divID',true);";
			$prevOnClick = "sendFKSearchRequest('$url','$fieldName','$tableName','$columnName','$criteria','$order','$orderType','$prPage','$divID',true);";
			$nextOnClick = "sendFKSearchRequest('$url','$fieldName','$tableName','$columnName','$criteria','$order','$orderType','$nePage','$divID',true);";
			$lastOnClick = "sendFKSearchRequest('$url','$fieldName','$tableName','$columnName','$criteria','$order','$orderType','$pages','$divID',true);";
		}
		
		//Initialization of the return String of this function
		$returnString = "<span class=\"paginationRow\" >";
		
		/**Validations*/
		
			
		
		//Don't let previous page get under 1
		if( $prPage < 1 ){
			$prPage = 1;
		}
		
		//Don't let next page get over the total number of pages
		if( $nePage > $pages ){
			$nePage = $pages;
		}
		
		//If theres no previous page, don't let user click on it 
		if( $prPage == $pageNumber ){
			$prevClass = "selectedPaginationClass";
			$prevOnClick = ""; 
		}
		
		//If theres no next page, don't let user click on it 
		if( $nePage == $pageNumber ){
			$nextClass = "selectedPaginationClass";
			$nextOnClick = "";
		}
		
		//If this is the last page, don't let user click on LAST link 
		if( $pageNumber == $pages ){
			$lastClass = "selectedPaginationClass";
			$lastOnClick = "";
		}
		
		
		//If this is the first page, don't let user click on FIRST link 
		if( $pageNumber == 1 ){
			$firstClass = "selectedPaginationClass";
			$firstOnClick = "";
		}
		
		$startingRow = min((($this->pageNumber-1)*$this->pageSize)+1,$rows);
		$endingRow = min($pageNumber*$this->pageSize,$rows);
		
		$returnString .= "<span class=\"selectedPaginationClass\" >$rows result(s). Showing $startingRow to $endingRow.</span><br/>";
		
		//Printing "FIRST and <"
		$returnString .= "<span class=\"$firstClass\" onClick=\"$firstOnClick\">First</span>&nbsp";
		$returnString .= "<span class=\"$prevClass\" onClick=\"$prevOnClick\" ><</span>&nbsp";
		//Printing pages
		for($i = 0; $i < $lister->getNumberOfPages(); $i++){
			$number = $i+1;
			$spanClass = "paginationClass";
			if( !$this->viewOnly ){
				$onClick = "sendSearchRequest('$url','$tableName','$criteria','$order','$orderType','$number','$divID');";
			}else{
				$fieldName = $this->inputToFill;
				$columnName = $this->inputPrimaryKey;
				//This must not have an FK_ before its name, cause this parameter is the correct name of 
				//the database TABLE to search
				//$tN = $this->tableName;
				//path,fieldName,tableName,columnName,criteria,order,orderType,pageNumber,divName,tableOnly
				$onClick = "sendFKSearchRequest('$url','$fieldName','$tableName','$columnName','$criteria','$order','$orderType','$number','$divID',true);";
			}
			if( $this->pageNumber == $number ){
				$spanClass = "selectedPaginationClass";
				$onClick = "";
			}
			$returnString .= "<span class=\"$spanClass\" onClick=\"$onClick\">$number</span>&nbsp";
		}
		//Printing "> and LAST"
		$returnString .= "<span class=\"$nextClass\" onClick=\"$nextOnClick\" >></span>&nbsp";
		$returnString .= "<span class=\"$lastClass\" onClick=\"$lastOnClick\" >Last</span>&nbsp";
		
		return $returnString . "</span>";
	}
	
	public function setPageSize($ps){
		if( $ps > 0 ){
			$this->pageSize = $ps;
		}else{
			$this->pageSize = 10;
		}
	}
	
	//HTML code for the table
	public function refreshTable(){
		
		$returnString = "";
		
		$this->lister = new Lister($this->tableName,$this->pageSize,$this->pageNumber,$this->criteria,$this->orderBy,$this->orderType);			
		
		$returnString .= $this->printPagination($this->lister);
			
		//Prints the table head
		$returnString  .= $this->printTableHead();	
			//If the table is empty, show the message
			if( $this->lister->getSize() < 1 ){
				$returnString .= "<tr><td colspan=\"" . $this->getColspan() . "\">No Rows Returned</td></tr>";
			}
			
			
		//Prints the table Body
		$returnString  .= $this->printTableBody();
		
		$returnString .= $this->printPagination($this->lister);	

		return $returnString;
	}
	
	private function getColspan(){
		//"+1" because theres an extra column besides the table columns, the "delete" column
		return count($this->columnsNames)+1;
	}
	
	private function getTableColumnsNames(){
		$columns = Database::getTableColumns($this->tableName);	
		$columnsNames = $this->getFieldsNames($columns);
		return $columnsNames;
	}
	
	private function printTableHead(){		
		$columns = $this->getTableColumnsNames();
		$tableId = $this->tableName . "_table";
		$divId = $this->tableName;
		$tableClass = "listTable";
		if( $this->viewOnly ){
			$tableId = "fk_" . $tableId;
			$divId = "fk_" . $divId;
			$tableClass = "fk_" . $tableClass;
		}		
		
		$returnString = "<table id=\"$tableId\" class=\"$tableClass\"><tr><td id=\"". $this->tableName ."_td\" colspan=\"" . $this->getColspan() . "\" class=\"tableNameRow\">". $this->objectName ." Search</td></tr><tr>";
		for($i = 0; $i < count($columns); $i++){
			$class = "class=\"";
			$field = $this->fields[$columns[$i]];
			if( $field->isForeignKey() ){$class .= "fk ";}
			else if( $field->isPrimaryKey() ){$class .= "pk ";}			
			
			if( $this->orderBy == $field->getName() ){
				if( $this->orderType == "ASC" ){
					$class .= "downOrder";
				}else{
					$class .= "upOrder";
				}
			}	
			$class .= "\"";		
			$criteria = addslashes($this->criteria);
			$order = $field->getName();
			$pageNumber = $this->pageNumber;
			$orderType = ($this->orderType == "ASC")? "DESC": "ASC";
			$url = $_SERVER['PHP_SELF'];
			if( !$this->viewOnly ){
				$extras = "onClick=\"sendSearchRequest('$url','$divId','$criteria','$order','$orderType','$pageNumber','".$divId."_div');\"";
			}else{
				$fieldName = $this->inputToFill;
				$columnName = $this->inputPrimaryKey;
				$tbNm = $this->tableName;				
				$extras = "onClick=\"sendFKSearchRequest('$url','$fieldName','$tbNm','$columnName','$criteria','$order','$orderType','$pageNumber','".$divId."_div',true);\"";
			}
			
			$returnString .= "<th $class $extras>{$field->getAlias()}</th>";
		}
		
		//If is not a readOnly table, make an empty room
		//for the delete column
		if( !$this->viewOnly ){
			$returnString .= "<th></th>";
		}
		$returnString .= "</tr>";
		
		return $returnString;
	}
	
	public function setInputPrimaryKey($colName){
		$this->inputPrimaryKey = $colName;
	}	
	
	/*
	 * Prints the Table Body that contains the database
	 * retrieved data. 
	 */
	private function printTableBody(){		
		//Size of the Array of Rows
		$size = count($this->lister->getObjectArray());
		
		//Array of rows returned by the search
		$objArray = $this->lister->getObjectArray();
		
		//Columns names
		$columns = $this->columnsNames;
		
		$divId = $this->tableName . "_div";
		
		if( $this->viewOnly ){
			$divId = "fk_" . $divId;
		}
		
		$returnString = "";
		for($i = 0; $i < $size; $i++){
			$rowClass = ($i%2)?'tableRowOdd':'tableRowEven';
			$obj = $objArray[$i];
		
			$returnString .= "<tr class=\"$rowClass\">";			
			
			$cellOnClick = "";
					
			//If its a readOnly Table, when the cell is clicked,
			//fill the value of the targetInput with this PrimaryKey			
			if( $this->viewOnly ){
				$cellOnClick = "onClick=\"ge('".$this->inputToFill."').value = '".$obj[$this->inputPrimaryKey]."'\"";
			}
			
			for($j = 0; $j < count($columns); $j++){
				$field = $this->fields[$columns[$j]];
				if( $field->isFile() ){
					$fileInfo = explode('__EASYCRUD__',$field->getShowValue($obj[$columns[$j]]));
					if( count($fileInfo) > 0 && !empty($fileInfo)){
						$fileTitle = $fileInfo[0];
						@$fileContent = $fileInfo[1];
						@$fileType = $fileInfo[2];
						@$fileSize = number_format(($fileInfo[3]/1024),2) . "KB(s)";						
						$returnString .= "<td $cellOnClick> Name:". $fileTitle . "<br/> Type:" . $fileType . "<br/> Size:" . $fileSize ."</td>";					
					}else{
						$returnString .= "<td $cellOnClick>File Content Ommited</td>";					
					}
				}else{				
					$returnString .= "<td $cellOnClick>". $field->getShowValue($obj[$columns[$j]]) ."</td>";						
				}
			}									
			
			//If is not a readOnly table, print the DELETE cell
			if( !$this->viewOnly ){	
				$pkColumns = $this->getTablePrimaryKeyColumns();
				$pkValues = Array();
			
				foreach($pkColumns as $pk){
					$pkValues[] = $obj[$pk];			
				}	
				$deleteParams = $this->createDeleteRequestParams($pkColumns,$pkValues);	
				$returnString .= "<td class=\"deleteCell\" onclick=\"showDeleteConfirmation('". $_SERVER['PHP_SELF'] . "','" . $this->tableName . "','". $deleteParams ."','".$divId."')\">Delete</td>";			
			}
			
			$returnString .= "</tr>";
		}
		$returnString .= "</table>";
		
		return $returnString;
	}
	
	public function createDeleteRequestParams($pkColumns,$pkValues){
		$returnString = "";
		if( count($pkColumns) == count($pkValues) ){
			for( $i = 0; $i < count($pkColumns); $i++ ){
				if($returnString == ""){
					$returnString .= $pkColumns[$i] . "=" . $pkValues[$i];
				}else{
					$returnString .= "," . $pkColumns[$i] . "=" . $pkValues[$i];
				}
			}
		}else{						
		}
		
		return $returnString;
	}
	
	/*
	 * Returns an array containing all the columns names
	 * that compose the table primary key
	 */
	public function getTablePrimaryKeyColumns(){
		$pks = Array();
		foreach($this->fields as $field){
			if( $field->isPrimaryKey() ){
				$pks[] = $field->getName();
			}
		}
		return $pks;
	}
	
	private function printSearchForm(){
		$tableDivId = $this->tableName;
		$formID = $this->tableName . "_searchForm";
		$divID = $this->tableName . "_searchDiv";
		$readOnlySearch = 'false';
		
		if( $this->viewOnly ){
			$formID = "fk_" . $formID;
			$tableDivId = "fk_" . $tableDivId;
			$divID = "fk_" . $divID;
		}
		$tableDivId = $tableDivId . "_div";
		
		$returnString = "";
		$returnString .= "<div id=\"$divID\" style=\"display:none;\">";
		$returnString .= "<form id=\"$formID\" action=# method=post> <br/>";
		$returnString .= "<table class=\"searchForm\">";
		$returnString .= "<tr><td colspan=\"2\"><center>Search ".$this->objectName."</center></td></tr>";	
		
		$columnsNames = $this->getTableColumnsNames();
		$orderType = $this->orderType;
		$stopRequests = "if(runningRequest){request.abort();}";		
		$searchRequest = "$stopRequests var crit = generateCriteria('$formID'); sendSearchRequest('". $_SERVER['PHP_SELF'] ."','". $this->tableName."',crit,null,'$orderType','1','$tableDivId');";
		if( $this->viewOnly ){
			$searchRequest = "var crit = generateCriteria('$formID');sendFKSearchRequest('".$_SERVER['PHP_SELF']."','{$this->inputToFill}','".$this->tableName."','".$this->inputPrimaryKey."',crit, null,'$orderType','1','".$tableDivId."',true);";
		}
		for($i = 0; $i < count($columnsNames); $i++){			
			$field = $this->fields[$columnsNames[$i]];
			$fieldID = "search_". $field->getName() ."_input";
			//Is the field an editable field?
			$editable = "";
			
			$visible = true;
			
			//Type of the html input field
			$type = "text";
			
			//attribute used by the Javascript: "generateCriteria" function
			//used to differ text fields from number fields
			$title = "text";						
			
			
			if( $this->viewOnly ){
				$readOnlySearch = "true";
				$fieldName = $this->inputToFill;
				$columnName = $this->inputPrimaryKey; 
				$fieldID = "fk_" . $fieldID;				
			}else{
				$readOnlySearch = "false";
				$fieldName = null;
				$columnName = null;
			}
			
			//On Keypress get the actual field value
			$onKeyPress = "onKeyPress=\"actualValue = ge('$fieldID').value;\"";		
			
			//On Keyup make an auto search if the field value has changed
			$onKeyUp = "onKeyUp=\"startSearchTiming('$formID','".$_SERVER['PHP_SELF']."','".$this->tableName."',this.form,'$readOnlySearch','$fieldName','$columnName','$tableDivId');\"";
			
			//If is a number Field, validate the input
			//and do not execute auto search
			if( $field->isIntNumber() ){
				$onKeyPress = "onKeyPress=\"actualValue = ge('search_". $field->getName() ."_input').value; return numbersonly(this, event);\"";
				$onKeyUp = "";
				$title = "number";				
			}	
			
			if( $field->isDate() ){
				$title = "date";				
			}
			
			//If is a password field, don't allow changes
			if( $field->isPassword() ){
				$editable = "readonly=\"readonly\"";
				$visible = false;
			}
			
			//If is a file field, don't allow changes
			if( $field->isFile() ){
				$editable = "readonly=\"readonly\"";
				$visible = false;
			}
			
			if( $visible ){
				$returnString .= "<tr>";
								
				$returnString .= "<td>".$field->getAlias() ." : </td><td><input type=$type id=\"$fieldID\" name=\"".$field->getName()."\" $onKeyPress $onKeyUp  $editable title=\"$title\" /></td>";
					
				$returnString .= "</tr>";
				
				if( $field->isDate() ){
					$returnString .= "<script type=\"text/javascript\"> 
											$(function(){ 
												$(\"#$fieldID\").datepicker({dateFormat: 'dd/mm/yy', changeYear: true}); 
											}); 
											
										</script>"; 
										
				}
				
				
			}
			
		}
		
		$returnString .= "<tr><td colspan=2><center><input type=button value=\"Search\" onclick=\"$searchRequest\" />";
		$returnString .= "<input type=button value=\"Cancel\" onclick=\"changeVisibility('$divID'); this.form.reset();\" /></center></td></tr>";		
		$returnString .= "</table></form></div>";
		return $returnString;
	}
	
	/*
	 * Prints the "Insert New Row" form
	 */ 
	private function printInsertForm(){
		$divId = $this->tableName . "_div";
		if( $this->viewOnly ){
			$divId = "fk_" . $divId;
		}
		$returnString = "<div id=\"". $this->tableName ."_insertDiv\" style=\"display:none;\">";		
		$returnString .= "<form  id=\"". $this->tableName . "_insertForm\" ENCTYPE=\"multipart/form-data\" method=\"POST\" action=\"". $_SERVER['PHP_SELF']. "\"><br/>"; 
		$returnString .= "<table class=\"insertForm\">";
		$returnString .= "<tr><td colspan=\"2\"><center>New ".$this->objectName."</center></td></tr>";
		$columnsNames = $this->getTableColumnsNames();
		$addViaPost = false;
		
		//all fields names
		$fieldsNames = "";
		
		//all fields that are files to upload
		$fileFields = "";
		
		//Validate function for the field		
		$validateField = "";
		
		$fkSearchs = "";
		for($i = 0; $i < count($columnsNames); $i++){
			//print the field?
			$print = true;
			$validate = "";
			//is a mandatory field?
			$required = "";
			
			//field object
			$field = $this->fields[$columnsNames[$i]];
			
			//type of the field
			$type = "text";		
			
			//primary key text info	
			$primaryKey = "";
			
			//foreign key button code
			$foreignKey = "";
			
			if( $field->isIntNumber() ){
				$validate = "onKeyPress=\"return numbersonly(this, event);\"";
				
			}
			
			if( $field->isFloatNumber()){
				$validate = "onkeypress=\"return numbersonly(this,event,true);\"";
			}
			
			//if is nullable show the mandatory MARK
			if( !$field->isNullable() ){
				$required = "*";
			}
			
			//If is a password field, put a password input instead a text one
			if( $field->isPassword() ){
				$type = "Password";
			}
			
			
			//if is primary key...
			if( $field->isPrimaryKey() ){
				
				//if is not a auto increment PK.. show the field and mark as PK
				if(  !$field->isAutoIncrement() ){
					$primaryKey = "(PK)";
				}else{
					//if is a auto increment PK, don't show the field
					$print = false;
				}
			}
			
			if( $field->isFile() ){
				$addViaPost = true;
				$type = "file";			
				if( $fileFields == "" ){
					$fileFields .= $field->getName();
				}else{
					$fileFields .= "," . $field->getName();
				}
				
				
			}else{
				if( !$field->isPrimaryKey() || ($field->isPrimaryKey() && !$field->isAutoIncrement()) ){
					if( $fieldsNames == "" ){
						$fieldsNames .= $field->getName();
					}else{
						$fieldsNames .= "," . $field->getName();
					}
				}
			}
			
			if( $field->isForeignKey() ){
				$refT = $field->refTable;
				$refCol = $field->refColumn;
				$divid = "search_fk_".$refT . "_" . $refCol;
				$validate = "readonly=\"readonly\"";
				$foreignKey = "<input type=\"button\" value=\"Search\" onClick=\"sendFKSearchRequest('".$_SERVER['PHP_SELF']."','insert_".$field->getName() . "_input','".$refT."','".$refCol."',null,null,'ASC','1','".$divid."',false);\" />";
				$fkSearchs .= "<div class=\"fkSearchDiv\" id=\"$divid\" style=\"display:none\" ></div>";
			}
			
			
			if( $print ){
				$returnString .= "<tr>";							
				$returnString .= "<td>".$field->getAlias() ."$required : </td><td><input type=$type id=\"insert_". $field->getName() ."_input\" name=\"".$field->getName()."\" $validate /> $primaryKey $foreignKey</td>";				
				$returnString .= "</tr>";
			}
			
			if( $field->isDate() ){
				$returnString .= "<script type=\"text/javascript\"> 
										$(function(){ 
											$(\"#insert_" . $field->getName() . "_input\").datepicker({dateFormat: 'dd/mm/yy', changeYear: true}); 
										});
										
									</script>";
			}
			
			
		}
		
		$returnString .= "<tr><td colspan=2><center>All (*) fields are mandatory</center></tr></tr>";
		
		if( $addViaPost ){
			$returnString .= "<input type=hidden name=\"table\" value=\"".$this->tableName."\" />";
			$returnString .= "<input type=hidden name=\"action\" value=\"INSERT_FILE\" />";
			$returnString .= "<input type=hidden name=\"uploadingFile\" value=\"true\" />";
			$returnString .= "<input type=hidden name=\"files\" value=\"" . $fileFields . "\"/>";
			$returnString .= "<input type=hidden name=\"fieldsNames\" value=\"" . $fieldsNames . "\"/>";
			$returnString .= "<tr><td colspan=2><center><input type=submit value=\"Insert ". $this->objectName . "\" />";		
		}else{
			$returnString .= "<tr><td colspan=2><center><input type=button value=\"Insert ". $this->objectName . "\" onclick=\"toggleInsertForm('".$this->tableName."');sendInsertRequest('". $_SERVER['PHP_SELF'] ."','". $this->tableName."_insertForm','".$this->tableName ."','".$divId."');this.form.reset()\" />";		
		}
		
		$returnString .= "<input type=button value=\"Cancel\" onclick=\"toggleInsertForm('".$this->tableName."'); this.form.reset();\" /></center></td></tr>";
		$returnString .= "</table>";
		
		$returnString .= "</form>".$fkSearchs . "</div><br/>";
		
		return $returnString;
	}
	
	//Returns a Field from the fields array.
	//Search by name.
	public function getField($name){
		$found = false;
		$index = 0;
		$returnField = null;
		$columns = $this->getTableColumnsNames();

		while(!$found && $index < count($columns)){
			$field = $this->fields[$columns[$index++]];
		
			if( $field->getName() == $name ){
				$returnField = $field;
				$found = true;
			}	
		}
		
		return $returnField;
	}
	
	//Update the Fields in the fields array 
	//mark all the FKs as FK and update the Field class attributes: 
	//$refSchema
	//$refTable
	//$refColumn
	public function setTableForeignKeys(){
		$fks = Database::getTableForeignKeys(Database::getSchemaName(),$this->tableName);

		foreach($fks as $fk){
			$column = $fk['COLUMN_NAME'];
			$refTable = $fk['REFERENCED_TABLE_NAME'];
			$refSchema = $fk['REFERENCED_TABLE_SCHEMA'];
			$refColumn = $fk['REFERENCED_COLUMN_NAME'];
			
			$field = $this->getField($column);
			$field->setForeignKey($refSchema,$refTable,$refColumn);
		}
	}

}
?>
