<?php

require_once( __DIR__ . "/Query.php" );

class Database {

    private $dbh;

    private $db_user; //newsfeed
    private $db_pass; //newsfeed
    private $db_name; //newsfeed
    private $db_host; //192.168.1.10

    private $tables;

    public function __construct() {

        $this->db_host = "192.168.1.10";
        $this->db_name = "newsfeed";
        $this->db_user = "newsfeed";
        $this->db_pass = "newsfeed";

        $this->tables = [
            'sites' => 'dashboard_sites'
        ];

        return $this->connect();
    }

    public function __destruct(){

    }

    private function connect() {

        try {
            $this->dbh = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name, $this->db_user, $this->db_pass);

        } catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        return $this;
    }

    private function clear($res) {

        $res->closeCursor();

        return true;
    }

    private function _t($table){

        return $this->tables[$table];
    }

    private function select($query) {

        $stmt = $this->dbh->query($query);
        $stmt->setFetchMode(PDO::FETCH_OBJ);

        foreach($stmt as $row) $q::add($row);

        $this->clear($stmt);

        return $q;
    }

    private function insert($query) {

        $this->dbh->exec($query);

        $this->clear($res);
    }

    private function exists($table,$col,$search) {

        $stmt = $this->dbh->prepare("SELECT * FROM $table WHERE $col = :search");
        $stmt->bindParam(':search', $search);
        $stmt->execute();
        $rows = $stmt->rowCount();

        $this->clear($stmt);

        if($rows>0)
            return true;
        else
            return false;

    }

    public function queue(){

        $stmt = $this->dbh->query("SELECT qid, name, url, script FROM queue LIMIT 1");
        //$stmt->execute();
        
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $row=$stmt->fetch();

        return $row;
    }

    public function queueClear($qid){
        
        $stmt = $this->dbh->prepare("DELETE from source_queue WHERE q_id = :qid");

        $stmt->bindParam(':qid', $qid);

        return $stmt->execute();
    }

    public function linkExists($link){

        return $this->exists('dashboard_sites', 'ds_base_url', $link->base_url);
    }

    public function newLink($link){

        $stmt = $this->dbh->prepare("INSERT into dashboard_sites (ds_title, ds_description, ds_image, ds_date, ds_base_url, ds_origin_url) values (:title, :description, :image, :date, :base_url, :origin_url)");

        $stmt->bindParam(':title', $link->title);
        $stmt->bindParam(':description', $link->description);
        $stmt->bindParam(':image', $link->image);
        $stmt->bindParam(':date', $link->date);
        $stmt->bindParam(':base_url', $link->base_url);
        $stmt->bindParam(':origin_url', $link->origin_url);

        return $stmt->execute();
    }
}