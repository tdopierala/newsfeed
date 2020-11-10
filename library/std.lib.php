<?php

set_error_handler (
    function($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, $errno, 0, $errfile, $errline);     
    }
);

class Std {

    public static function short_md5($str){

        $md5=md5($str);
        $hash="";
    
        for($i=0; $i<strlen($md5); $i++){
            $hash.=substr($md5, $i, 1);
            $i++;
        }
    
        return $hash;
    }

    public static function console_log($var){

    /*  "boolean"
        "integer"
        "double" (for historical reasons "double" is returned in case of a float, and not simply "float")
        "string"
        "array"
        "object"
        "resource"
        "resource (closed)" as of PHP 7.2.0
        "NULL"
        "unknown type" */
    
    
        $type = gettype($var);
    
        switch($type){
    
            case "boolean":
    
            break;
    
            case "integer":
    
            break;
    
            case "double":
    
            break;
    
            case "string":
                $out = $var;
            break;
    
            case "array":
                $out = print_r($var, true);
            break;
    
            case "object":
    
            break;
    
            case "NULL":
    
            break;
    
            default:
                $out = "unknown type";
            break;
        }
    
        echo "\n" . $out;
        return $out;
	}
	
	public static function generate_user_agent() {

		$f = './useragents.txt';

		if(file_exists($f)){

			$lines = file($f);

			return 'User-Agent: '.$lines[array_rand($lines)];
		
		} else {

			return 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36';

		}
	}

    /* public static log($msg) {


	} */
	

}