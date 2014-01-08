<?

require_once 'Util.php';

//This class represents a column from a table
class Field{
	
	//Name of the table field
	var $name;
	
	//Alias name of the table field
	var $alias;
	
	//Type of the field
	var $type;
	
	//Will the field be visible on the table?
	var $isVisible;
	
	//The MySQL-key of the field: PRI, MUL, UNI...
	var $key;
	
	//Can the field value be NULL?
	var $nullable;
	
	//MySQL-extra of the field: auto_increment...
	var $extraInfo;
	
	//Is  a Password field?
	var $isPassword;
	
	//The insert callback function of this field
	var $insertCallbackFunction;
	
	//The show callback function of this field
	var $showCallbackFunction;
	
	//If this field is a Foreign Key, 
	//this is the schema name of the referenced column 
	var $refSchema = NULL;
	
	//If this field is a Foreign Key, 
	//this is the table name of the referenced column 
	var $refTable = NULL;
	
	//If this field is a Foreign Key, 
	//this is the referenced column
	var $refColumn = NULL;
	
	//Is this a Foreign Key?
	var $isForeignKey = false;
	
	//Actual Ordering type of the field
	var $orderType = "ASC";
	
	public function __construct($fN,$fT,$iV,$k,$n,$extra){
		$this->name = $fN;
		$this->alias = $fN;
		$this->type = $fT;
		$this->isVisible = $iV;
		$this->key = $k;
		$this->nullable = $n;
		$this->extraInfo = $extra;
	}
	
	public function getOrderType(){
		return $this->orderType;
	}
	
	public function invertOrderType(){
		if( $this->orderType == "ASC" ){
			$this->orderType = "DESC";			
		}else{
			$this->orderType = "ASC";
		}
	}
	
	public function isForeignKey(){
			return $this->isForeignKey;
	}
	
	public function setForeignKey($refS,$refT,$refC){
		$this->refSchema = $refS;
		$this->refTable = $refT;
		$this->refColumn = $refC;
		$this->isForeignKey = true;
	}
	
	public function isPrimaryKey(){
		$isPrimary = false;
		if($this->key == 'PRI'){
			$isPrimary = true;
		}
		return $isPrimary;
	}
	
	public function isFile(){
		return (strtoupper($this->type) == 'BLOB' || 				
				strtoupper($this->type) == 'LONGBLOB' ||							
				strtoupper($this->type) == 'MEDIUMBLOB' ||				
				strtoupper($this->type) == 'TINYBLOB');
	}
	
	public function isUnique(){
		$isUnique = false;
		if($this->key == 'UNI'){
			$isUnique = true;
		}
		return $isUnique;
	}
	
	public function isAutoIncrement(){
		$isAutoIncrement = false;
		if($this->extraInfo == 'auto_increment'){
			$isAutoIncrement = true;
		}
		return $isAutoIncrement;
	}
	
	public function isDate(){
		if( $this->type == 'date' || $this->type == 'datetime'){
			return true;
		}else{
			return false;
		}
	}
	public function isPassword(){ return $this->isPassword; }
	public function setPassword($bool){ $this->isPassword = $bool; }
	public function getName(){ return $this->name; }
	public function getAlias(){ return $this->alias; }
	public function setAlias($alias){ $this->alias = $alias; }
	public function getType(){ return $this->type; }
	public function isVisible(){ return $this->isVisible; }
	public function setVisible($bool){ $this->isVisible = $bool; }
	public function getKey(){ return $this->key; }
	
	
	//Can the field value be null?
	public function isNullable(){ 
		if( $this->nullable == "NO" ){
			return false;
		}else{
			return true;
		}
	}
	
	public function isIntNumber(){
		$isNumber = false;
		if( Util::startsWith(strtoupper($this->type),'INT') ||
			Util::startsWith(strtoupper($this->type),'INTEGER') ||
			Util::startsWith(strtoupper($this->type),'SMALLINT') ||
			Util::startsWith(strtoupper($this->type),'TINYINT') ||
			Util::startsWith(strtoupper($this->type),'MEDIUMINT') ||
			Util::startsWith(strtoupper($this->type),'BIGINT') ||			
			Util::startsWith(strtoupper($this->type),'NUMERIC')
			){
			$isNumber = true; 
		}
		return $isNumber;
	}
	
	
	public function isFloatNumber(){
		$isNumber = false;
		if( Util::startsWith(strtoupper($this->type),'FLOAT') ||
			Util::startsWith(strtoupper($this->type),'REAL') ||
			Util::startsWith(strtoupper($this->type),'DOUBLE PRECISION') ||
			Util::startsWith(strtoupper($this->type),'DOUBLE') ||
			Util::startsWith(strtoupper($this->type),'DECIMAL') ||
			Util::startsWith(strtoupper($this->type),'DEC') ||
			Util::startsWith(strtoupper($this->type),'NUMERIC')
			){
			$isNumber = true; 
		}
		return $isNumber;
	}
	
	//Sets the function that will be called before
	//the field insertion on table
	public function setInsertCallbackFunction($func){
		$this->insertCallbackFunction = $func;
	}
	
	//Sets the function that will be called before
	//the field exibition on table
	public function setShowCallbackFunction($func){
		$this->showCallbackFunction = $func;
	}
	
	//Returns the field value after being processed by the 
	//Insert Callback Function
	public function getProcessedValue($value){
		$t = $this->insertCallbackFunction;
		if( $t != NULL ){
			return $t($value);
		}else{
			return $value;
		}
		
	}
	
	//If the field has a Callback Function to show it's value,
	//return the processed value, else return the raw value
	public function getShowValue($value){
		$t = $this->showCallbackFunction;
		if( $t != NULL ){
			return $t($value);
		}else{
			return $value;
		}
	}
	
}
?>
