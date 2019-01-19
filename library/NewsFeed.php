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

    public function __construct(){

        $this->db = new Database();

        $this->execute();

    }

    private function execute(){

        $this->getSource();

        if($this->sources){
            foreach($this->sources as $source){
                $this->feed_url = $source->url;

                $this->getContent();

                $exe = $this->parse($source->script);

                //$this->db->queueClear($source->qid);

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
        
        //parse by source
        require_once(__DIR__ . "/../scripts/" . $script . ".php");

        $this->news_list = $news_list;
        return $news_list;

    }

    private function save() {

        foreach ($this->news_list as $entry) {

            if(!$this->db->linkExists($entry)){
                
                //$ex = $this->db->newLink($entry);

            }
        }
        
    }

    public function __toString(){
        return print_r($this->news_list);
    }
}