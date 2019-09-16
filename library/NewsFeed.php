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
			$opt = null;

			for($a=1; $a<count($argv); $a++){
				switch($argv[$a]){
					//run 
					case 'get': $run = "get"; break;
					//case 'update': $run = "update"; break;
					case 'getall': $run = "getall"; break;
					case 'load': $run = "load"; $opt = isset($argv[$a+1]) ? $argv[$a+1] : null; break;
					case 'update': $run = "update"; break;
					
					//options
					case '-q': $this->clear_queue = false; break; //don't clear queue
					case '-s': $this->save = false; break; //don't save to db
					case '-u': $this->update_enable = true; break; //update records
					case '-d': $this->debug = true; break; //debug mod (more info output)
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

				case 'update':
					$this->update();
				break;
			}

		} catch (PDOException $e) {

			print date("Y-m-d H:i:s").": Error!: " . $e->getMessage() . "<br/>";
			//die();

		} catch (Exception|EmptyCollectionException|ErrorException $e) {
			//print "Error: ".$e->getMessage();
			print date("Y-m-d H:i:s").": {".$e->getMessage() . "} in " . $e->getFile() . ", line " . $e->getLine() . "\n";
			//die();
		} /* finally {
			print "error?\n";
			die();
		} */

	}

	private function execute() : bool {

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

	private function getContent() : bool {

		$headers = @get_headers($this->feed_url);
		if(!$headers or $headers[0] == 'HTTP/1.1 404 Not Found'){

			Log::init("Source ". $this->feed_url ." is not valid or not responding.");

			return false;

		} else {

			$context = stream_context_create([
				"http" => [
					"header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
				]
			]);
			$content = file_get_contents($this->feed_url, false, $context);

			if(empty($content) or trim($content) == ""){

				Log::init("Page content of ". $this->feed_url ." is empty.");

				return false;
			}

			$this->content = $content;
			return true;
		}
	}

	private function parse($script) : bool {

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

			if(!empty($news_list[$i]->image_url) or trim($news_list[$i]->image_url) != ""){

				$filename = $script . "_" . $news_list[$i]->hash;
				$downloaded_img = _ROOTDIR_ . '/images/download/' . $filename . '.unknown' ;

				$context = stream_context_create([
					"http" => ["header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"]
				]);

				$image = file_get_contents(str_replace(" ", "%20", $news_list[$i]->image_url), false, $context);
				file_put_contents($downloaded_img, $image);
				//TODO: check if the image downloding finished with sucess

				$imgtype = null; $ext = null;
				switch(exif_imagetype($downloaded_img)) {
					case IMG_GIF:  $imgtype = 'image/gif';  $ext = ".gif"; break;
					case IMG_JPG:  $imgtype = 'image/jpg';  $ext = ".jpg"; break;
					case IMG_JPEG: $imgtype = 'image/jpeg'; $ext = ".jpeg"; break;
					case IMG_PNG:  $imgtype = 'image/png';  $ext = ".png"; break;

					case IMAGETYPE_GIF:  $imgtype = 'image/gif';  $ext = ".gif"; break;
					case IMAGETYPE_JPEG: $imgtype = 'image/jpeg'; $ext = ".jpeg"; break;
					case IMAGETYPE_PNG:  $imgtype = 'image/png';  $ext = ".png"; break;
					/* 
					case IMAGETYPE_SWF: $imgtype = 'swf'; break;
					case IMAGETYPE_PSD: $imgtype = 'psd'; break;
					case IMAGETYPE_BMP: $imgtype = 'bmp'; break;
					case IMAGETYPE_TIFF_II: $imgtype = 'tiff II'; break;
					case IMAGETYPE_TIFF_MM: $imgtype = 'tiff mm'; break;
					case IMAGETYPE_JPC: $imgtype = 'jpc'; break;
					case IMAGETYPE_JP2: $imgtype = 'jp2'; break;
					case IMAGETYPE_JPX: $imgtype = 'jpx'; break;
					case IMAGETYPE_JB2: $imgtype = 'jb2'; break;
					case IMAGETYPE_SWC: $imgtype = 'swc'; break;
					case IMAGETYPE_IFF: $imgtype = 'iff'; break;
					case IMAGETYPE_WBMP: $imgtype = 'wbmp'; break;
					case IMAGETYPE_XBM: $imgtype = 'xbm'; break;
					case IMAGETYPE_ICO: $imgtype = 'ico'; break;
					case IMAGETYPE_WEBP: $imgtype = 'webp'; break;
						*/
					//case IMG_WBMP: $imgtype = 'image/wbmp'; break;
					//case IMG_XPM:  $imgtype = 'image/xpm'; break;
					default:       $imgtype = 'unknown';
				}

				//file delete when extention not recognized
				if (is_null($imgtype) or $imgtype == 'unknown' or is_null($ext)) {
					unlink($downloaded_img);
					continue;
				}

				$news_list[$i]->image_local = $filename . $ext;
				$origin_img = _ROOTDIR_ . '/images/origin/' . $news_list[$i]->image_local;

				if ($this->update_enable and file_exists($origin_img)) {
					unlink($origin_img); //delete origin file when file exist and update mode enable
				}

				if( !file_exists($origin_img) and file_exists($downloaded_img)){

					rename($downloaded_img, $origin_img); //move file from /download to /origin and change name

					if($this->prepareImage($origin_img)){
						if ($this->debug or $this->update_enable) 
							Log::init("Image ". $news_list[$i]->image_local ." saved properly.");
					} else {
						Log::init("Saving image ". $news_list[$i]->image_local ." failed.");
					}
				}
			}
		}

		//print_r($news_list);

		$this->news_list = $news_list;
		return true;

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

	private function update(){

		//$this->convertOldImages();
		$this->clearOldImages();

		Log::init("Update finish.");
		return true;
	}

	private function loadSourceList($opt){

		$res = $this->db->loadSource($opt);
		Log::init("Source list was successfuly loaded.");
		//echo date("Y-m-d H:i:s") . ": Source list was successfuly loaded.\n";
	}

	private function prepareImage($_image){

		$imgtype = null; $ext = null;
		$exif = @exif_imagetype($_image);
		switch($exif) {
			case IMG_GIF:  $imgtype = 'image/gif';  $ext = "gif"; break;
			case IMG_JPG:  $imgtype = 'image/jpg';  $ext = "jpg"; break;
			case IMG_JPEG: $imgtype = 'image/jpeg'; $ext = "jpeg"; break;
			case IMG_PNG:  $imgtype = 'image/png';  $ext = "png"; break;

			case IMAGETYPE_GIF:  $imgtype = 'image/gif';  $ext = "gif"; break;
			case IMAGETYPE_JPEG: $imgtype = 'image/jpeg'; $ext = "jpeg"; break;
			case IMAGETYPE_PNG:  $imgtype = 'image/png';  $ext = "png"; break;

			default: $imgtype = 'unknown';
		}

		if(is_null($imgtype) or $imgtype == 'unknown' or is_null($ext)) {
			
			Log::init("Unknown image type for: ". $_image);
			
			return false;
		}

		//$ext = substr($_image, strrpos($_image, ".")+1);
		$filename = substr($_image, strrpos($_image, "/")+1, (-1)*(strlen($ext)+1));

		$_origin = _ROOTDIR_ . '/images/normal/' . $filename . "." . $ext;//".jpg";
		$_thumb = _ROOTDIR_ . '/images/thumb/' . $filename . "." . $ext;//".jpg";

		switch ($ext) {
			case 'jpg':
			case 'jpeg': $image = imagecreatefromjpeg($_image); break;
			case 'gif':  $image = imagecreatefromgif($_image); break;
			case 'png':  $image = imagecreatefrompng($_image); break;
		}
		
		if(!file_exists($_origin)) imagejpeg($image, $_origin);

		if(file_exists($_origin) and $this->update_enable) {
			unlink($_origin);
			imagejpeg($image, $_origin);
		}
		

		$img = imagecreatefromjpeg($_origin);
		//$dir = _ROOTDIR_ ."/images/thumbnails/";
		//if (!file_exists($dir) && !is_dir($dir)) mkdir($dir);

		$thumb_width = 300;
		$thumb_height = 200;

		$width = imagesx($img);
		$height = imagesy($img);

		$original_aspect = $width / $height;
		$thumb_aspect = $thumb_width / $thumb_height;

		if ( $original_aspect >= $thumb_aspect ){ //if image is wider than thumbnail (in aspect ratio sense)
			$new_height = $thumb_height;
			$new_width = $width / ($height / $thumb_height);
		} else { //if the thumbnail is wider than the image
			$new_width = $thumb_width;
			$new_height = $height / ($width / $thumb_width);
		}

		$thumb = imagecreatetruecolor( $thumb_width, $thumb_height );

		// resize and crop
		$crop = imagecopyresampled(
			$thumb,
			$img,
			0 - ($new_width - $thumb_width) / 2, // center the image horizontally
			0 - ($new_height - $thumb_height) / 2, // center the image vertically
			0, 0,
			$new_width, $new_height,
			$width, $height
		);

		if(file_exists($_thumb)) unlink($_thumb); //removes thumbnail if exist

		$save = imagejpeg($thumb, $_thumb);

		return $crop and $save ? true : false;
	}

	private function clearOldImages(){

		$source = '/images/origin/';
		$images = scandir(_ROOTDIR_ . $source);

		foreach($images as $image){
			$dirs = ['.','..','normal','origin','thumb','junk'];
			if(!in_array($image, $dirs)) {

				$path = _ROOTDIR_ . $source . $image;
				$junk = _ROOTDIR_ . '/images/junk/' . $image;
				$thumb = _ROOTDIR_ . '/images/thumb/' . $image;
				$normal = _ROOTDIR_ . '/images/normal/' . $image;
				

				Log::console("[..] " . $image);

				$imgurl = $this->db->getDBImage($image);
				if(!$imgurl){

					Log::init("Moving file: ". $image ." to junk.");

					if(!rename($path, $junk)) { Log::init("Image ". $image ." moving failed."); }
					
					if (file_exists($thumb))
						if (!unlink($thumb)) { Log::init("Removing thumb: ". $image); }
					
					if (file_exists($normal))
						if (!unlink($normal)) { Log::init("Removing normal: ". $image); }
				}
			}
		}
	}

	private function convertOldImages(){

		$source = '/images/origin/';
		$images = scandir(_ROOTDIR_ . $source);

		foreach($images as $image){
			$dirs = ['.','..','normal','origin','thumb','junk'];
			if(!in_array($image, $dirs)) {

				if(substr($image, -10) == "_to_remove") continue;

				$path = _ROOTDIR_ . $source . $image;
				$copy = _ROOTDIR_ . '/tmp/' . $image;
				$location1 = _ROOTDIR_ . '/images/junk/' . $image;
				$location2 = _ROOTDIR_ . '/images/origin/' . $image;

				$result = $this->prepareImage($path);

				if($result){
					
					//if(!copy($path, $location2)) { Log::init("Image ". $image ." copy failed."); continue; }
					
					//if(!rename($path, $location1)) { Log::init("Image ". $image ." moving failed."); continue; }

					Log::init("Image ". $image ." saved properly.");

					continue;
				}

				/* if(!$result){

					$imgurl = $this->db->getDBImage($image);
					
					file_put_contents($copy, file_get_contents(str_replace(' ', '%20', $imgurl[0])));

					$web = $this->prepareImage($copy);

					if($web){

						if(!copy($copy, $location2)) { Log::init("Image ". $image ." copy failed."); continue; }
					
						if(!rename($copy, $location1)) { Log::init("Image ". $image ." moving failed."); continue; }

						if(!rename($path, $path."_to_remove")) { Log::init("Image ". $image ." moving failed."); continue; }

						Log::init("Image ". $image ." saved properly.");

						continue;
					}
				} */
				
				Log::init("[!!] Saving image ". $image ." failed.");
			}
		}

	}

	public function __toString(){
		return print_r($this->news_list);
	}
}