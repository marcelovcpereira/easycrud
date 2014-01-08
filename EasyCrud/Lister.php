<?
require_once 'Database.php';
require_once 'Util.php';

class Lister{
	
		var $tableName;
		var $pageSize = 10;
		var $pageNumber = 1;
		var $object = null;
		var $objectArray = Array();
		var $numberOfPages = 0;
		var $numberOfObjects = 0;
		var $criteria = null;
		var $orderBy = null;
		var $orderType = "ASC";
		//tableName,pageSize,$pageNumber,$criteria
		function __construct($tN,$pS,$pN,$crit,$order,$type){
			$this->tableName = $tN;
			$this->setPageSize($pS);
			$this->setPageNumber($pN);
			$this->criteria = $crit;
			$this->orderBy = $order;
			$this->orderType = $type;
			$this->initializeNumberOfPages();
			$this->loadList();
		}
		
		private function initializeNumberOfPages(){
			Database::fastConnect();
			if( $this->criteria == NULL ){}
		}
		
		public function getNumberOfPages(){
			return $this->numberOfPages;
		}
		
		
		private function loadList(){
			Database::fastConnect();	
			
			if( $this->criteria == NULL || $this->criteria == null || $this->criteria == "null" || $this->criteria == ""){
				$this->criteria = "";
			}
			
			if( $this->orderBy == NULL || $this->orderBy == null || $this->orderBy == "null" || $this->orderBy == ""){
				$this->orderBy = "1";	
			}
			
			//Initializing number of pages attribute
			$numberOfPagesQuery = "SELECT COUNT(*) FROM " . $this->tableName . " WHERE 1=1" . $this->criteria;
			
			$numberOfPagesResult = mysql_query($numberOfPagesQuery);
			
			if( $numberOfPagesResult ){
				$row = mysql_fetch_row($numberOfPagesResult);
				$numberOfRows = $row[0];
				$this->numberOfObjects = $row[0];
				$this->numberOfPages = (int)($numberOfRows/$this->pageSize);
				if( $numberOfRows%$this->pageSize != 0 ){
					$this->numberOfPages++;
				}
				
			}else{
				throw new Exception('Database Query Error: crit:'.$this->criteria."::::::" . mysql_error());
			}
			
			/* If there's no row returned, the number of pages will be 1 */
			if( $this->numberOfPages < 1 ){
				$this->numberOfPages = 1;
			}
			
			$rowOffset = ($this->pageNumber-1)*$this->pageSize;
			
			$query = "SELECT * FROM " . $this->tableName . " WHERE 1=1" . $this->criteria . " ORDER BY " . $this->orderBy . " " . $this->orderType . " LIMIT " . $rowOffset . "," . $this->pageSize;
			$result = mysql_query($query);
			
			if( $result ){
				
				$numberOfObjects = mysql_num_rows($result);
				if( $numberOfObjects > 0 ){
					while($row = mysql_fetch_assoc($result)){
						$this->objectArray[] = $row;
					}					
				}else{
					$this->objectArray = NULL;
				}
			}else{
				throw new Exception('Database Query Error: ' . mysql_error());
			}					
		}
		
		
		public function getObjectArray(){
			return $this->objectArray;
		}
		
		public function getSize(){
			return count($this->objectArray);
		}
		
		public function setPageSize($pS){
			if( $pS != null && $pS > 0 ){
				$this->pageSize = $pS;
			}else{
				$this->pageSize = 10;	
			}			
		}
		
		public function setPageNumber($pN){
			if( $pN != null && $pN > 0 ){
				$this->pageNumber = $pN;
			}else{
				$this->pageNumber = 1;	
			}
		}
		
		
		
}
?>
