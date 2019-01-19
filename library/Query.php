<?php

class Query{

    private static $query;
    private static $count=0;
    private static $result=[];

    public static function set($query) {
        self::$query = $query;
    }

    public static function add($row){
        self::$result[] = $row;
        self::$count++;
    }

    public static function getResult(){

    }

    public static function getCount(){
        
    }

    public static function getQuery(){
        
    }

}