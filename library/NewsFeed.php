<?php

require "vendor/autoload.php";

require_once( __DIR__ . "/Url.php" );
require_once( __DIR__ . "/Database.php" );
require_once( __DIR__ . "/Link.php" );

use PHPHtmlParser\Dom;

class NewsFeed {

    private $feed_url;
    private $content;
    private $news_list = [];

    private $sources = [];

    private $db;

    private $clear_queue = true;
    private $save = true;
    private $update_enable = false;

    public function __construct($argv){

        try {

            $this->db = new Database();

            if(isset($argv[2])) {
                switch($argv[2]){
                    case '-q':
                        $this->clear_queue = false;
                    break;
                }
            }

            $run = ""; //default set of run

            for($a=1; $a<count($argv); $a++){
                switch($argv[$a]){
                    //run 
                    case 'get': $run = "get"; break;
                    //case 'update': $run = "update"; break;
                    case 'getall': $run = "getall"; break;
                    case 'load': $run = "load"; break;
                    //options
                    case '-q': $this->clear_queue = false; break; //don't clear queue
                    case '-s': $this->save = false; break; //don't save to db
                    case '-u': $this->update_enable = true; //update records
                }
            }

            switch($run){
                    
                //collect news
                case 'get':
                    $this->execute();
                break;

                //collect from all sources
                case 'getall':
                    $this->loop();
                break;

                //set queue
                case 'load':
                    $this->loadSourceList();
                break;
            }

        } catch (Exception|EmptyCollectionException $e) {
            //print "Error: ".$e->getMessage();
            print date("Y-m-d H:i:s").": {".$e->getMessage() . "} in " . $e->getFile() . ", line " . $e->getLine() . "\n";
            die();
        } /* finally {
            print "error?\n";
            die();
        } */

    }

    private function execute(){

        $return = false;

        $this->getSource();

        if($this->sources){
            
            if(count($this->sources)==0) {

                echo date("Y-m-d H:i:s") . ": Queue is empty. Nothing to load.\n";
                $this->clear_queue = false;
                $return = false;

            } else {

                $source = $this->sources[0];
                $this->feed_url = $source->url;
                echo "Donloading data from ".$source->name." (".$source->url.")\n";
                $content = $this->getContent();

                if($content) {

                    $parse = $this->parse($source->script);

                    if($this->save and $parse) {
                        $this->save();
                        echo date("Y-m-d H:i:s") . ": Successfuly load feed from ".$source->name."\n";
                    }

                    $this->sources = false;
                    $return = true;
                
                } else {
                    $return = $content;
                }
            }

            $return = true;

        } else {

            $this->sources = false;
            echo date("Y-m-d H:i:s") . ": Queue is empty. Nothing to load.\n";
            $return = false;
        }

        if($this->clear_queue and $return) 
            $this->db->queueClear($source->qid);

        return $return;
    }

    private function loop(){

        for($i=0; $i<100; $i++){
            if(!$this->execute()){
                break;
            }
        }
    }

    private function getSource(){
        
        $queue = $this->db->queue();
        if($queue)
            $this->sources[] = $queue;
        else
            $this->sources = false;
    }

    private function getContent() {

        $headers = @get_headers($this->feed_url);
        if(!$headers or $headers[0] == 'HTTP/1.1 404 Not Found'){

            echo date("Y-m-d H:i:s") . ": Source ". $this->feed_url ." is not valid or not responding.\n";

            return false;

        } else {

            $content = file_get_contents($this->feed_url);

            if(empty($content) or trim($content) == ""){
                echo date("Y-m-d H:i:s") . ": Page content of ". $this->feed_url ." is empty.\n";
                return false;
            }

            $this->content = $content;
            return true;
        }
    }

    private function parse($script){

        $x = new SimpleXmlElement($this->content);
        $news_list=[];

        $dom = new Dom;
        
        //parse by source
        if(file_exists(_ROOTDIR_ . "/scripts/" . $script . ".php")){
            require_once(_ROOTDIR_ . "/scripts/" . $script . ".php");
        } else {
            return false;
        }

        for($i=0; $i<count($news_list); $i++){
            $news_list[$i]->hash = Std::short_md5($news_list[$i]->base_url);

            if(!empty($news_list[$i]->image_url) or trim($news_list[$i]->image_url) != ""){
                $img_ext = explode(".",$news_list[$i]->image_url);
                //$img_ext[count($img_ext)-1] 

                $news_list[$i]->image_local = $script . "_" . $news_list[$i]->hash . ".jpg" ;

                if(!file_exists(_ROOTDIR_ . '/images/' . $news_list[$i]->image_local)){
                    $image = file_get_contents($news_list[$i]->image_url);
                    file_put_contents(_ROOTDIR_ . '/images/' . $news_list[$i]->image_local, $image);
                }
            }
        }

        //print_r($news_list);


        $this->news_list = $news_list;
        return $news_list;

    }

    private function save() {

        foreach ($this->news_list as $entry) {

            if($this->db->linkExists($entry)){

                if($this->update_enable) $this->db->updateLink($entry);

            } else {

                $this->db->newLink($entry);
            }
        }
    }

    private function repere(){

        $feed = $this->db->getBrokenFeeds();



    }

    private function loadSourceList(){

        $res = $this->db->loadSource();
        echo date("Y-m-d H:i:s") . ": Source list was successfuly loaded.\n";
    }

    public function __toString(){
        return print_r($this->news_list);
    }
}