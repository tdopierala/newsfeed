<?php

require "vendor/autoload.php";

require_once( __DIR__ . "/Url.php" );
require_once( __DIR__ . "/Database.php" );
require_once( __DIR__ . "/Link.php" );
require_once( __DIR__ . "/Log.php" );

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
    private $debug = false;

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
            $opt;

            for($a=1; $a<count($argv); $a++){
                switch($argv[$a]){
                    //run 
                    case 'get': $run = "get"; break;
                    //case 'update': $run = "update"; break;
                    case 'getall': $run = "getall"; break;
                    case 'load': $run = "load"; $opt = isset($argv[$a+1]) ? $argv[$a+1] : null; break;
                    //options
                    case '-q': $this->clear_queue = false; break; //don't clear queue
                    case '-s': $this->save = false; break; //don't save to db
                    case '-u': $this->update_enable = true; //update records
                    case '-d': $this->debug = true; //update records
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
                    $this->loadSourceList($opt);
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

                //echo date("Y-m-d H:i:s") . ": Queue is empty. Nothing to load.\n";
                Log::init("Queue is empty. Nothing to load.");
                $this->clear_queue = false;
                $return = false;

            } else {

                $source = $this->sources[0];
                $this->feed_url = $source->url;

                Log::init("Donloading data from ".$source->name." (".$source->url.")");

                $content = $this->getContent();

                if($content) {

                    $parse = $this->parse($source->script);

                    if($this->save and $parse) {
                        if($this->save()){
                            Log::init("Successfuly load feed from ".$source->name);
                            //echo date("Y-m-d H:i:s") . ": Successfuly load feed from ".$source->name."\n";
                        } else {
                            Log::init("Database failed when saving: ".$source->name);
                            //echo date("Y-m-d H:i:s") . ": Database failed when saving: ".$source->name."\n";
                        }
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
            //echo date("Y-m-d H:i:s") . ": Queue is empty. Nothing to load.\n";
            Log::init("Queue is empty. Nothing to load.");
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

            Log::init("Source ". $this->feed_url ." is not valid or not responding.");
            //echo date("Y-m-d H:i:s") . ": Source ". $this->feed_url ." is not valid or not responding.\n";

            return false;

        } else {

            $content = file_get_contents($this->feed_url);

            if(empty($content) or trim($content) == ""){

                Log::init("Page content of ". $this->feed_url ." is empty.");
                //echo date("Y-m-d H:i:s") . ": Page content of ". $this->feed_url ." is empty.\n";

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
            $_this = $this;
            require_once(_ROOTDIR_ . "/scripts/" . $script . ".php");
        } else {
            return false;
        }

        for($i=0; $i<count($news_list); $i++){
            $news_list[$i]->hash = Std::short_md5($news_list[$i]->base_url);

            var_dump($news_list[$i]->image_url);

            if(!empty($news_list[$i]->image_url) or trim($news_list[$i]->image_url) != ""){

                $filename = $script . "_" . $news_list[$i]->hash;

                $news_list[$i]->image_local = $filename . ".jpg";

                $new_img = _ROOTDIR_ . '/images/origin/' . $filename . $ext ;

                if(!file_exists($new_img)){

                    $image = file_get_contents($news_list[$i]->image_url);
                    file_put_contents($new_img, $image);
                }
            }
        }

        //print_r($news_list);


        $this->news_list = $news_list;
        return $news_list;

    }

    private function save() : bool{

        $result = [];

        foreach ($this->news_list as $entry) {

            if($this->db->linkExists($entry)){
                if($this->update_enable) 
                    $result[] = $this->db->updateLink($entry);
            } else {
                $result[] = $this->db->newLink($entry);
            }
        }

        return !in_array(false, $result, true);
    }

    private function repere(){

        $feed = $this->db->getBrokenFeeds();
    }

    private function loadSourceList($opt){

        $res = $this->db->loadSource($opt);
        Log::init("Source list was successfuly loaded.");
        //echo date("Y-m-d H:i:s") . ": Source list was successfuly loaded.\n";
    }

    public function __toString(){
        return print_r($this->news_list);
    }
}