<?php

require_once( __DIR__ . "/Query.php" );

class Database {

	private $dbh;

	private $db_user; //newsfeed
	private $db_pass; //newsfeed
	private $db_name; //newsfeed
	private $db_host; //192.168.1.10

	private $tables;

	// $auth = ['dbhost'=>'192.168.1.10', 'dbname'=>'newsfeed', 'dbuser'=>'newsfeed', 'dbpass'=>'newsfeed']
	public function __construct($auth = ['dbhost'=>'localhost', 'dbname'=>'newsfeed', 'dbuser'=>'newsfeed', 'dbpass'=>'newsfeed']) {

		$this->db_host = $auth['dbhost'];
		$this->db_name = $auth['dbname'];
		$this->db_user = $auth['dbuser'];
		$this->db_pass = $auth['dbpass'];

		$this->tables = [
			'sites' => 'dashboard_sites'
		];

		return $this->connect();
	}

	private function connect() {

		//try {
			$this->dbh = new PDO('mysql:host='.$this->db_host.';dbname='.$this->db_name, $this->db_user, $this->db_pass);

		//} catch (PDOException $e) {
		//    print "Error!: " . $e->getMessage() . "<br/>";
		//    die();
		//}

		return $this;
	}

	private function clear($res) {

		$res->closeCursor();
		return true;
	}

	private function _t($table){

		return $this->tables[$table];
	}

	private function select($query) { //to remove

		$stmt = $this->dbh->query($query);
		$stmt->setFetchMode(PDO::FETCH_OBJ);

		foreach($stmt as $row) $q::add($row);

		$this->clear($stmt);

		return $q;
	}

	private function insert($query) { //to remove

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

	public function queue(){ //to remove

		$stmt = $this->dbh->query("SELECT qid, name, url, script FROM queue LIMIT 1");
		//$stmt->execute();
		
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		$row=$stmt->fetch();

		return $row;
	}

	public function queueClear($qid){

		// 1 - weating
		// 2 - parsing...
		// 3 - compleated
		
		$stmt = $this->dbh->prepare("DELETE from source_queue WHERE q_id = :qid");
		//$stmt = $this->dbh->prepare("UPDATE source_queue SET q_status = 3 WHERE q_id = :qid");

		$stmt->bindParam(':qid', $qid);

		$res = $stmt->execute();

		$this->clear($stmt);

		return $res;
	}

	public function linkExists($link){

		return $this->exists('dashboard_sites', 'ds_base_url', $link->base_url);
	}

	public function newLink($link){

		$params = [
			0=>$link->title, 
			1=>$link->hash, 
			2=>$link->description, 
			3=>$link->image_url,
			4=>$link->image_local,
			5=>$link->date,
			6=>$link->base_url,
			7=>$link->origin_url,
			8=>$link->content
		];

		return $this->func('new_link', $params);
	}

	public function updateLink($link){

		$params = [
			null,
			$link->title, 
			$link->hash, 
			$link->description, 
			$link->image_url,
			$link->image_local,
			$link->date,
			$link->base_url,
			$link->origin_url,
			$link->content
		];

		return $this->func('update_link', $params);
	}

	private function func($_proc,$_params=[]){

		$params="";
		for($i=0; $i<count($_params); $i++) $params .= ",?";

		$query="CALL " . $_proc . "(" . substr($params,1) . ")";
		//var_dump($_params);
		$stmt = $this->dbh->prepare($query);

		for($i=0; $i<count($_params); $i++) $stmt->bindParam(($i+1),$_params[$i]);

		return $stmt->execute();
	}

	public function getBrokenFeeds(){

		$stmt = $this->dbh->query("SELECT ds_title, ds_hash, ds_description, ds_image_url, ds_image_local, ds_date, ds_base_url, ds_origin_url FROM dashboard_sites WHERE ds_image_url == '' or ds_image_local is null");
		//$stmt->execute();
		
		$stmt->setFetchMode(PDO::FETCH_OBJ);
		$row=$stmt->fetch();

		return $row;
	}

	public function getDBImage($imagename){
		
		$stmt = $this->dbh->prepare("SELECT ds_image_url, ds_image_local from dashboard_sites WHERE ds_image_local like :imagelocal LIMIT 1");

		$stmt->execute(['imagelocal' => $imagename]);
		$row=$stmt->fetch();

		$this->clear($stmt);

		return $row;
	}

	public function loadSource($opt){

		if(empty($opt)) $prop[] = 0;
		else $prop[] = $opt;

		return $this->func("set_queue", $prop);
	}
}