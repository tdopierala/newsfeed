<?php

class Link {

    public $title;
    public $hash;
    public $description;
    public $base_url;
    public $date;
    public $image_url;
    public $image_local;
    public $origin_url;
    public $link2;

    public function __construct($params){

        foreach($params as $k => $v) 
            $this->$k = (string)$v;

        
        return $this;
    }

}