<?php

foreach($x->channel->item as $entry) {

	$_link = (string)Url::get($entry->link);

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	$headers = @get_headers($_link);

	if(!$headers or $headers[0] == 'HTTP/1.1 404 Not Found') continue;

	$code = explode(" ",$headers[0]);
	if($code[1] != "200" ) continue; //and $code[1] != "301"

	if(empty((string)$entry->title)) continue;

	$url = explode("/",$_link)[2];
	switch($url){
		case "www.tvn24.pl":

			$dom->loadFromFile($_link);
			$html = $dom->outerHtml;
			$figure = $dom->find('figure', 0);

			$dom->load($figure->innerHtml);
			$imgs = $dom->find('img', 0);

			if(strlen($imgs)==0) {
				//var_dump("No thumbnails. Link lost.");
				continue 2;
			}

			$image_url = htmlspecialchars_decode($imgs->getAttribute('src'));

		break;
		case "konkret24.tvn24.pl":

			$dom->loadFromFile($_link);
			$html = $dom->outerHtml;
			$picture = $dom->find('picture', 0);

			$dom->load($figure->innerHtml);
			$imgs = $dom->find('img', 0);

			$image_url = htmlspecialchars_decode($imgs->getAttribute('src'));

		break;
		default: 
			//var_dump($url);
			//var_dump("not valid one");
			continue 2;
		break;
	}

	$news_list[] = new Link(array(
		'title' => (string)$entry->title,
		'description' => trim(strip_tags((string)$entry->description)),
		'base_url' => $_link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link
		,'content' => $html
	));
}