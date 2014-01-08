<?php
	$fp = fopen('data.txt', 'w');
		fwrite($fp, '0');
		fwrite($fp, '0000');
		fclose($fp);
		
	if( isset($_FILES['picture_input']) ){
		$fp = fopen('data.txt', 'w');
		fwrite($fp, '1');
		fwrite($fp, '23');
		fclose($fp);
	}else{  $fp = fopen('data.txt', 'w');
		fwrite($fp, '5');
		fwrite($fp, '67');
		fclose($fp);}
	
?>
