<?php

require_once( __DIR__ . "/Url.php" );
require_once( __DIR__ . "/Database.php" );
require_once( __DIR__ . "/Link.php" );

class NewsFeed {

    private $feed_url;
    private $content;
    private $news_list = [];

    private $sources = [];

    private $db;

    private $clear_queue = true;
    private $save = false;

    public function __construct($argv){

        $this->db = new Database();

        for($a=1; $a<count($argv); $a++){
            switch($argv[$a]){
                
                case 'collect':
                    $this->execute();
                break;

                case 'load':
                    $this->loadSourceList();
                break;
            }
        }

    }

    private function execute(){

        $this->getSource();

        if($this->sources){
            foreach($this->sources as $source){
                $this->feed_url = $source->url;

                $this->getContent();

                $exe = $this->parse($source->script);

                if($this->clear_queue)
                    $this->db->queueClear($source->qid);

                if($this->save)
                    $this->save();

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
        $this->content = file_get_contents($this->feed_url);
    }

    private function parse($script){

        $x = new SimpleXmlElement($this->content);
        $news_list=[];
        
        //parse by source
        require_once(__DIR__ . "/../scripts/" . $script . ".php");

        print_r($news_list);
        $this->news_list = $news_list;
        return $news_list;

    }

    private function save() {

        foreach ($this->news_list as $entry) {

            if(!$this->db->linkExists($entry)){
                
                $ex = $this->db->newLink($entry);

            }
        }
        
    }

    private function loadSourceList(){

        $this->db->loadSource($entry);
    }

    public function __toString(){
        return print_r($this->news_list);
    }
}