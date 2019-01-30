<?php

class Log {

    private static $message;

    public static function init($msg){
        self::$message = $msg;

        self::console($msg);
    }

    public static function output($message){
        //return date("Y-m-d H:i:s") . ": " . $mesage . "\n";
        return "[".date("Y-m-d H:i:s") . "]: " . $message . "\n";
    }

    public static function console($msg){
        self::$message = $msg;
        echo self::output($msg);
    }

    public static function dump($msg){
        self::$message = $msg;
        var_dump($msg);
    }

    public static function file($msg){
        self::$message = $msg;
    }
}