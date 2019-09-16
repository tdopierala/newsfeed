<?php

foreach($x->channel->item as $entry) {

	$_link = (string)$entry->link;

	if($_this->debug) Log::init($_link);
	else Log::console($_link);

	/* $ch = curl_init($_link);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)");
	curl_setopt($ch, CURLOPT_MAXREDIRS, 2); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$html = curl_exec($ch);
	curl_close($ch); */

	//$dom->loadFromFile($_link);
	//$html = $dom->outerHtml;

	$try=0;

	do {
		$dom->loadFromFile($_link);
		$html = $dom->outerHtml;

		if ($try>0) {
			if ($_this->debug) Log::init("Retrying ($try): $_link");
			else Log::console("Retrying ($try): $_link");
		}

		$try++;
		if ($try>10) break;
	} while (empty($html));
	
	$dom->load($html);
	$picture = $dom->find('.hero__image', 0);

	if (is_object($picture)) {

		$dom->load($picture->innerHtml);
		$imgs = $dom->find('img', 0);

		$image_url = htmlspecialchars_decode($imgs->getAttribute('src'));

	} else {
		Log::dump($picture);
		//var_dump($picture);
		Log::init(substr_count($html, '.hero__image'));
		Log::init($_link);
		Log::dump($html);
	}

	$news_list[] = new Link(array(
		'title' => (string)$entry->title,
		'description' => strip_tags((string)$entry->description),
		'base_url' => $entry->link,
		'date' => date("Y-m-d H:i:s",strtotime($entry->pubDate)),
		'image_url' => $image_url,
		'origin_url' => $entry->guid,
		'link2' => $entry->link,
		'content' => $html
	));
}