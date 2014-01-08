<?php

class Util{
	
public static function startsWith($string, $char)
{
    $length = strlen($char);
    return (substr($string, 0, $length) === $char);
}

public static function endsWith($string, $char)
{
    $length = strlen($char);
    $start =  $length *-1; //negative
    return (substr($string, $start, $length) === $char);
}

public static function saveDump($var, $fileName){
	ob_start();
	var_dump($var);
	$temp = ob_get_clean();
	$fp = fopen($fileName,"a");
	fwrite($fp, "var ".$temp . "\r\n");
	fclose($fp);
	return $temp;
}

}

?>
