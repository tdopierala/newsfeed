<?php

class Log {

	private static $message;

	private function __construct(){

	}


	public static function init($msg){
		self::$message = $msg;
		self::console_nl($msg);
		echo "\n";
	}

	private static function output($message){
		//return "\e[1;37;40m[".date("Y-m-d H:i:s") . "]\e[0m " . $message;
		return "[".date("Y-m-d H:i:s") . "] " . $message;
	}

	private static function toFile($msg){
		self::$message = $msg;
		$filename = "main_" . date("Y-m-d") . ".log";
		$log = fopen(_ROOTDIR_ . "/log/" . $filename, "a");
		fwrite($log, self::output($msg) . "\n");
		fclose($log);
	}

	public static function printout($msg){
		self::$message = $msg;
		self::toFile($msg);
		echo "\r";
		for ($i=0;$i<150;$i++) { echo ' '; }
		echo "\r" . substr(self::output($msg), 0, 150) . "\n";
	}

	public static function console($msg){
		self::$message = $msg;
		self::toFile($msg);

		$_msg = self::output($msg);
		$count = 134;
		$len = strlen($_msg);

		if($len < $count)
			for ($i=0; $i<($count - $len); $i++)
				$_msg = $_msg.' ';

		$clear = "\r";
		for ($i=0; $i<$count+6; $i++) $clear = $clear . ' ';
		$clear = $clear . "\r";

		echo $clear . substr($_msg, 0, $count) . " {" . sprintf('%03d', strlen(self::output($msg))) . "}";
	}

	public static function console_nl($msg){
		self::$message = $msg;
		self::toFile($msg);

		$_msg = self::output($msg);
		$count = 134;
		$len = strlen($_msg);

		if($len < $count)
			for ($i=0; $i<($count - $len); $i++)
				$_msg = $_msg.' ';

		echo "\n" . substr($_msg, 0, $count) . " {" . sprintf('%03d', strlen(self::output($msg))) . "}";
	}

	public static function dump($msg){
		self::$message = $msg;
		echo "\n";
		var_dump($msg);
	}
}